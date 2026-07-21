<?php
require_once '../includes/auth.php';
require_role('student');

require_once '../config/database.php';
require_once '../includes/theme_settings_helpers.php';
require_once '../includes/student_edit_validation.php';

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function format_request_song_duration(?string $duration): string
{
    if (!$duration) {
        return '';
    }

    $parts = explode(':', $duration);

    if (count($parts) !== 3) {
        return $duration;
    }

    return sprintf('%02d:%02d', (int) $parts[1], (int) $parts[2]);
}

$student_stmt = $pdo->prepare(
    'SELECT
        s.*,
        u.avatar,
        u.theme_primary_color,
        u.theme_secondary_color,
        producer.username AS producer_name
     FROM students s
     INNER JOIN users u ON u.id = s.user_id
     LEFT JOIN users producer ON producer.id = s.producer_id
     WHERE s.user_id = ?
     LIMIT 1'
);

$student_stmt->execute([$_SESSION['id']]);
$student = $student_stmt->fetch();

if (!$student) {
    redirect_to_account_issue(
        'Student profile not found',
        'Your login is active, but no student profile is linked to this account yet. Please log out and ask an administrator to check your student account setup.',
        404
    );
}

$_SESSION['student_name'] = $student['name'];
$_SESSION['avatar'] = $student['avatar'] ?? '';
$_SESSION['theme_primary_color'] = $student['theme_primary_color'] ?: DEFAULT_THEME_PRIMARY;
$_SESSION['theme_secondary_color'] = $student['theme_secondary_color'] ?: DEFAULT_THEME_SECONDARY;

$admin_stmt = $pdo->query(
    'SELECT id, username
     FROM users
     WHERE role = "admin"
       AND is_active = 1
     ORDER BY username ASC'
);
$admins = $admin_stmt->fetchAll();

$current_song_stmt = $pdo->prepare(
    'SELECT
        so.id,
        so.title,
        so.title_jp,
        so.artist,
        so.duration,
        so.release_date,
        so.song_type,
        so.notes
     FROM student_songs ss
     INNER JOIN songs so ON so.id = ss.song_id
     WHERE ss.student_id = ?
     ORDER BY FIELD(so.song_type, "Solo", "Group", "Remix", "Cover"), so.title ASC'
);
$current_song_stmt->execute([(int) $student['id']]);
$current_songs = $current_song_stmt->fetchAll();

$library_song_stmt = $pdo->prepare(
    'SELECT
        so.id,
        so.title,
        so.title_jp,
        so.artist,
        so.duration,
        so.release_date,
        so.song_type
     FROM songs so
     WHERE NOT EXISTS (
        SELECT 1
        FROM student_songs ss
        WHERE ss.song_id = so.id
          AND ss.student_id = ?
     )
     ORDER BY so.title ASC'
);
$library_song_stmt->execute([(int) $student['id']]);
$available_songs = $library_song_stmt->fetchAll();

$producer_is_available = !empty($student['producer_id'])
    && in_array($student['producer_status'] ?? 'unassigned', ['active', 'removal_pending'], true);

$recipient_summary = $producer_is_available
    ? 'You can send this request to your producer or admin.'
    : 'You are unassigned, so requests go to admin only.';

$birthday_timestamp = !empty($student['birthday']) ? strtotime((string) $student['birthday']) : false;
$birthday_display = $birthday_timestamp ? date('F d', $birthday_timestamp) : '';
$birthday_month_value = $birthday_timestamp ? (int) date('n', $birthday_timestamp) : 1;
$birthday_day_value = $birthday_timestamp ? (int) date('j', $birthday_timestamp) : 1;
$birthday_months = student_edit_month_options();

$page_title = 'Request';
$page_styles = ['/gakumas-sms/assets/css/pages/request.css'];
require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

<main class="dashboard-main request-main">
    <section class="request-hero">
        <div>
            <p class="dashboard-eyebrow">Student Request Desk</p>
            <h2>Request Profile or Song Changes</h2>
            <p>Prepare profile updates and song requests before they are reviewed by staff.</p>
        </div>

        <div class="request-route-card">
            <span>Route</span>
            <strong><?= e($producer_is_available ? 'Producer or Admin' : 'Admin Only') ?></strong>
            <small><?= e($recipient_summary) ?></small>
        </div>
    </section>

    <form class="request-workspace" id="studentRequestForm" action="#" method="post">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

        <aside class="request-type-panel" aria-label="Request type">
            <button type="button" class="request-type-button is-active" data-request-type="profile_update">
                <i class="bi bi-person-lines-fill" aria-hidden="true"></i>
                <span>
                    <strong>Profile Update</strong>
                    <small>Identity, physical, academy details</small>
                </span>
            </button>

            <button type="button" class="request-type-button" data-request-type="song_add">
                <i class="bi bi-music-note-beamed" aria-hidden="true"></i>
                <span>
                    <strong>Add Song</strong>
                    <small>Existing or new library song</small>
                </span>
            </button>

            <button type="button" class="request-type-button" data-request-type="song_edit">
                <i class="bi bi-pencil-square" aria-hidden="true"></i>
                <span>
                    <strong>Edit Song</strong>
                    <small>Correct global song data</small>
                </span>
            </button>

            <button type="button" class="request-type-button" data-request-type="song_delete">
                <i class="bi bi-trash3" aria-hidden="true"></i>
                <span>
                    <strong>Delete Song</strong>
                    <small>Remove from your list</small>
                </span>
            </button>
        </aside>

        <section class="request-form-panel">
            <div class="request-card request-recipient-card">
                <div class="request-card-heading">
                    <i class="bi bi-send-check" aria-hidden="true"></i>
                    <div>
                        <h3>Recipient</h3>
                        <p>Choose who should review this request.</p>
                    </div>
                </div>

                <div class="request-recipient-grid">
                    <?php if ($producer_is_available): ?>
                        <label class="request-choice">
                            <input type="radio" name="recipient_type" value="producer" checked>
                            <span>
                                <strong>Producer</strong>
                                <small><?= e($student['producer_name'] ?: 'Assigned producer') ?></small>
                            </span>
                        </label>
                    <?php endif; ?>

                    <label class="request-choice">
                        <input type="radio" name="recipient_type" value="admin" <?= $producer_is_available ? '' : 'checked' ?>>
                        <span>
                            <strong>Admin</strong>
                            <small><?= empty($admins) ? 'Admin team' : e($admins[0]['username']) ?></small>
                        </span>
                    </label>
                </div>
            </div>

            <div class="request-card request-section is-active" data-request-section="profile_update">
                <div class="request-card-heading">
                    <i class="bi bi-person-heart" aria-hidden="true"></i>
                    <div>
                        <h3>Profile Update</h3>
                        <p>Request changes for profile details you cannot update yourself.</p>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="requestName" class="form-label">Name</label>
                        <input type="text" id="requestName" name="name" class="form-control" value="<?= e($student['name']) ?>">
                    </div>

                    <div class="col-md-6">
                        <label for="requestNameJp" class="form-label">Japanese Name</label>
                        <input type="text" id="requestNameJp" name="name_jp" class="form-control" value="<?= e($student['name_jp']) ?>">
                    </div>

                    <div class="col-md-4">
                        <label for="requestBirthday" class="form-label">Birthday</label>
                        <div class="request-birthday-picker" data-selected-month="<?= (int) $birthday_month_value ?>" data-selected-day="<?= (int) $birthday_day_value ?>">
                            <input type="hidden" id="requestBirthday" name="birthday" value="<?= e($birthday_display) ?>">
                            <button type="button" class="form-control request-birthday-button" id="requestBirthdayButton">
                                <span id="requestBirthdayLabel"><?= e($birthday_display ?: 'Choose birthday') ?></span>
                                <i class="bi bi-calendar3" aria-hidden="true"></i>
                            </button>

                            <div class="request-birthday-popover d-none" id="requestBirthdayPopover">
                                <div class="request-birthday-controls">
                                    <button type="button" class="request-calendar-nav" id="requestBirthdayPrevMonth" aria-label="Previous month">
                                        <i class="bi bi-chevron-left" aria-hidden="true"></i>
                                    </button>

                                    <select class="form-select request-birthday-month" id="requestBirthdayMonthSelect" aria-label="Birthday month">
                                        <?php foreach ($birthday_months as $month_number => $month_name): ?>
                                            <option value="<?= (int) $month_number ?>" <?= (int) $month_number === (int) $birthday_month_value ? 'selected' : '' ?>>
                                                <?= e($month_name) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>

                                    <button type="button" class="request-calendar-nav" id="requestBirthdayNextMonth" aria-label="Next month">
                                        <i class="bi bi-chevron-right" aria-hidden="true"></i>
                                    </button>
                                </div>

                                <div class="request-calendar-weekdays" aria-hidden="true">
                                    <span>Sun</span>
                                    <span>Mon</span>
                                    <span>Tue</span>
                                    <span>Wed</span>
                                    <span>Thu</span>
                                    <span>Fri</span>
                                    <span>Sat</span>
                                </div>

                                <div class="request-calendar-days" id="requestBirthdayDayGrid"></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label for="requestZodiac" class="form-label">Zodiac</label>
                        <input type="text" id="requestZodiac" name="zodiac" class="form-control" value="<?= e($student['zodiac']) ?>" readonly>
                    </div>

                    <div class="col-md-4">
                        <label for="requestBloodType" class="form-label">Blood Type</label>
                        <select id="requestBloodType" name="blood_type" class="form-select">
                            <?php foreach (['', 'A', 'B', 'AB', 'O'] as $blood_type): ?>
                                <option value="<?= e($blood_type) ?>" <?= ($student['blood_type'] ?? '') === $blood_type ? 'selected' : '' ?>>
                                    <?= e($blood_type === '' ? 'Not listed' : $blood_type) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label for="requestHeight" class="form-label">Height</label>
                        <div class="input-group request-fixed-suffix">
                            <input type="number" id="requestHeight" name="height" class="form-control" value="<?= e((string) $student['height']) ?>" min="0">
                            <span class="input-group-text">cm</span>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label for="requestWeight" class="form-label">Weight</label>
                        <div class="input-group request-fixed-suffix">
                            <input type="number" id="requestWeight" name="weight" class="form-control" value="<?= e((string) $student['weight']) ?>" min="0">
                            <span class="input-group-text">kg</span>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label for="requestThreeSize" class="form-label">Three Size</label>
                        <input type="text" id="requestThreeSize" name="three_size" class="form-control" value="<?= e($student['three_size']) ?>">
                    </div>

                    <div class="col-md-6">
                        <label for="requestSchoolYear" class="form-label">Class</label>
                        <input type="text" id="requestSchoolYear" name="school_year" class="form-control" value="<?= e($student['school_year']) ?>">
                    </div>
                </div>
            </div>

            <div class="request-card request-section" data-request-section="song_add">
                <div class="request-card-heading">
                    <i class="bi bi-music-note-beamed" aria-hidden="true"></i>
                    <div>
                        <h3>Add Song</h3>
                        <p>Search the global library first, then switch to new song details if it is not found.</p>
                    </div>
                </div>

                <div class="request-mode-tabs" role="tablist" aria-label="Song add mode">
                    <button type="button" class="request-mode-button is-active" data-song-add-mode="existing">Existing Song</button>
                    <button type="button" class="request-mode-button" data-song-add-mode="new">Song Not Found</button>
                </div>

                <div class="request-song-add-panel is-active" data-song-add-panel="existing">
                    <label class="request-search" for="librarySongSearch">
                        <i class="bi bi-search" aria-hidden="true"></i>
                        <input type="search" id="librarySongSearch" placeholder="Search title, Japanese title, artist, or type" autocomplete="off">
                    </label>

                    <div class="request-song-picker" id="librarySongResults" role="listbox" aria-label="Available songs">
                        <?php if (empty($available_songs)): ?>
                            <p class="request-empty-note">Every global song is already assigned to your profile.</p>
                        <?php else: ?>
                            <?php foreach ($available_songs as $song): ?>
                                <?php
                                $song_search = strtolower(implode(' ', [
                                    $song['title'] ?? '',
                                    $song['title_jp'] ?? '',
                                    $song['artist'] ?? '',
                                    $song['song_type'] ?? '',
                                ]));
                                ?>
                                <label class="request-song-option" data-song-option data-song-search="<?= e($song_search) ?>">
                                    <input type="radio" name="existing_song_id" value="<?= (int) $song['id'] ?>">
                                    <span>
                                        <strong><?= e($song['title']) ?></strong>
                                        <?php if (!empty($song['title_jp']) && $song['title_jp'] !== $song['title']): ?>
                                            <small lang="ja"><?= e($song['title_jp']) ?></small>
                                        <?php endif; ?>
                                        <small><?= e($song['artist'] ?: 'Unknown artist') ?> &middot; <?= e($song['song_type']) ?></small>
                                    </span>
                                    <em><?= e(format_request_song_duration($song['duration'])) ?></em>
                                </label>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <p class="request-empty-note d-none" id="librarySongEmpty">No available songs match your search.</p>
                </div>

                <div class="request-song-add-panel" data-song-add-panel="new">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="newSongTitle" class="form-label">Title</label>
                            <input type="text" id="newSongTitle" name="new_song_title" class="form-control" placeholder="Song title">
                        </div>

                        <div class="col-md-6">
                            <label for="newSongTitleJp" class="form-label">Japanese Title</label>
                            <input type="text" id="newSongTitleJp" name="new_song_title_jp" class="form-control" placeholder="Optional">
                        </div>

                        <div class="col-md-6">
                            <label for="newSongArtist" class="form-label">Artist</label>
                            <input type="text" id="newSongArtist" name="new_song_artist" class="form-control" placeholder="Artist">
                        </div>

                        <div class="col-md-3">
                            <label for="newSongType" class="form-label">Type</label>
                            <select id="newSongType" name="new_song_type" class="form-select">
                                <option>Solo</option>
                                <option>Group</option>
                                <option>Remix</option>
                                <option>Cover</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label for="newSongDuration" class="form-label">Duration</label>
                            <input type="text" id="newSongDuration" name="new_song_duration" class="form-control" placeholder="03:42">
                        </div>

                        <div class="col-md-4">
                            <label for="newSongReleaseDate" class="form-label">Release Date</label>
                            <input type="date" id="newSongReleaseDate" name="new_song_release_date" class="form-control">
                        </div>

                        <div class="col-md-8">
                            <label for="newSongNotes" class="form-label">Notes</label>
                            <input type="text" id="newSongNotes" name="new_song_notes" class="form-control" placeholder="Reason or extra context">
                        </div>
                    </div>
                </div>
            </div>

            <div class="request-card request-section" data-request-section="song_edit">
                <div class="request-card-heading">
                    <i class="bi bi-pencil-square" aria-hidden="true"></i>
                    <div>
                        <h3>Song Correction</h3>
                        <p>Use this only for correcting song information, not replacing a song.</p>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-12">
                        <label for="editSongSelect" class="form-label">Current Song</label>
                        <select id="editSongSelect" name="edit_song_id" class="form-select">
                            <option value="">Choose one of your songs</option>
                            <?php foreach ($current_songs as $song): ?>
                                <option value="<?= (int) $song['id'] ?>"><?= e($song['title']) ?> &middot; <?= e($song['artist'] ?: 'Unknown artist') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="editSongTitle" class="form-label">Corrected Title</label>
                        <input type="text" id="editSongTitle" name="edit_song_title" class="form-control">
                    </div>

                    <div class="col-md-6">
                        <label for="editSongArtist" class="form-label">Corrected Artist</label>
                        <input type="text" id="editSongArtist" name="edit_song_artist" class="form-control">
                    </div>

                    <div class="col-md-4">
                        <label for="editSongDuration" class="form-label">Corrected Duration</label>
                        <input type="text" id="editSongDuration" name="edit_song_duration" class="form-control" placeholder="03:45">
                    </div>

                    <div class="col-md-8">
                        <label for="editSongReason" class="form-label">Reason</label>
                        <input type="text" id="editSongReason" name="edit_song_reason" class="form-control" placeholder="What should staff verify?">
                    </div>
                </div>
            </div>

            <div class="request-card request-section" data-request-section="song_delete">
                <div class="request-card-heading">
                    <i class="bi bi-trash3" aria-hidden="true"></i>
                    <div>
                        <h3>Remove Song From My List</h3>
                        <p>This removes the song assignment from your profile only.</p>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-12">
                        <label for="deleteSongSelect" class="form-label">Current Song</label>
                        <select id="deleteSongSelect" name="delete_song_id" class="form-select">
                            <option value="">Choose one of your songs</option>
                            <?php foreach ($current_songs as $song): ?>
                                <option value="<?= (int) $song['id'] ?>"><?= e($song['title']) ?> &middot; <?= e($song['artist'] ?: 'Unknown artist') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-12">
                        <label for="deleteSongReason" class="form-label">Reason</label>
                        <textarea id="deleteSongReason" name="delete_song_reason" class="form-control" rows="4" placeholder="Why should this song be removed from your profile?"></textarea>
                    </div>
                </div>
            </div>

            <div class="request-card request-note-card">
                <label for="requestMessage" class="form-label">Message</label>
                <textarea id="requestMessage" name="request_message" class="form-control" rows="4" placeholder="Add context for the reviewer"></textarea>
            </div>

            <div class="request-actions">
                <button type="reset" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i>
                    Reset
                </button>

                <button type="button" class="btn btn-primary" disabled aria-disabled="true">
                    <i class="bi bi-send" aria-hidden="true"></i>
                    Submit Request
                </button>

                <span>Interface only</span>
            </div>
        </section>
    </form>
</main>

<script>
const studentRequestForm = document.getElementById('studentRequestForm');
const requestTypeButtons = Array.from(document.querySelectorAll('[data-request-type]'));
const requestSections = Array.from(document.querySelectorAll('[data-request-section]'));
const addModeButtons = Array.from(document.querySelectorAll('[data-song-add-mode]'));
const addModePanels = Array.from(document.querySelectorAll('[data-song-add-panel]'));
const requestBirthday = document.getElementById('requestBirthday');
const requestZodiac = document.getElementById('requestZodiac');
const requestBirthdayPicker = document.querySelector('.request-birthday-picker');
const requestBirthdayButton = document.getElementById('requestBirthdayButton');
const requestBirthdayLabel = document.getElementById('requestBirthdayLabel');
const requestBirthdayPopover = document.getElementById('requestBirthdayPopover');
const requestBirthdayMonthSelect = document.getElementById('requestBirthdayMonthSelect');
const requestBirthdayDayGrid = document.getElementById('requestBirthdayDayGrid');
const requestBirthdayPrevMonth = document.getElementById('requestBirthdayPrevMonth');
const requestBirthdayNextMonth = document.getElementById('requestBirthdayNextMonth');
const librarySongSearch = document.getElementById('librarySongSearch');
const librarySongOptions = Array.from(document.querySelectorAll('[data-song-option]'));
const librarySongEmpty = document.getElementById('librarySongEmpty');
const monthNames = [
    'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December'
];

if (studentRequestForm) {
    studentRequestForm.addEventListener('submit', (event) => {
        event.preventDefault();
    });

    studentRequestForm.addEventListener('reset', () => {
        window.setTimeout(() => {
            const birthday = parseMonthDay(requestBirthday?.value || '');

            if (birthday && requestBirthdayPicker && requestBirthdayMonthSelect && requestBirthdayLabel) {
                requestBirthdayPicker.dataset.selectedMonth = String(birthday.month);
                requestBirthdayPicker.dataset.selectedDay = String(birthday.day);
                requestBirthdayMonthSelect.value = String(birthday.month);
                requestBirthdayLabel.textContent = requestBirthday.value;
                renderRequestBirthdayDays();
            }

            updateRequestZodiac();
        }, 0);
    });
}

function zodiacFromMonthDay(month, day) {
    if ((month === 1 && day >= 20) || (month === 2 && day <= 18)) return 'Aquarius';
    if ((month === 2 && day >= 19) || (month === 3 && day <= 20)) return 'Pisces';
    if ((month === 3 && day >= 21) || (month === 4 && day <= 19)) return 'Aries';
    if ((month === 4 && day >= 20) || (month === 5 && day <= 20)) return 'Taurus';
    if ((month === 5 && day >= 21) || (month === 6 && day <= 20)) return 'Gemini';
    if ((month === 6 && day >= 21) || (month === 7 && day <= 22)) return 'Cancer';
    if ((month === 7 && day >= 23) || (month === 8 && day <= 22)) return 'Leo';
    if ((month === 8 && day >= 23) || (month === 9 && day <= 22)) return 'Virgo';
    if ((month === 9 && day >= 23) || (month === 10 && day <= 22)) return 'Libra';
    if ((month === 10 && day >= 23) || (month === 11 && day <= 21)) return 'Scorpio';
    if ((month === 11 && day >= 22) || (month === 12 && day <= 21)) return 'Sagittarius';
    return 'Capricorn';
}

function formatMonthDay(month, day) {
    return `${monthNames[month - 1]} ${String(day).padStart(2, '0')}`;
}

function parseMonthDay(value) {
    const trimmed = value.trim();
    const textMatch = trimmed.match(/^([A-Za-z]+)\s+(\d{1,2})$/);

    if (!textMatch) {
        return null;
    }

    const monthIndex = monthNames.map((month) => month.toLowerCase()).indexOf(textMatch[1].toLowerCase());

    if (monthIndex === -1) {
        return null;
    }

    return {
        month: monthIndex + 1,
        day: Number(textMatch[2])
    };
}

function updateRequestZodiac() {
    if (!requestBirthday || !requestZodiac) {
        return;
    }

    if (!requestBirthday.value) {
        requestZodiac.value = '';
        return;
    }

    const birthday = parseMonthDay(requestBirthday.value);

    if (!birthday || birthday.month < 1 || birthday.month > 12 || birthday.day < 1 || birthday.day > 31) {
        requestZodiac.value = '';
        return;
    }

    requestZodiac.value = zodiacFromMonthDay(birthday.month, birthday.day);
}

function setRequestBirthday(month, day) {
    if (!requestBirthday || !requestBirthdayPicker || !requestBirthdayLabel) {
        return;
    }

    const value = formatMonthDay(month, day);
    requestBirthdayPicker.dataset.selectedMonth = String(month);
    requestBirthdayPicker.dataset.selectedDay = String(day);
    requestBirthday.value = value;
    requestBirthdayLabel.textContent = value;
    updateRequestZodiac();
}

function closeRequestBirthdayPicker() {
    if (requestBirthdayPopover) {
        requestBirthdayPopover.classList.add('d-none');
    }
}

function renderRequestBirthdayDays() {
    if (!requestBirthdayMonthSelect || !requestBirthdayDayGrid || !requestBirthdayPicker) {
        return;
    }

    const selectedMonth = Number(requestBirthdayMonthSelect.value);
    const selectedDay = Number(requestBirthdayPicker.dataset.selectedDay || 1);
    const daysInMonth = new Date(new Date().getFullYear(), selectedMonth, 0).getDate();
    const firstWeekday = new Date(new Date().getFullYear(), selectedMonth - 1, 1).getDay();

    requestBirthdayDayGrid.innerHTML = '';

    for (let index = 0; index < firstWeekday; index += 1) {
        const spacer = document.createElement('span');
        spacer.className = 'request-calendar-spacer';
        requestBirthdayDayGrid.appendChild(spacer);
    }

    for (let day = 1; day <= daysInMonth; day += 1) {
        const dayButton = document.createElement('button');
        dayButton.type = 'button';
        dayButton.className = 'request-calendar-day';
        dayButton.textContent = String(day);

        if (day === selectedDay && selectedMonth === Number(requestBirthdayPicker.dataset.selectedMonth || 1)) {
            dayButton.classList.add('is-selected');
        }

        dayButton.addEventListener('click', () => {
            setRequestBirthday(selectedMonth, day);
            renderRequestBirthdayDays();
            closeRequestBirthdayPicker();
        });

        requestBirthdayDayGrid.appendChild(dayButton);
    }
}

if (requestBirthday) {
    updateRequestZodiac();
}

if (requestBirthdayButton && requestBirthdayPopover) {
    requestBirthdayButton.addEventListener('click', () => {
        const isOpening = requestBirthdayPopover.classList.contains('d-none');
        requestBirthdayPopover.classList.toggle('d-none', !isOpening);

        if (isOpening) {
            renderRequestBirthdayDays();
        }
    });
}

if (requestBirthdayMonthSelect) {
    requestBirthdayMonthSelect.addEventListener('change', () => {
        const month = Number(requestBirthdayMonthSelect.value);
        const currentDay = Math.min(
            Number(requestBirthdayPicker?.dataset.selectedDay || 1),
            new Date(new Date().getFullYear(), month, 0).getDate()
        );

        if (requestBirthdayPicker) {
            requestBirthdayPicker.dataset.selectedDay = String(currentDay);
        }

        renderRequestBirthdayDays();
    });
}

if (requestBirthdayPrevMonth && requestBirthdayMonthSelect) {
    requestBirthdayPrevMonth.addEventListener('click', () => {
        const currentMonth = Number(requestBirthdayMonthSelect.value);
        requestBirthdayMonthSelect.value = String(currentMonth === 1 ? 12 : currentMonth - 1);
        requestBirthdayMonthSelect.dispatchEvent(new Event('change'));
    });
}

if (requestBirthdayNextMonth && requestBirthdayMonthSelect) {
    requestBirthdayNextMonth.addEventListener('click', () => {
        const currentMonth = Number(requestBirthdayMonthSelect.value);
        requestBirthdayMonthSelect.value = String(currentMonth === 12 ? 1 : currentMonth + 1);
        requestBirthdayMonthSelect.dispatchEvent(new Event('change'));
    });
}

document.addEventListener('click', (event) => {
    if (
        requestBirthdayPicker
        && requestBirthdayPopover
        && !requestBirthdayPopover.classList.contains('d-none')
        && !requestBirthdayPicker.contains(event.target)
    ) {
        closeRequestBirthdayPicker();
    }
});

renderRequestBirthdayDays();

requestTypeButtons.forEach((button) => {
    button.addEventListener('click', () => {
        const selectedType = button.dataset.requestType;

        requestTypeButtons.forEach((item) => {
            item.classList.toggle('is-active', item === button);
        });

        requestSections.forEach((section) => {
            section.classList.toggle('is-active', section.dataset.requestSection === selectedType);
        });
    });
});

addModeButtons.forEach((button) => {
    button.addEventListener('click', () => {
        const selectedMode = button.dataset.songAddMode;

        addModeButtons.forEach((item) => {
            item.classList.toggle('is-active', item === button);
        });

        addModePanels.forEach((panel) => {
            panel.classList.toggle('is-active', panel.dataset.songAddPanel === selectedMode);
        });
    });
});

if (librarySongSearch) {
    librarySongSearch.addEventListener('input', () => {
        const query = librarySongSearch.value.trim().toLowerCase();
        let visibleCount = 0;

        librarySongOptions.forEach((option) => {
            const isVisible = !query || option.dataset.songSearch.includes(query);
            option.classList.toggle('d-none', !isVisible);

            if (isVisible) {
                visibleCount += 1;
            }
        });

        if (librarySongEmpty) {
            librarySongEmpty.classList.toggle('d-none', visibleCount > 0);
        }
    });
}
</script>

<?php require_once '../includes/footer.php'; ?>
