<?php
require_once '../includes/auth.php';
require_role('admin');

require_once '../config/database.php';
require_once '../includes/admin_song_helpers.php';
require_once '../includes/admin_request_helpers.php';

$page_title = 'Student Songs';
$page_success = $_SESSION['admin_song_success'] ?? '';
$page_error = '';
$valid_types = ['Solo', 'Group', 'Remix', 'Cover'];
$form_values = default_admin_song_form_values();
$manage_student_id = isset($_GET['manage_student_id']) ? max(0, (int) $_GET['manage_student_id']) : 0;
$manual_request_id = max(0, (int) ($_GET['request_id'] ?? $_POST['request_id'] ?? 0));
$manual_request = $manual_request_id > 0 ? load_admin_request_detail($pdo, $manual_request_id) : null;

if ($manual_request) {
    $manage_student_id = (int) ($manual_request['student_id'] ?? $manage_student_id);
}
unset($_SESSION['admin_song_success']);

// Add/remove assignment POSTs are shared with the admin song helper.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (($_POST['request_action'] ?? '') === 'completed' && $manual_request) {
        verify_csrf($_POST['csrf_token'] ?? '');

        try {
            $page_success = producer_request_handle_action($pdo, $manual_request, (int) $_SESSION['id'], 'admin', 'completed');
            $_SESSION['admin_request_success'] = $page_success;
            header('Location: ' . producer_request_detail_url($manual_request, 'admin'));
            exit;
        } catch (Throwable $exception) {
            $page_error = $exception->getMessage();
        }
    } else {
        $post_result = handle_admin_song_post($pdo, $valid_types, $form_values);
        $page_error = $post_result['song_page_error'];
        $manage_student_id = $post_result['manage_student_id'];
    }
}

// Load all active students so admin can manage assigned and unassigned students.
$admin_student_stmt = $pdo->query(
    'SELECT
        s.id,
        s.name,
        s.name_jp,
        s.school_year,
        s.rank,
        p.username AS producer_name,
        COUNT(ss.id) AS song_count
     FROM students s
     INNER JOIN users u ON u.id = s.user_id AND u.is_active = 1
     LEFT JOIN users p ON p.id = s.producer_id
     LEFT JOIN student_songs ss ON ss.student_id = s.id
     GROUP BY s.id, s.name, s.name_jp, s.school_year, s.rank, p.username
     ORDER BY s.school_year ASC, s.name ASC'
);
$admin_students = $admin_student_stmt->fetchAll();

$managed_student = null;
$managed_student_songs = [];
$available_student_songs = [];

// If a student is selected, load their profile, current songs, and songs still available to add.
if ($manage_student_id > 0) {
    $managed_student_stmt = $pdo->prepare(
        'SELECT
            s.id,
            s.name,
            s.name_jp,
            s.school_year,
            s.rank,
            p.username AS producer_name
         FROM students s
         INNER JOIN users u ON u.id = s.user_id AND u.is_active = 1
         LEFT JOIN users p ON p.id = s.producer_id
         WHERE s.id = ?
         LIMIT 1'
    );
    $managed_student_stmt->execute([$manage_student_id]);
    $managed_student = $managed_student_stmt->fetch();

    if ($managed_student) {
        // Current songs are the student's actual assigned song list.
        $managed_song_stmt = $pdo->prepare(
            'SELECT
                so.id,
                so.title,
                so.title_jp,
                so.artist,
                so.duration,
                so.release_date,
                so.song_type,
                so.notes,
                ss.added_at
             FROM student_songs ss
             INNER JOIN songs so ON so.id = ss.song_id
             WHERE ss.student_id = ?
             ORDER BY FIELD(so.song_type, "Solo", "Group", "Remix", "Cover"), so.title ASC'
        );
        $managed_song_stmt->execute([$manage_student_id]);
        $managed_student_songs = $managed_song_stmt->fetchAll();

        // Add-song results exclude songs the student already has to avoid duplicate assignments.
        $available_song_stmt = $pdo->prepare(
            'SELECT
                so.id,
                so.title,
                so.title_jp,
                so.artist,
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
        $available_song_stmt->execute([$manage_student_id]);
        $available_student_songs = $available_song_stmt->fetchAll();
    } else {
        $manage_student_id = 0;
        $page_error = $page_error ?: 'The student you wanted to manage could not be found.';
    }
}

$page_styles = [
    '/gakumas-sms/assets/css/pages/song.css',
    '/gakumas-sms/assets/css/pages/admin-songs.css?v=20260720b',
];
$manual_request_requested = producer_request_context_requested_data($manual_request);
$manual_request_closed = $manual_request && in_array((string) ($manual_request['status'] ?? ''), ['approved', 'rejected', 'cancelled'], true);
$manual_request_back_url = $manual_request ? producer_request_detail_url($manual_request, 'admin') : '';
$manual_request_song_id = (int) ($manual_request['song_id'] ?? ($manual_request_requested['song_id'] ?? 0));
require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

<main class="dashboard-main admin-songs-main">
    <!-- Page-level hero keeps navigation back to the global song library close by. -->
    <section class="admin-songs-hero">
        <div>
            <p class="dashboard-eyebrow">Student Song Management</p>
            <h2>Manage Student Songs</h2>
            <p>Select any active student, including unassigned students, then add or remove their songs.</p>
        </div>

        <div class="admin-songs-filter-actions">
            <a href="/gakumas-sms/admin/songs.php" class="admin-songs-reset-button">
                <i class="bi bi-music-note-list" aria-hidden="true"></i>
                Song Library
            </a>
        </div>
    </section>

    <?php if ($page_success !== ''): ?>
        <div class="alert alert-success" role="status"><?= e($page_success) ?></div>
    <?php endif; ?>

    <?php if ($page_error !== ''): ?>
        <div class="alert alert-danger" role="alert"><?= e($page_error) ?></div>
    <?php endif; ?>

    <!-- Student picker: choosing a card reloads this page with manage_student_id. -->
    <section class="admin-student-songs-card admin-songs-library" id="adminStudentSongs">
        <div class="admin-student-songs-heading">
            <div>
                <p class="dashboard-eyebrow">Choose Student</p>
                <h3>Students</h3>
                <p>Search and choose one student to manage their song list.</p>
            </div>

            <span><?= count($admin_students) ?> active students</span>
        </div>

        <label class="admin-student-manage-search">
            <span>Search student</span>
            <input type="search" id="adminManageStudentSearch" placeholder="Search by student, Japanese name, class, rank, or producer" autocomplete="off">
        </label>

        <?php if (empty($admin_students)): ?>
            <div class="empty-dashboard-state">
                <strong>No active students found</strong>
                <p>Add or activate students before managing song assignments.</p>
            </div>
        <?php else: ?>
            <div class="admin-student-manage-grid" id="adminManageStudentGrid">
                <?php foreach ($admin_students as $student): ?>
                    <?php
                    // Client-side search checks this combined text instead of hitting the database again.
                    $student_card_search = strtolower(implode(' ', [
                        $student['name'] ?? '',
                        $student['name_jp'] ?? '',
                        $student['school_year'] ?? '',
                        $student['rank'] ?? '',
                        $student['producer_name'] ?? 'Unassigned',
                    ]));
                    ?>
                    <a
                        href="/gakumas-sms/admin/student_songs.php?manage_student_id=<?= (int) $student['id'] ?><?= $manual_request_id > 0 ? '&request_id=' . (int) $manual_request_id : '' ?>#adminStudentSongs"
                        class="admin-student-manage-card <?= $manage_student_id === (int) $student['id'] ? 'is-selected' : '' ?>"
                        data-manage-student-card
                        data-student-search="<?= e($student_card_search) ?>">
                        <span>
                            <strong><?= e($student['name']) ?></strong>
                            <?php if (!empty($student['name_jp'])): ?>
                                <small lang="ja"><?= e($student['name_jp']) ?></small>
                            <?php endif; ?>
                        </span>
                        <em><?= e($student['school_year'] ?: 'No class') ?> · <?= e($student['producer_name'] ?: 'Unassigned') ?></em>
                        <b><?= (int) $student['song_count'] ?> song<?= (int) $student['song_count'] === 1 ? '' : 's' ?></b>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <?php if (!$managed_student): ?>
        <section class="empty-dashboard-state admin-student-song-empty">
            <strong>Select a student to manage songs</strong>
            <p>Their current songs and add-song search will appear here.</p>
        </section>
    <?php else: ?>
        <?php if ($manual_request): ?>
            <section class="admin-student-songs-card admin-songs-library manual-request-panel">
                <div class="admin-student-songs-heading">
                    <div>
                        <p class="dashboard-eyebrow">Manual Request</p>
                        <h3><?= e(producer_request_type_label($manual_request['request_type'] ?? '')) ?></h3>
                        <p><?= e($manual_request['details'] ?: 'No message was included with this request.') ?></p>
                    </div>
                    <a href="<?= e($manual_request_back_url) ?>" class="manual-request-back" aria-label="Back to request">
                        <i class="bi bi-arrow-left" aria-hidden="true"></i>
                    </a>
                </div>

                <dl class="manual-request-data">
                    <?php foreach (producer_request_visible_data($manual_request_requested) as $key => $value): ?>
                        <div>
                            <dt><?= e(producer_request_field_label((string) $key)) ?></dt>
                            <dd><?= e(producer_request_display_value((string) $key, $value)) ?></dd>
                        </div>
                    <?php endforeach; ?>
                </dl>

                <form method="post">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="request_id" value="<?= (int) $manual_request['id'] ?>">
                    <input type="hidden" name="request_action" value="completed">
                    <button type="submit" class="btn btn-primary" <?= $manual_request_closed ? 'disabled' : '' ?>>
                        <i class="bi bi-check2-circle" aria-hidden="true"></i>
                        Mark Request Completed
                    </button>
                </form>
            </section>
        <?php endif; ?>

        <!-- Selected student workspace: add songs above, review/remove current songs below. -->
        <section class="admin-student-songs-card admin-songs-library">
            <div class="admin-managed-student-panel">
                <section class="admin-managed-student-summary">
                    <div>
                        <p class="dashboard-eyebrow">Selected Student</p>
                        <h3><?= e($managed_student['name']) ?>'s Songs</h3>
                        <p>
                            <?= e($managed_student['school_year'] ?: 'No class') ?>
                            · Rank <?= e($managed_student['rank'] ?: 'Debut') ?>
                            · <?= e($managed_student['producer_name'] ?: 'Unassigned') ?>
                        </p>
                    </div>
                    <strong><?= count($managed_student_songs) ?> song<?= count($managed_student_songs) === 1 ? '' : 's' ?></strong>
                </section>

                <section class="admin-student-song-add<?= $manual_request && ($manual_request['request_type'] ?? '') === 'song_add' ? ' request-field-highlight' : '' ?>">
                    <div>
                        <p class="dashboard-eyebrow">Manage Assignment</p>
                        <h4>Add existing song</h4>
                        <p>Search the global song library. Results exclude songs this student already has.</p>
                    </div>

                    <?php if (empty($available_student_songs)): ?>
                        <div class="admin-student-song-add-empty">
                            <i class="bi bi-music-note-list" aria-hidden="true"></i>
                            <span>Every global song is already assigned to this student.</span>
                        </div>
                    <?php else: ?>
                        <form method="post" class="admin-student-song-add-form" id="adminStudentSongAddForm">
                            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                            <input type="hidden" name="action" value="admin_add_student_song">
                            <input type="hidden" name="student_id" value="<?= (int) $managed_student['id'] ?>">
                            <input type="hidden" name="song_id" id="adminStudentSongAddSelectedId">
                            <?php if ($manual_request): ?>
                                <input type="hidden" name="request_id" value="<?= (int) $manual_request['id'] ?>">
                            <?php endif; ?>

                            <div class="admin-student-song-add-row">
                                <label class="admin-student-song-search-field">
                                    <span>Search song</span>
                                    <input type="search" id="adminStudentSongAddSearch" autocomplete="off" placeholder="Type song title, Japanese title, or artist">
                                </label>

                                <button type="submit" class="btn btn-primary" id="adminStudentSongAddSubmit" disabled>
                                    <i class="bi bi-plus-lg" aria-hidden="true"></i>
                                    Add Song
                                </button>
                            </div>

                            <div class="admin-student-song-picker">
                                <div class="admin-student-song-picker-list d-none" id="adminStudentSongAddResults" role="listbox" aria-label="Available songs">
                                    <?php foreach ($available_student_songs as $available_song): ?>
                                        <?php
                                        // Search picker matches title, Japanese title, artist, and song type.
                                        $available_song_search = strtolower(implode(' ', [
                                            $available_song['title'] ?? '',
                                            $available_song['title_jp'] ?? '',
                                            $available_song['artist'] ?? '',
                                            $available_song['song_type'] ?? '',
                                        ]));
                                        ?>
                                        <button
                                            type="button"
                                            class="admin-student-song-picker-option"
                                            data-admin-song-option
                                            data-song-id="<?= (int) $available_song['id'] ?>"
                                            data-song-search="<?= e($available_song_search) ?>"
                                            role="option"
                                            aria-selected="false">
                                            <span>
                                                <strong><?= e($available_song['title']) ?></strong>
                                                <?php if (!empty($available_song['title_jp']) && $available_song['title_jp'] !== $available_song['title']): ?>
                                                    <small lang="ja"><?= e($available_song['title_jp']) ?></small>
                                                <?php endif; ?>
                                                <small><?= e($available_song['artist'] ?: 'Unknown artist') ?></small>
                                            </span>
                                            <em><?= e($available_song['song_type']) ?></em>
                                        </button>
                                    <?php endforeach; ?>
                                </div>

                                <p class="admin-student-song-picker-empty d-none" id="adminStudentSongAddEmpty">No available songs match your search.</p>
                            </div>
                        </form>
                    <?php endif; ?>
                </section>

                <?php if (empty($managed_student_songs)): ?>
                    <div class="empty-dashboard-state admin-student-song-empty">
                        <strong>No songs assigned yet</strong>
                        <p>Add an existing song above to start this student's song list.</p>
                    </div>
                <?php else: ?>
                    <div class="song-library admin-managed-song-library">
                        <section class="song-category-section">
                            <div class="song-category-heading">
                                <div>
                                    <i class="bi bi-music-note-list"></i>
                                    <h2>Current Songs</h2>
                                </div>
                                <span><?= count($managed_student_songs) ?> tracks</span>
                            </div>

                            <div class="song-list admin-managed-song-list" role="list">
                                <div class="song-list-header admin-managed-song-list-header" aria-hidden="true">
                                    <span>#</span>
                                    <span>Title</span>
                                    <span>Artist</span>
                                    <span>Release</span>
                                    <span>Time</span>
                                    <span></span>
                                </div>

                                <?php foreach ($managed_student_songs as $index => $song): ?>
                                    <?php $collapse_id = 'adminManagedSongDetails' . (int) $song['id']; ?>
                                    <article class="song-track admin-managed-song-track<?= $manual_request_song_id === (int) $song['id'] ? ' request-field-highlight' : '' ?>" role="listitem">
                                        <button
                                            class="song-track-button collapsed"
                                            type="button"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#<?= e($collapse_id) ?>"
                                            aria-expanded="false"
                                            aria-controls="<?= e($collapse_id) ?>">
                                            <span class="song-track-number"><?= (int) ($index + 1) ?></span>
                                            <span class="song-track-title">
                                                <strong title="<?= e($song['title']) ?>"><?= e($song['title']) ?></strong>
                                                <?php if (!empty($song['title_jp']) && $song['title_jp'] !== $song['title']): ?>
                                                    <small lang="ja" title="<?= e($song['title_jp']) ?>"><?= e($song['title_jp']) ?></small>
                                                <?php endif; ?>
                                            </span>
                                            <span class="song-track-artist"><?= e($song['artist'] ?: 'Unknown artist') ?></span>
                                            <span class="song-track-release"><?= e(format_song_date($song['release_date'])) ?></span>
                                            <span class="song-track-duration"><?= e(format_song_duration($song['duration'])) ?></span>
                                            <i class="bi bi-chevron-down song-track-chevron"></i>
                                        </button>

                                        <div class="collapse" id="<?= e($collapse_id) ?>">
                                            <div class="song-track-details admin-managed-song-details">
                                                <dl>
                                                    <div>
                                                        <dt>Type</dt>
                                                        <dd><?= e($song['song_type']) ?></dd>
                                                    </div>
                                                    <div>
                                                        <dt>Added</dt>
                                                        <dd><?= e(format_song_date($song['added_at'])) ?></dd>
                                                    </div>
                                                    <div>
                                                        <dt>Notes</dt>
                                                        <dd><?= e($song['notes'] ?: 'No notes available.') ?></dd>
                                                    </div>
                                                </dl>

                                                <form method="post" class="admin-managed-song-remove-form">
                                                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                                    <input type="hidden" name="action" value="admin_remove_student_song">
                                                    <input type="hidden" name="student_id" value="<?= (int) $managed_student['id'] ?>">
                                                    <input type="hidden" name="song_id" value="<?= (int) $song['id'] ?>">
                                                    <?php if ($manual_request): ?>
                                                        <input type="hidden" name="request_id" value="<?= (int) $manual_request['id'] ?>">
                                                    <?php endif; ?>
                                                    <button type="submit" class="admin-song-small-button admin-song-danger-button" data-admin-remove-student-song="<?= e($song['title']) ?>">
                                                        <i class="bi bi-trash3" aria-hidden="true"></i>
                                                        Remove from student
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                        </section>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    <?php endif; ?>
</main>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const manageStudentSearch = document.getElementById('adminManageStudentSearch');
    const manageStudentCards = Array.from(document.querySelectorAll('[data-manage-student-card]'));
    const adminSongAddSearch = document.getElementById('adminStudentSongAddSearch');
    const adminSongAddOptions = Array.from(document.querySelectorAll('[data-admin-song-option]'));
    const adminSongAddSelectedId = document.getElementById('adminStudentSongAddSelectedId');
    const adminSongAddSubmit = document.getElementById('adminStudentSongAddSubmit');
    const adminSongAddResults = document.getElementById('adminStudentSongAddResults');
    const adminSongAddEmpty = document.getElementById('adminStudentSongAddEmpty');
    const removeStudentSongButtons = Array.from(document.querySelectorAll('[data-admin-remove-student-song]'));

    // Filter student cards locally for a fast picker experience.
    if (manageStudentSearch && manageStudentCards.length > 0) {
        manageStudentSearch.addEventListener('input', () => {
            const query = manageStudentSearch.value.trim().toLowerCase();

            manageStudentCards.forEach((card) => {
                card.hidden = query !== '' && !card.dataset.studentSearch.includes(query);
            });
        });
    }

    // The add-song picker stays hidden until admin types, then only shows matching available songs.
    if (adminSongAddSearch) {
        adminSongAddSearch.addEventListener('input', () => {
            const query = adminSongAddSearch.value.trim().toLowerCase();
            let visibleOptionCount = 0;

            if (adminSongAddSelectedId) {
                adminSongAddSelectedId.value = '';
            }

            if (adminSongAddSubmit) {
                adminSongAddSubmit.disabled = true;
            }

            adminSongAddOptions.forEach((option) => {
                option.classList.remove('is-selected');
                option.setAttribute('aria-selected', 'false');

                const isVisible = query !== '' && option.dataset.songSearch.includes(query);
                option.classList.toggle('d-none', !isVisible);

                if (isVisible) {
                    visibleOptionCount += 1;
                }
            });

            if (adminSongAddResults) {
                adminSongAddResults.classList.toggle('d-none', query === '');
            }

            if (adminSongAddEmpty) {
                adminSongAddEmpty.classList.toggle('d-none', query === '' || visibleOptionCount > 0);
            }
        });
    }

    // Selecting a song stores its ID in the hidden field and enables the Add Song button.
    adminSongAddOptions.forEach((option) => {
        option.addEventListener('click', () => {
            adminSongAddOptions.forEach((item) => {
                item.classList.remove('is-selected');
                item.setAttribute('aria-selected', 'false');
            });

            option.classList.add('is-selected');
            option.setAttribute('aria-selected', 'true');

            if (adminSongAddSelectedId) {
                adminSongAddSelectedId.value = option.dataset.songId || '';
            }

            if (adminSongAddSubmit) {
                adminSongAddSubmit.disabled = !option.dataset.songId;
            }
        });
    });

    // Removing from a student never deletes the global song, but still asks for confirmation.
    removeStudentSongButtons.forEach((button) => {
        button.addEventListener('click', (event) => {
            const songTitle = button.dataset.adminRemoveStudentSong || 'this song';
            const confirmed = window.confirm(`Remove "${songTitle}" from this student? The global song will not be deleted.`);

            if (!confirmed) {
                event.preventDefault();
            }
        });
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>
