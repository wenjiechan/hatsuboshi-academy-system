<?php
require_once '../includes/auth.php';
require_role('admin');

require_once '../config/database.php';
require_once '../includes/admin_song_helpers.php';

// Read filter and page state from the URL so search results can be shared/bookmarked.
$song_search = trim((string) ($_GET['song'] ?? ''));
$type_filter = trim((string) ($_GET['type'] ?? ''));
$page = max(1, (int) ($_GET['page'] ?? 1));
$per_page = 20;
$valid_types = ['Solo', 'Group', 'Remix', 'Cover'];
$show_create_form = isset($_GET['create']) && $_GET['create'] === '1';
$edit_song_id = isset($_GET['edit']) ? max(0, (int) $_GET['edit']) : 0;
$song_page_success = $_SESSION['admin_song_success'] ?? '';
$song_page_error = '';
$form_values = default_admin_song_form_values();
$selected_student_ids = [];
unset($_SESSION['admin_song_success']);

// Ignore unknown type values instead of sending invalid options into SQL filters.
if (!in_array($type_filter, $valid_types, true)) {
    $type_filter = '';
}

// Create, edit, and delete actions live in the helper so this page stays mostly layout/query focused.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_result = handle_admin_song_post($pdo, $valid_types, $form_values);
    $song_page_error = $post_result['song_page_error'];
    $form_values = $post_result['form_values'];
    $selected_student_ids = $post_result['selected_student_ids'];
    $show_create_form = $post_result['show_create_form'];
    $edit_song_id = $post_result['edit_song_id'];
}

// Admin can optionally assign a newly-created global song to students immediately.
// This list powers the collapsed "Who will use this song?" section in the create form.
$assignable_student_stmt = $pdo->query(
    'SELECT
        s.id,
        s.name,
        s.name_jp,
        s.school_year,
        p.username AS producer_name
     FROM students s
     INNER JOIN users u ON u.id = s.user_id AND u.is_active = 1
     LEFT JOIN users p ON p.id = s.producer_id
     ORDER BY
        s.school_year ASC,
        s.name ASC'
);
$assignable_students = $assignable_student_stmt->fetchAll();

$edit_song = null;
$edit_song_usage_count = 0;

// When editing, load the global song row plus its usage count for the warning badge.
if ($edit_song_id > 0) {
    $edit_stmt = $pdo->prepare(
        'SELECT
            so.*,
            COUNT(ss.id) AS usage_count
         FROM songs so
         LEFT JOIN student_songs ss ON ss.song_id = so.id
         WHERE so.id = ?
         GROUP BY so.id
         LIMIT 1'
    );
    $edit_stmt->execute([$edit_song_id]);
    $edit_song = $edit_stmt->fetch();

    if ($edit_song && $_SERVER['REQUEST_METHOD'] !== 'POST') {
        $form_values = [
            'title' => $edit_song['title'] ?? '',
            'title_jp' => $edit_song['title_jp'] ?? '',
            'artist' => $edit_song['artist'] ?? '',
            'duration' => $edit_song['duration'] ?? '',
            'release_date' => $edit_song['release_date'] ?? '',
            'song_type' => $edit_song['song_type'] ?? 'Group',
            'notes' => $edit_song['notes'] ?? '',
        ];
        $edit_song_usage_count = (int) ($edit_song['usage_count'] ?? 0);
    } elseif ($edit_song) {
        $edit_song_usage_count = (int) ($edit_song['usage_count'] ?? 0);
    } else {
        $edit_song_id = 0;
        $song_page_error = $song_page_error ?: 'The song you wanted to edit could not be found.';
    }
}

// Header counts describe the whole global song library, not only filtered results.
$summary_stmt = $pdo->query(
    'SELECT
        COUNT(*) AS total_songs,
        SUM(CASE WHEN song_type = "Solo" THEN 1 ELSE 0 END) AS solo_songs,
        SUM(CASE WHEN song_type = "Group" THEN 1 ELSE 0 END) AS group_songs,
        (
            SELECT COUNT(DISTINCT song_id)
            FROM student_songs
        ) AS assigned_songs
     FROM songs'
);
$summary = $summary_stmt->fetch() ?: [];

$where = [];
$params = [];

// Build the library filter WHERE clause once, then reuse it for count and page queries.
if ($song_search !== '') {
    $where[] = '(so.title LIKE ? OR so.title_jp LIKE ? OR so.artist LIKE ?)';
    $params[] = '%' . $song_search . '%';
    $params[] = '%' . $song_search . '%';
    $params[] = '%' . $song_search . '%';
}

if ($type_filter !== '') {
    $where[] = 'so.song_type = ?';
    $params[] = $type_filter;
}

$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$count_stmt = $pdo->prepare('SELECT COUNT(*) FROM songs so ' . $where_sql);
$count_stmt->execute($params);
$total_results = (int) $count_stmt->fetchColumn();

// Pagination is server-side so the global library can stay fast with many songs.
$total_pages = max(1, (int) ceil($total_results / $per_page));
$page = min($page, $total_pages);
$offset = ($page - 1) * $per_page;

// Load one page of songs with usage counts so admin can see which global rows affect students.
$song_stmt = $pdo->prepare(
    'SELECT
        so.id,
        so.title,
        so.title_jp,
        so.artist,
        so.duration,
        so.release_date,
        so.song_type,
        so.notes,
        so.updated_at,
        COUNT(ss.id) AS usage_count
     FROM songs so
     LEFT JOIN student_songs ss ON ss.song_id = so.id
     ' . $where_sql . '
     GROUP BY
        so.id,
        so.title,
        so.title_jp,
        so.artist,
        so.duration,
        so.release_date,
        so.song_type,
        so.notes,
        so.updated_at
     ORDER BY
        FIELD(so.song_type, "Solo", "Group", "Remix", "Cover"),
        so.title ASC
     LIMIT ' . (int) $per_page . ' OFFSET ' . (int) $offset
);
$song_stmt->execute($params);
$songs = $song_stmt->fetchAll();

$category_order = ['Solo', 'Group', 'Remix', 'Cover'];
$songs_by_category = array_fill_keys($category_order, []);

// Group only the current page of songs so the UI matches student/producer song pages.
foreach ($songs as $song) {
    $category = $song['song_type'] ?: 'Group';
    $songs_by_category[$category][] = $song;
}

$category_icons = [
    'Solo' => 'bi-person-fill',
    'Group' => 'bi-people-fill',
    'Remix' => 'bi-vinyl-fill',
    'Cover' => 'bi-mic-fill',
];

$song_usage = [];
$song_ids = array_map(static fn(array $song): int => (int) $song['id'], $songs);

// Fetch usage for the visible songs only; each details panel reads from this map.
if (!empty($song_ids)) {
    $placeholders = implode(',', array_fill(0, count($song_ids), '?'));

    // Usage details show exactly which students will be affected by future edits.
    $usage_stmt = $pdo->prepare(
        'SELECT
            ss.song_id,
            ss.added_at,
            s.name AS student_name,
            s.name_jp AS student_name_jp,
            s.school_year,
            s.producer_id,
            p.username AS producer_name
         FROM student_songs ss
         INNER JOIN students s ON s.id = ss.student_id
         LEFT JOIN users p ON p.id = s.producer_id
         WHERE ss.song_id IN (' . $placeholders . ')
         ORDER BY s.school_year, s.name'
    );
    $usage_stmt->execute($song_ids);

    foreach ($usage_stmt->fetchAll() as $usage_row) {
        $song_usage[(int) $usage_row['song_id']][] = $usage_row;
    }
}

$page_title = 'Songs';
$pagination_base_params = array_filter([
    'song' => $song_search,
    'type' => $type_filter,
], static fn ($value) => $value !== '');

$page_styles = [
    '/gakumas-sms/assets/css/pages/song.css',
    '/gakumas-sms/assets/css/pages/admin-songs.css?v=20260720b',
];
require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

<main class="dashboard-main admin-songs-main">
    <section class="admin-songs-hero">
        <div>
            <p class="dashboard-eyebrow">Song Library</p>
            <h2>Global Songs</h2>
            <p>Search the global song library and review which students are using each song.</p>
        </div>

        <div class="admin-songs-summary-grid" aria-label="Song library summary">
            <div>
                <span>Total Songs</span>
                <strong><?= (int) ($summary['total_songs'] ?? 0) ?></strong>
            </div>

            <div>
                <span>Solo Songs</span>
                <strong><?= (int) ($summary['solo_songs'] ?? 0) ?></strong>
            </div>

            <div>
                <span>Group Songs</span>
                <strong><?= (int) ($summary['group_songs'] ?? 0) ?></strong>
            </div>

            <div>
                <span>Assigned Songs</span>
                <strong><?= (int) ($summary['assigned_songs'] ?? 0) ?></strong>
            </div>
        </div>
    </section>

    <section class="admin-songs-filters">
        <form method="get" class="admin-songs-filter-form">
            <label>
                <span>Song / Artist</span>
                <input type="search" name="song" value="<?= e($song_search) ?>" placeholder="Search title, Japanese title, or artist">
            </label>

            <label>
                <span>Type</span>
                <select name="type">
                    <option value="">All types</option>
                    <?php foreach ($valid_types as $type): ?>
                        <option value="<?= e($type) ?>" <?= $type_filter === $type ? 'selected' : '' ?>>
                            <?= e($type) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <div class="admin-songs-filter-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search" aria-hidden="true"></i>
                    Search
                </button>
                <a href="/gakumas-sms/admin/songs.php?create=1#song-form" class="btn btn-primary">
                    <i class="bi bi-plus-lg" aria-hidden="true"></i>
                    Add Song
                </a>
                <a href="/gakumas-sms/admin/student_songs.php" class="admin-songs-reset-button">
                    <i class="bi bi-person-lines-fill" aria-hidden="true"></i>
                    Manage Student Songs
                </a>
            </div>
        </form>
    </section>

    <?php if ($song_page_success !== ''): ?>
        <div class="alert alert-success" role="status"><?= e($song_page_success) ?></div>
    <?php endif; ?>

    <?php if ($song_page_error !== ''): ?>
        <div class="alert alert-danger" role="alert"><?= e($song_page_error) ?></div>
    <?php endif; ?>

    <?php if ($show_create_form || $edit_song): ?>
        <section class="admin-song-form-card" id="song-form">
            <div class="admin-song-form-heading">
                <div>
                    <p class="dashboard-eyebrow"><?= $edit_song ? 'Edit Global Song' : 'Create Global Song' ?></p>
                    <h3><?= $edit_song ? 'Edit song details' : 'Add new song' ?></h3>
                    <p><?= $edit_song ? 'Editing this global song affects every student using it.' : 'Create a new global song row that producers/admin can assign to students.' ?></p>
                </div>

                <?php if ($edit_song): ?>
                    <span><?= (int) $edit_song_usage_count ?> student<?= $edit_song_usage_count === 1 ? '' : 's' ?> using this song</span>
                <?php endif; ?>
            </div>

            <form method="post" class="admin-song-form">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="action" value="<?= $edit_song ? 'update_song' : 'create_song' ?>">
                <?php if ($edit_song): ?>
                    <input type="hidden" name="song_id" value="<?= (int) $edit_song['id'] ?>">
                <?php endif; ?>

                <div class="admin-song-form-grid">
                    <label>
                        <span>Title</span>
                        <input type="text" name="title" value="<?= e($form_values['title']) ?>" maxlength="150" required>
                    </label>

                    <label>
                        <span>Japanese Title</span>
                        <input type="text" name="title_jp" value="<?= e($form_values['title_jp']) ?>" maxlength="150">
                    </label>

                    <label>
                        <span>Artist</span>
                        <input type="text" name="artist" value="<?= e($form_values['artist']) ?>" maxlength="150">
                    </label>

                    <label>
                        <span>Song Type</span>
                        <select name="song_type" required>
                            <?php foreach ($valid_types as $type): ?>
                                <option value="<?= e($type) ?>" <?= $form_values['song_type'] === $type ? 'selected' : '' ?>>
                                    <?= e($type) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>

                    <label>
                        <span>Duration</span>
                        <input type="text" name="duration" value="<?= e($form_values['duration']) ?>" placeholder="MM:SS or HH:MM:SS">
                    </label>

                    <label>
                        <span>Release Date</span>
                        <input type="date" name="release_date" value="<?= e($form_values['release_date']) ?>">
                    </label>
                </div>

                <label class="admin-song-notes-field">
                    <span>Notes</span>
                    <textarea name="notes" rows="4"><?= e($form_values['notes']) ?></textarea>
                </label>

                <?php if (!$edit_song): ?>
                    <section class="admin-song-assignment-field" aria-labelledby="song-assignment-title">
                        <div class="admin-song-assignment-heading">
                            <div>
                                <span id="song-assignment-title">Who will use this song?</span>
                                <small>Select students here and the song will be added to their student song list immediately.</small>
                            </div>

                            <div class="admin-song-assignment-heading-actions">
                                <strong><?= count($assignable_students) ?> available</strong>
                                <button
                                    class="admin-song-assignment-toggle"
                                    type="button"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#adminSongAssignmentCollapse"
                                    aria-expanded="<?= !empty($selected_student_ids) ? 'true' : 'false' ?>"
                                    aria-controls="adminSongAssignmentCollapse"
                                >
                                    <i class="bi bi-chevron-down" aria-hidden="true"></i>
                                    <span>
                                        Choose Students
                                        <small id="adminSongSelectedCount">(<?= count($selected_student_ids) ?> selected)</small>
                                    </span>
                                </button>
                            </div>
                        </div>

                        <div class="collapse <?= !empty($selected_student_ids) ? 'show' : '' ?>" id="adminSongAssignmentCollapse">
                            <div class="admin-song-assignment-body">
                                <div class="admin-song-student-tools">
                                    <label class="admin-song-student-search">
                                        <span>Search student</span>
                                        <input
                                            type="search"
                                            id="adminSongStudentSearch"
                                            placeholder="Search by student, Japanese name, class, or producer"
                                            autocomplete="off"
                                        >
                                    </label>

                                    <button type="button" class="admin-song-clear-students" id="adminSongClearStudents">
                                        <i class="bi bi-x-circle" aria-hidden="true"></i>
                                        Clear selected
                                    </button>
                                </div>

                        <?php if (empty($assignable_students)): ?>
                            <p class="admin-song-assignment-empty">No active students are available for assignment.</p>
                        <?php else: ?>
                            <div class="admin-song-student-picker" id="adminSongStudentPicker">
                                <?php foreach ($assignable_students as $student): ?>
                                    <?php
                                    $student_search_text = implode(' ', [
                                        $student['name'] ?? '',
                                        $student['name_jp'] ?? '',
                                        $student['school_year'] ?? '',
                                        $student['producer_name'] ?? 'Unassigned',
                                    ]);
                                    ?>
                                    <label class="admin-song-student-option" data-student-search="<?= e(mb_strtolower($student_search_text)) ?>">
                                        <input
                                            type="checkbox"
                                            name="student_ids[]"
                                            value="<?= (int) $student['id'] ?>"
                                            <?= in_array((int) $student['id'], $selected_student_ids, true) ? 'checked' : '' ?>
                                        >
                                        <span>
                                            <strong><?= e($student['name']) ?></strong>
                                            <?php if (!empty($student['name_jp'])): ?>
                                                <small lang="ja"><?= e($student['name_jp']) ?></small>
                                            <?php endif; ?>
                                        </span>
                                        <em><?= e($student['school_year'] ?: 'No class') ?> · <?= e($student['producer_name'] ?: 'Unassigned') ?></em>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                            </div>
                        </div>
                    </section>
                <?php endif; ?>

                <div class="admin-song-form-actions">
                    <a href="/gakumas-sms/admin/songs.php" class="admin-songs-reset-button">
                        <i class="bi bi-x-lg" aria-hidden="true"></i>
                        Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save" aria-hidden="true"></i>
                        <?= $edit_song ? 'Save Changes' : 'Create Song' ?>
                    </button>
                </div>
            </form>
        </section>
    <?php endif; ?>


    <section class="admin-songs-library">
        <div class="admin-songs-library-heading">
            <div>
                <p class="dashboard-eyebrow">Library Results</p>
                <h3>All songs</h3>
            </div>
            <span>
                <?= (int) $total_results ?> result<?= $total_results === 1 ? '' : 's' ?>
                · Page <?= (int) $page ?> of <?= (int) $total_pages ?>
            </span>
        </div>

        <?php if (empty($songs)): ?>
            <div class="empty-dashboard-state">
                <strong>No songs found</strong>
                <p>Try another title, artist, or type filter.</p>
            </div>
        <?php else: ?>
            <?php if ($total_pages > 1): ?>
                <nav class="admin-song-pagination admin-song-pagination-top" aria-label="Song library pagination">
                    <?php
                    $first_params = array_merge($pagination_base_params, ['page' => 1]);
                    $previous_params = array_merge($pagination_base_params, ['page' => max(1, $page - 1)]);
                    $next_params = array_merge($pagination_base_params, ['page' => min($total_pages, $page + 1)]);
                    $last_params = array_merge($pagination_base_params, ['page' => $total_pages]);
                    ?>
                    <a class="<?= $page <= 1 ? 'is-disabled' : '' ?>" href="/gakumas-sms/admin/songs.php?<?= e(http_build_query($first_params)) ?>" aria-label="First page" title="First page">
                        <i class="bi bi-chevron-double-left" aria-hidden="true"></i>
                    </a>

                    <a class="<?= $page <= 1 ? 'is-disabled' : '' ?>" href="/gakumas-sms/admin/songs.php?<?= e(http_build_query($previous_params)) ?>" aria-label="Previous page" title="Previous page">
                        <i class="bi bi-chevron-left" aria-hidden="true"></i>
                    </a>

                    <span>Page <?= (int) $page ?> / <?= (int) $total_pages ?></span>

                    <a class="<?= $page >= $total_pages ? 'is-disabled' : '' ?>" href="/gakumas-sms/admin/songs.php?<?= e(http_build_query($next_params)) ?>" aria-label="Next page" title="Next page">
                        <i class="bi bi-chevron-right" aria-hidden="true"></i>
                    </a>

                    <a class="<?= $page >= $total_pages ? 'is-disabled' : '' ?>" href="/gakumas-sms/admin/songs.php?<?= e(http_build_query($last_params)) ?>" aria-label="Last page" title="Last page">
                        <i class="bi bi-chevron-double-right" aria-hidden="true"></i>
                    </a>
                </nav>
            <?php endif; ?>

            <div class="song-library admin-song-library-list">
                <?php $page_row_number = $offset; ?>
                <?php foreach ($category_order as $category): ?>
                    <?php if (empty($songs_by_category[$category])): ?>
                        <?php continue; ?>
                    <?php endif; ?>

                    <section class="song-category-section">
                        <div class="song-category-heading">
                            <div>
                                <i class="bi <?= e($category_icons[$category] ?? 'bi-music-note-list') ?>"></i>
                                <h2><?= e($category) ?></h2>
                            </div>
                            <span><?= count($songs_by_category[$category]) ?> shown on this page</span>
                        </div>

                        <div class="song-list admin-song-list" role="list">
                            <div class="song-list-header admin-song-list-header">
                                <span>
                                    #
                                    <button type="button" class="song-sort-button" data-sort-column="number" data-sort-direction="asc" aria-label="Sort number ascending">
                                        <i class="bi bi-sort-numeric-down" aria-hidden="true"></i>
                                    </button>
                                </span>
                                <span>
                                    Title
                                    <button type="button" class="song-sort-button" data-sort-column="title" data-sort-direction="asc" aria-label="Sort title ascending">
                                        <i class="bi bi-sort-alpha-down" aria-hidden="true"></i>
                                    </button>
                                </span>
                                <span>Artist</span>
                                <span>
                                    Release
                                    <button type="button" class="song-sort-button" data-sort-column="release" data-sort-direction="asc" aria-label="Sort release ascending">
                                        <i class="bi bi-sort-numeric-down" aria-hidden="true"></i>
                                    </button>
                                </span>
                                <span>
                                    Time
                                    <button type="button" class="song-sort-button" data-sort-column="duration" data-sort-direction="asc" aria-label="Sort time ascending">
                                        <i class="bi bi-sort-numeric-down" aria-hidden="true"></i>
                                    </button>
                                </span>
                                <span>
                                    Used
                                    <button type="button" class="song-sort-button" data-sort-column="usage" data-sort-direction="asc" aria-label="Sort used ascending">
                                        <i class="bi bi-sort-numeric-down" aria-hidden="true"></i>
                                    </button>
                                </span>
                                <span></span>
                            </div>

                            <?php foreach ($songs_by_category[$category] as $index => $song): ?>
                                <?php
                                $song_id = (int) $song['id'];
                                $usage_rows = $song_usage[$song_id] ?? [];
                                $details_id = 'songDetails' . $song_id;
                                $usage_id = 'songUsage' . $song_id;
                                $page_row_number++;
                                $row_number = $page_row_number;
                                ?>

                                <article
                                    class="song-track admin-song-track"
                                    data-sort-number="<?= (int) $row_number ?>"
                                    data-sort-title="<?= e($song['title']) ?>"
                                    data-sort-release="<?= e($song['release_date'] ?: '') ?>"
                                    data-sort-duration="<?= e($song['duration'] ?: '') ?>"
                                    data-sort-usage="<?= (int) $song['usage_count'] ?>"
                                    role="listitem">
                                    <button
                                        class="admin-song-track-row collapsed"
                                        type="button"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#<?= e($details_id) ?>"
                                        aria-expanded="false"
                                        aria-controls="<?= e($details_id) ?>">
                                        <span class="song-track-number"><?= (int) $row_number ?></span>

                                        <span class="song-track-title">
                                            <strong title="<?= e($song['title']) ?>"><?= e($song['title']) ?></strong>
                                            <?php if (!empty($song['title_jp']) && $song['title_jp'] !== $song['title']): ?>
                                                <small lang="ja" title="<?= e($song['title_jp']) ?>"><?= e($song['title_jp']) ?></small>
                                            <?php endif; ?>
                                        </span>

                                        <span class="song-track-artist"><?= e($song['artist'] ?: 'Unknown artist') ?></span>
                                        <span class="song-track-release"><?= e(format_song_date($song['release_date'])) ?></span>
                                        <span class="song-track-duration"><?= e(format_song_duration($song['duration'])) ?></span>
                                        <span class="admin-song-usage-count"><?= (int) $song['usage_count'] ?></span>
                                        <i class="bi bi-chevron-down song-track-chevron" aria-hidden="true"></i>
                                    </button>

                                    <div class="collapse" id="<?= e($details_id) ?>">
                                        <div class="song-track-details admin-song-details-panel">
                                            <dl>
                                                <div>
                                                    <dt>Title</dt>
                                                    <dd><?= e($song['title']) ?></dd>
                                                </div>
                                                <div>
                                                    <dt>Japanese Title</dt>
                                                    <dd lang="ja"><?= e($song['title_jp'] ?: 'Not listed') ?></dd>
                                                </div>
                                                <div>
                                                    <dt>Artist</dt>
                                                    <dd><?= e($song['artist'] ?: 'Unknown artist') ?></dd>
                                                </div>
                                                <div>
                                                    <dt>Type</dt>
                                                    <dd><?= e($song['song_type']) ?></dd>
                                                </div>
                                                <div>
                                                    <dt>Duration</dt>
                                                    <dd><?= e(format_song_duration($song['duration'])) ?></dd>
                                                </div>
                                                <div>
                                                    <dt>Release Date</dt>
                                                    <dd><?= e(format_song_date($song['release_date'])) ?></dd>
                                                </div>
                                                <div>
                                                    <dt>Used By</dt>
                                                    <dd><?= (int) $song['usage_count'] ?> student<?= (int) $song['usage_count'] === 1 ? '' : 's' ?></dd>
                                                </div>
                                                <div>
                                                    <dt>Last Updated</dt>
                                                    <dd><?= e(format_song_date($song['updated_at'])) ?></dd>
                                                </div>
                                            </dl>

                                            <div class="song-notes">
                                                <span>Notes</span>
                                                <p><?= e($song['notes'] ?: 'No notes available for this song.') ?></p>
                                            </div>

                                            <div class="admin-song-details-actions">
                                                <a
                                                    href="/gakumas-sms/admin/songs.php?edit=<?= (int) $song_id ?>#song-form"
                                                    class="admin-song-small-button">
                                                    <i class="bi bi-pencil" aria-hidden="true"></i>
                                                    Edit Song
                                                </a>

                                                <?php if ((int) $song['usage_count'] === 0): ?>
                                                    <form
                                                        method="post"
                                                        onsubmit="return confirm('Delete this song from the global library? This cannot be undone.');">
                                                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                                        <input type="hidden" name="action" value="delete_song">
                                                        <input type="hidden" name="song_id" value="<?= (int) $song_id ?>">
                                                        <button type="submit" class="admin-song-small-button admin-song-danger-button">
                                                            <i class="bi bi-trash" aria-hidden="true"></i>
                                                            Delete Song
                                                        </button>
                                                    </form>
                                                <?php else: ?>
                                                    <button
                                                        type="button"
                                                        class="admin-song-small-button"
                                                        data-bs-toggle="collapse"
                                                        data-bs-target="#<?= e($usage_id) ?>"
                                                        aria-expanded="false"
                                                        aria-controls="<?= e($usage_id) ?>">
                                                        <i class="bi bi-people" aria-hidden="true"></i>
                                                        Review Usage
                                                    </button>

                                                    <form
                                                        method="post"
                                                        onsubmit="return confirm('This song is used by <?= (int) $song['usage_count'] ?> student<?= (int) $song['usage_count'] === 1 ? '' : 's' ?>. This will remove it from every student song list and delete the global song. This cannot be undone. Continue?');">
                                                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                                        <input type="hidden" name="action" value="delete_song_with_usage">
                                                        <input type="hidden" name="song_id" value="<?= (int) $song_id ?>">
                                                        <button type="submit" class="admin-song-small-button admin-song-danger-button">
                                                            <i class="bi bi-trash3" aria-hidden="true"></i>
                                                            Remove From All & Delete
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="collapse" id="<?= e($usage_id) ?>">
                                        <div class="song-track-details admin-song-usage-panel">
                                            <?php if (empty($usage_rows)): ?>
                                                <p>This song is not currently assigned to any student.</p>
                                            <?php else: ?>
                                                <div class="admin-song-usage-list">
                                                    <?php foreach ($usage_rows as $usage): ?>
                                                        <article class="admin-song-usage-card">
                                                            <div>
                                                                <strong><?= e($usage['student_name']) ?></strong>
                                                                <?php if (!empty($usage['student_name_jp'])): ?>
                                                                    <small lang="ja"><?= e($usage['student_name_jp']) ?></small>
                                                                <?php endif; ?>
                                                            </div>
                                                            <span><?= e($usage['school_year'] ?: 'No class') ?></span>
                                                            <span><?= e($usage['producer_name'] ?: 'Unassigned') ?></span>
                                                            <span>Added <?= e(format_song_date($usage['added_at'])) ?></span>
                                                        </article>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endforeach; ?>
            </div>

        <?php endif; ?>
    </section>
</main>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const songSortButtons = Array.from(document.querySelectorAll('.song-sort-button'));
    const studentSearch = document.getElementById('adminSongStudentSearch');
    const studentOptions = document.querySelectorAll('#adminSongAssignmentCollapse [data-student-search]');
    const studentCheckboxes = document.querySelectorAll('input[name="student_ids[]"]');
    const selectedCount = document.getElementById('adminSongSelectedCount');
    const clearStudents = document.getElementById('adminSongClearStudents');

    const durationToSeconds = (value) => {
        if (!value) {
            return 0;
        }

        const parts = value.split(':').map((part) => Number.parseInt(part, 10) || 0);

        if (parts.length === 3) {
            return (parts[0] * 3600) + (parts[1] * 60) + parts[2];
        }

        if (parts.length === 2) {
            return (parts[0] * 60) + parts[1];
        }

        return 0;
    };

    songSortButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const list = button.closest('.admin-song-list');
            const sortColumn = button.dataset.sortColumn;

            if (!list || !sortColumn) {
                return;
            }

            const currentDirection = button.dataset.sortDirection || 'asc';
            const nextDirection = currentDirection === 'asc' ? 'desc' : 'asc';
            const tracks = Array.from(list.querySelectorAll('.admin-song-track'));

            tracks.sort((firstTrack, secondTrack) => {
                const sortKey = `sort${sortColumn.charAt(0).toUpperCase() + sortColumn.slice(1)}`;
                const firstValue = firstTrack.dataset[sortKey] || '';
                const secondValue = secondTrack.dataset[sortKey] || '';
                let comparison = 0;

                if (['number', 'usage'].includes(sortColumn)) {
                    comparison = (Number.parseInt(firstValue, 10) || 0) - (Number.parseInt(secondValue, 10) || 0);
                } else if (sortColumn === 'duration') {
                    comparison = durationToSeconds(firstValue) - durationToSeconds(secondValue);
                } else if (sortColumn === 'release') {
                    comparison = new Date(firstValue || '1900-01-01') - new Date(secondValue || '1900-01-01');
                } else {
                    comparison = firstValue.localeCompare(secondValue, undefined, { sensitivity: 'base' });
                }

                return nextDirection === 'asc' ? comparison : comparison * -1;
            });

            tracks.forEach((track) => list.appendChild(track));

            button.dataset.sortDirection = nextDirection;
            button.setAttribute('aria-label', `Sort ${sortColumn} ${nextDirection === 'asc' ? 'ascending' : 'descending'}`);

            const icon = button.querySelector('i');
            if (icon) {
                const isNumeric = ['number', 'release', 'duration', 'usage'].includes(sortColumn);
                icon.className = `bi ${isNumeric
                    ? (nextDirection === 'asc' ? 'bi-sort-numeric-down' : 'bi-sort-numeric-up')
                    : (nextDirection === 'asc' ? 'bi-sort-alpha-down' : 'bi-sort-alpha-up')}`;
            }
        });
    });

    const updateSelectedCount = () => {
        if (!selectedCount) {
            return;
        }

        const checkedCount = Array.from(studentCheckboxes).filter((checkbox) => checkbox.checked).length;
        selectedCount.textContent = `(${checkedCount} selected)`;
    };

    if (studentSearch && studentOptions.length > 0) {
        studentSearch.addEventListener('input', () => {
            const query = studentSearch.value.trim().toLowerCase();

            studentOptions.forEach((option) => {
                option.hidden = query !== '' && !option.dataset.studentSearch.includes(query);
            });
        });

        studentCheckboxes.forEach((checkbox) => {
            checkbox.addEventListener('change', updateSelectedCount);
        });

        if (clearStudents) {
            clearStudents.addEventListener('click', () => {
                studentCheckboxes.forEach((checkbox) => {
                    checkbox.checked = false;
                });

                updateSelectedCount();
            });
        }

        updateSelectedCount();
    }

});
</script>

<?php require_once '../includes/footer.php'; ?>
