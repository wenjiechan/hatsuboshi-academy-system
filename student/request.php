<?php
require_once '../includes/auth.php';
require_role('student');

require_once '../config/database.php';
require_once '../includes/theme_settings_helpers.php';
require_once '../includes/student_request_helpers.php';

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

$request_context = load_student_request_context($pdo, (int) $_SESSION['id']);
$student = $request_context['student'];

if (!$student) {
    redirect_to_account_issue(
        'Student profile not found',
        'Your login is active, but no student profile is linked to this account yet. Please log out and ask an administrator to check your student account setup.',
        404
    );
}

apply_student_request_session_theme($student);

$admins = $request_context['admins'];
$current_songs = $request_context['current_songs'];
$available_songs = $request_context['available_songs'];
$producer_is_available = $request_context['producer_is_available'];
$recipient_summary = $request_context['recipient_summary'];
$birthday_data = student_request_birthday_view_data($student);
$birthday_display = $birthday_data['display'];
$birthday_month_value = $birthday_data['month'];
$birthday_day_value = $birthday_data['day'];
$birthday_months = $birthday_data['months'];
$request_success = $_SESSION['student_request_success'] ?? '';
$request_error = '';
unset($_SESSION['student_request_success']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf($_POST['csrf_token'] ?? '');

    $request_type = (string) ($_POST['request_type'] ?? 'profile_update');
    $message = trim((string) ($_POST['request_message'] ?? ''));

    $payload = match ($request_type) {
        'profile_update' => student_request_profile_payload($_POST, $student),
        'song_add' => student_request_song_add_payload($_POST, $available_songs),
        'song_edit' => student_request_song_edit_payload($_POST, $current_songs),
        'song_delete' => student_request_song_delete_payload($_POST, $current_songs),
        default => ['error' => 'Choose a valid request type.'],
    };

    $recipient_id = student_request_recipient_id($_POST, $student, $admins, $producer_is_available, $request_type);

    if (($payload['error'] ?? '') !== '') {
        $request_error = $payload['error'];
    } elseif ($recipient_id <= 0) {
        $request_error = 'No valid recipient is available for this request.';
    } else {
        create_student_update_request($pdo, $student, $recipient_id, $payload, $message);
        $_SESSION['student_request_success'] = 'Request submitted successfully.';
        header('Location: /gakumas-sms/student/request.php');
        exit;
    }
}

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

    <?php if ($request_success !== ''): ?>
        <div class="alert alert-success" role="status">
            <?= e($request_success) ?>
        </div>
    <?php endif; ?>

    <?php if ($request_error !== ''): ?>
        <div class="alert alert-danger" role="alert">
            <?= e($request_error) ?>
        </div>
    <?php endif; ?>

    <form class="request-workspace" id="studentRequestForm" action="/gakumas-sms/student/request.php" method="post">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="request_type" id="studentRequestTypeInput" value="profile_update">

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
                        <label class="request-choice" data-recipient-choice="producer">
                            <input type="radio" name="recipient_type" value="producer" checked data-recipient-input="producer">
                            <span>
                                <strong>Producer</strong>
                                <small><?= e($student['producer_name'] ?: 'Assigned producer') ?></small>
                            </span>
                        </label>
                    <?php endif; ?>

                    <label class="request-choice" data-recipient-choice="admin">
                        <input type="radio" name="recipient_type" value="admin" <?= $producer_is_available ? '' : 'checked' ?> data-recipient-input="admin">
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
                <input type="hidden" name="song_add_mode" id="songAddModeInput" value="existing">

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
                                <option
                                    value="<?= (int) $song['id'] ?>"
                                    data-title="<?= e($song['title']) ?>"
                                    data-title-jp="<?= e($song['title_jp']) ?>"
                                    data-artist="<?= e($song['artist']) ?>"
                                    data-duration="<?= e(format_request_song_duration($song['duration'])) ?>"
                                    data-song-type="<?= e($song['song_type']) ?>">
                                    <?= e($song['title']) ?> &middot; <?= e($song['artist'] ?: 'Unknown artist') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="editSongTitle" class="form-label">Title</label>
                        <input type="text" id="editSongTitle" name="edit_song_title" class="form-control">
                    </div>

                    <div class="col-md-6">
                        <label for="editSongTitleJp" class="form-label">Japanese Title</label>
                        <input type="text" id="editSongTitleJp" name="edit_song_title_jp" class="form-control">
                    </div>

                    <div class="col-md-6">
                        <label for="editSongArtist" class="form-label">Artist</label>
                        <input type="text" id="editSongArtist" name="edit_song_artist" class="form-control">
                    </div>

                    <div class="col-md-3">
                        <label for="editSongDuration" class="form-label">Duration</label>
                        <input type="text" id="editSongDuration" name="edit_song_duration" class="form-control" placeholder="03:45">
                    </div>

                    <div class="col-md-3">
                        <label for="editSongType" class="form-label">Type</label>
                        <select id="editSongType" name="edit_song_type" class="form-select">
                            <option value="">Choose type</option>
                            <option>Solo</option>
                            <option>Group</option>
                            <option>Remix</option>
                            <option>Cover</option>
                        </select>
                    </div>

                    <div class="col-12">
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

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-send" aria-hidden="true"></i>
                    Submit Request
                </button>
            </div>
        </section>
    </form>
</main>

<script src="/gakumas-sms/assets/js/student-request.js"></script>
<?php require_once '../includes/footer.php'; ?>
