<?php
require_once '../includes/auth.php';
require_role('producer');

require_once '../config/database.php';
require_once '../includes/theme_settings_helpers.php';
require_once '../includes/producer_request_helpers.php';

// Escape text before printing it into HTML.
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

// Display TIME values as MM:SS for the song list.
function format_song_duration(?string $duration): string
{
    if (!$duration) {
        return '--:--';
    }

    $parts = explode(':', $duration);

    if (count($parts) !== 3) {
        return $duration;
    }

    return sprintf('%02d:%02d', (int) $parts[1], (int) $parts[2]);
}

// Display nullable release dates in a readable format.
function format_song_date(?string $date): string
{
    if (!$date) {
        return 'Not dated';
    }

    return date('M j, Y', strtotime($date));
}

// Load the current producer account and theme colors.
$stmt = $pdo->prepare(
    'SELECT id, username, theme_primary_color, theme_secondary_color
     FROM users
     WHERE id = ?
       AND role = ?
     LIMIT 1'
);

$stmt->execute([$_SESSION['id'], 'producer']);
$producer = $stmt->fetch();

if (!$producer) {
    redirect_to_account_issue(
        'Producer profile not found',
        'Your login is active, but no producer account is linked to this session. Please log out and ask an administrator to check your account setup.',
        404
    );
}

$_SESSION['theme_primary_color'] = $producer['theme_primary_color'] ?: ($_SESSION['theme_primary_color'] ?? DEFAULT_THEME_PRIMARY);
$_SESSION['theme_secondary_color'] = $producer['theme_secondary_color'] ?: ($_SESSION['theme_secondary_color'] ?? DEFAULT_THEME_SECONDARY);

$producer_song_success = $_SESSION['producer_song_success'] ?? null;
$producer_song_error = $_SESSION['producer_song_error'] ?? null;
unset($_SESSION['producer_song_success'], $_SESSION['producer_song_error']);

// Read the filter values from the URL so filtered views can be refreshed/bookmarked.
$student_filter = trim((string) ($_GET['student'] ?? ''));
$class_filter = trim((string) ($_GET['class'] ?? ''));
$song_filter = trim((string) ($_GET['song'] ?? ''));
$selected_student_id = isset($_GET['student_id']) ? max(0, (int) $_GET['student_id']) : 0;
$manual_request_id = max(0, (int) ($_GET['request_id'] ?? $_POST['request_id'] ?? 0));
$manual_request = $manual_request_id > 0 ? load_producer_request_detail($pdo, (int) $producer['id'], $manual_request_id) : null;

if ($manual_request) {
    $selected_student_id = (int) ($manual_request['student_id'] ?? $selected_student_id);
}

$song_like = '%' . $song_filter . '%';

// Add or remove a song assignment. The global song row is never edited here.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf((string) ($_POST['csrf_token'] ?? ''));

    if (($_POST['request_action'] ?? '') === 'completed' && $manual_request) {
        try {
            $_SESSION['producer_song_success'] = producer_request_handle_action($pdo, $manual_request, (int) $producer['id'], 'producer', 'completed');
            $_SESSION['producer_request_success'] = $_SESSION['producer_song_success'];
        } catch (Throwable $exception) {
            $_SESSION['producer_request_error'] = $exception->getMessage();
        }

        header('Location: ' . producer_request_detail_url($manual_request, 'producer'));
        exit;
    }

    $action = (string) ($_POST['song_action'] ?? '');
    $posted_student_id = filter_input(INPUT_POST, 'student_id', FILTER_VALIDATE_INT);
    $posted_song_id = filter_input(INPUT_POST, 'song_id', FILTER_VALIDATE_INT);
    $return_query = trim((string) ($_POST['return_query'] ?? ''));
    $redirect_url = '/gakumas-sms/producer/songs.php';

    if ($return_query !== '') {
        parse_str(ltrim($return_query, '?'), $return_params);
        $safe_return_params = [];

        foreach (['student', 'class', 'song'] as $return_key) {
            if (isset($return_params[$return_key]) && is_scalar($return_params[$return_key]) && trim((string) $return_params[$return_key]) !== '') {
                $safe_return_params[$return_key] = trim((string) $return_params[$return_key]);
            }
        }

        if (isset($return_params['student_id']) && filter_var($return_params['student_id'], FILTER_VALIDATE_INT)) {
            $safe_return_params['student_id'] = (int) $return_params['student_id'];
        }

        if (isset($return_params['request_id']) && filter_var($return_params['request_id'], FILTER_VALIDATE_INT)) {
            $safe_return_params['request_id'] = (int) $return_params['request_id'];
        }

        if (!empty($safe_return_params)) {
            $redirect_url .= '?' . http_build_query($safe_return_params);
        }
    } elseif ($posted_student_id && $posted_student_id > 0) {
        $redirect_url .= '?student_id=' . (int) $posted_student_id;
    }

    $redirect_url .= '#studentSongs';

    if (!$posted_student_id || $posted_student_id <= 0 || !$posted_song_id || $posted_song_id <= 0) {
        $_SESSION['producer_song_error'] = 'Please choose a valid student and song.';
        header('Location: ' . $redirect_url);
        exit;
    }

    $ownership_stmt = $pdo->prepare(
        'SELECT id, name
         FROM students
         WHERE id = ?
           AND producer_id = ?
         LIMIT 1'
    );
    $ownership_stmt->execute([$posted_student_id, $producer['id']]);
    $owned_student = $ownership_stmt->fetch();

    if (!$owned_student) {
        $_SESSION['producer_song_error'] = 'You can only manage songs for your assigned students.';
        header('Location: ' . $redirect_url);
        exit;
    }

    if ($action === 'add') {
        $song_exists_stmt = $pdo->prepare('SELECT title FROM songs WHERE id = ? LIMIT 1');
        $song_exists_stmt->execute([$posted_song_id]);
        $song_to_add = $song_exists_stmt->fetch();

        if (!$song_to_add) {
            $_SESSION['producer_song_error'] = 'The selected song could not be found.';
            header('Location: ' . $redirect_url);
            exit;
        }

        try {
            $add_stmt = $pdo->prepare(
                'INSERT INTO student_songs (student_id, song_id, added_by)
                 VALUES (?, ?, ?)'
            );
            $add_stmt->execute([$posted_student_id, $posted_song_id, $producer['id']]);
            $_SESSION['producer_song_success'] = 'Song added to ' . $owned_student['name'] . '.';
        } catch (PDOException $exception) {
            if ($exception->getCode() === '23000') {
                $_SESSION['producer_song_error'] = 'This student already has that song.';
            } else {
                $_SESSION['producer_song_error'] = 'The song could not be added. Please try again.';
            }
        }
    } elseif ($action === 'remove') {
        $remove_stmt = $pdo->prepare(
            'DELETE FROM student_songs
             WHERE student_id = ?
               AND song_id = ?
             LIMIT 1'
        );
        $remove_stmt->execute([$posted_student_id, $posted_song_id]);

        if ($remove_stmt->rowCount() > 0) {
            $_SESSION['producer_song_success'] = 'Song removed from ' . $owned_student['name'] . '.';
        } else {
            $_SESSION['producer_song_error'] = 'That song is not assigned to this student.';
        }
    } else {
        $_SESSION['producer_song_error'] = 'Unknown song action.';
    }

    header('Location: ' . $redirect_url);
    exit;
}

// Header summary counts include all students assigned to this producer, not only filtered results.
$stats_stmt = $pdo->prepare(
    'SELECT
        COUNT(DISTINCT s.id) AS assigned_students,
        SUM(CASE WHEN so.song_type = "Solo" THEN 1 ELSE 0 END) AS solo_songs,
        SUM(CASE WHEN so.song_type = "Group" THEN 1 ELSE 0 END) AS group_songs
     FROM students s
     LEFT JOIN student_songs ss ON ss.student_id = s.id
     LEFT JOIN songs so ON so.id = ss.song_id
     WHERE s.producer_id = ?'
);
$stats_stmt->execute([$producer['id']]);
$stats = $stats_stmt->fetch() ?: [];

$where = ['s.producer_id = ?'];
$params = [$producer['id']];
$student_select_params = [];
$matching_song_select = 'NULL AS matching_song_titles, 0 AS matching_song_count';

// Combine the three filter boxes with AND; each individual box can match related fields with OR.
if ($student_filter !== '') {
    $where[] = '(s.name LIKE ? OR s.name_jp LIKE ?)';
    $params[] = '%' . $student_filter . '%';
    $params[] = '%' . $student_filter . '%';
}

if ($class_filter !== '') {
    $where[] = 's.school_year LIKE ?';
    $params[] = '%' . $class_filter . '%';
}

if ($song_filter !== '') {
    // When searching by song, show which song titles caused each student card to match.
    $matching_song_select = 'GROUP_CONCAT(DISTINCT CASE
            WHEN so.title LIKE ? OR so.title_jp LIKE ? OR so.artist LIKE ?
            THEN so.title
            ELSE NULL
        END ORDER BY so.title SEPARATOR ", ") AS matching_song_titles,
        SUM(CASE
            WHEN so.title LIKE ? OR so.title_jp LIKE ? OR so.artist LIKE ?
            THEN 1
            ELSE 0
        END) AS matching_song_count';
    $student_select_params = [$song_like, $song_like, $song_like, $song_like, $song_like, $song_like];

    $where[] = 'EXISTS (
        SELECT 1
        FROM student_songs ss_filter
        INNER JOIN songs so_filter ON so_filter.id = ss_filter.song_id
        WHERE ss_filter.student_id = s.id
          AND (
            so_filter.title LIKE ?
            OR so_filter.title_jp LIKE ?
            OR so_filter.artist LIKE ?
          )
    )';
    $params[] = $song_like;
    $params[] = $song_like;
    $params[] = $song_like;
}

// Load the filtered student cards. Producers only see their own assigned students.
$student_stmt = $pdo->prepare(
    'SELECT
        s.id,
        s.name,
        s.name_jp,
        s.school_year,
        s.rank,
        u.avatar,
        COUNT(ss.id) AS song_count,
        SUM(CASE WHEN so.song_type = "Solo" THEN 1 ELSE 0 END) AS solo_count,
        SUM(CASE WHEN so.song_type = "Group" THEN 1 ELSE 0 END) AS group_count,
        GROUP_CONCAT(DISTINCT so.title ORDER BY so.title SEPARATOR ", ") AS song_titles,
        ' . $matching_song_select . '
     FROM students s
     INNER JOIN users u ON u.id = s.user_id
     LEFT JOIN student_songs ss ON ss.student_id = s.id
     LEFT JOIN songs so ON so.id = ss.song_id
     WHERE ' . implode(' AND ', $where) . '
     GROUP BY s.id, s.name, s.name_jp, s.school_year, s.rank, u.avatar
     ORDER BY s.school_year, s.name'
);
$student_stmt->execute(array_merge($student_select_params, $params));
$students = $student_stmt->fetchAll();

$selected_student = null;
$songs = [];
$available_songs = [];

if ($selected_student_id > 0) {
    // Keep the selected student in sync with the active filters.
    // If filters no longer match, the song section will not render stale results.
    $selected_where = array_merge(['s.id = ?'], $where);
    $selected_params = array_merge([$selected_student_id], $params);

    $selected_stmt = $pdo->prepare(
        'SELECT
            s.id,
            s.name,
            s.name_jp,
            s.school_year,
            s.rank
         FROM students s
         WHERE ' . implode(' AND ', $selected_where) . '
         LIMIT 1'
    );
    $selected_stmt->execute($selected_params);
    $selected_student = $selected_stmt->fetch();

    if ($selected_student) {
        // If a song filter is active, only show matching songs for the selected student.
        $selected_song_where = ['ss.student_id = ?'];
        $selected_song_params = [$selected_student['id']];

        if ($song_filter !== '') {
            $selected_song_where[] = '(so.title LIKE ? OR so.title_jp LIKE ? OR so.artist LIKE ?)';
            $selected_song_params[] = $song_like;
            $selected_song_params[] = $song_like;
            $selected_song_params[] = $song_like;
        }

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
                ss.added_at
             FROM student_songs ss
             INNER JOIN songs so ON so.id = ss.song_id
             WHERE ' . implode(' AND ', $selected_song_where) . '
             ORDER BY
                FIELD(so.song_type, "Solo", "Group", "Remix", "Cover"),
                so.release_date IS NULL,
                so.release_date DESC,
                so.title ASC'
        );
        $song_stmt->execute($selected_song_params);
        $songs = $song_stmt->fetchAll();

        // Add-song search should only offer global songs the selected student does not already have.
        // The NOT EXISTS check prevents duplicate student_songs rows from appearing in the picker.
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
                FROM student_songs ss_existing
                WHERE ss_existing.song_id = so.id
                  AND ss_existing.student_id = ?
             )
             ORDER BY
                FIELD(so.song_type, "Solo", "Group", "Remix", "Cover"),
                so.title ASC'
        );
        $available_song_stmt->execute([$selected_student['id']]);
        $available_songs = $available_song_stmt->fetchAll();
    }
}

// Group the selected student's songs so the page matches the student song page layout.
$category_order = ['Solo', 'Group', 'Remix', 'Cover'];
$songs_by_category = array_fill_keys($category_order, []);

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

$selected_total_songs = count($songs);
$latest_release = null;

// Find the latest release date from the currently visible song set.
foreach ($songs as $song) {
    if ($song['release_date'] && (!$latest_release || $song['release_date'] > $latest_release)) {
        $latest_release = $song['release_date'];
    }
}

$page_title = 'Student Songs';
$page_styles = [
    '/gakumas-sms/assets/css/pages/song.css',
    '/gakumas-sms/assets/css/pages/producer-songs.css',
];
$return_query = http_build_query(array_filter([
    'student' => $student_filter,
    'class' => $class_filter,
    'song' => $song_filter,
    'student_id' => $selected_student_id,
    'request_id' => $manual_request_id,
], static fn ($value) => $value !== '' && $value !== 0));
$manual_request_requested = producer_request_context_requested_data($manual_request);
$manual_request_closed = $manual_request && in_array((string) ($manual_request['status'] ?? ''), ['approved', 'rejected', 'cancelled'], true);
$manual_request_back_url = $manual_request ? producer_request_detail_url($manual_request, 'producer') : '';
$manual_request_song_id = (int) ($manual_request['song_id'] ?? ($manual_request_requested['song_id'] ?? 0));
require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

<main class="dashboard-main song-main producer-songs-main">
    <section class="song-hero producer-songs-hero">
        <div>
            <p class="dashboard-eyebrow">Producer Repertoire</p>
            <h2>Student Songs</h2>
            <p>Choose one of your assigned students first, then review their songs in the familiar student song layout.</p>
        </div>

        <div class="song-summary-grid producer-songs-summary">
            <div>
                <span>Assigned Students</span>
                <strong><?= (int) ($stats['assigned_students'] ?? 0) ?></strong>
            </div>

            <div>
                <span>Solo Songs</span>
                <strong><?= (int) ($stats['solo_songs'] ?? 0) ?></strong>
            </div>

            <div>
                <span>Group Songs</span>
                <strong><?= (int) ($stats['group_songs'] ?? 0) ?></strong>
            </div>
        </div>
    </section>

    <section class="producer-song-filters">
        <form method="get" class="producer-song-filter-form" action="/gakumas-sms/producer/songs.php<?= $selected_student_id > 0 ? '#studentSongs' : '' ?>">
            <label>
                <span>Student</span>
                <input type="search" name="student" value="<?= e($student_filter) ?>" placeholder="Search student name">
            </label>

            <label>
                <span>Class</span>
                <input type="search" name="class" value="<?= e($class_filter) ?>" placeholder="Example: 1-1">
            </label>

            <label>
                <span>Song</span>
                <input type="search" name="song" value="<?= e($song_filter) ?>" placeholder="Search song title or artist">
            </label>

            <?php if ($selected_student_id > 0): ?>
                <input type="hidden" name="student_id" value="<?= (int) $selected_student_id ?>">
            <?php endif; ?>

            <div class="producer-song-filter-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search" aria-hidden="true"></i>
                    Filter
                </button>
                <a href="/gakumas-sms/producer/songs.php" class="producer-song-clear-button">
                    <i class="bi bi-x-circle" aria-hidden="true"></i>
                    Reset
                </a>
            </div>
        </form>
    </section>

    <?php if ($producer_song_success): ?>
        <div class="student-page-alert success" role="status">
            <i class="bi bi-check-circle" aria-hidden="true"></i>
            <?= e($producer_song_success) ?>
        </div>
    <?php endif; ?>

    <?php if ($producer_song_error): ?>
        <div class="student-page-alert error" role="alert">
            <i class="bi bi-exclamation-circle" aria-hidden="true"></i>
            <?= e($producer_song_error) ?>
        </div>
    <?php endif; ?>

    <section class="producer-student-picker">
        <div class="producer-student-picker-heading">
            <div>
                <p class="dashboard-eyebrow">Choose Student</p>
                <h3>Assigned students</h3>
            </div>
            <span><?= count($students) ?> result<?= count($students) === 1 ? '' : 's' ?></span>
        </div>

        <?php if (empty($students)): ?>
            <div class="empty-dashboard-state">
                <strong>No matching students</strong>
                <p>Try another student name, class, or song name.</p>
            </div>
        <?php else: ?>
            <div class="producer-student-grid">
            <?php foreach ($students as $student): ?>
                    <?php
                    $student_id = (int) $student['id'];
                    $profile_avatar = trim((string) ($student['avatar'] ?? ''));

                    // Build the student avatar path with the same local/absolute URL rules used elsewhere.
                    if ($profile_avatar !== '') {
                        $avatar_path = str_replace('\\', '/', $profile_avatar);

                        if (!str_starts_with($avatar_path, '/') && !preg_match('/^https?:\/\//i', $avatar_path)) {
                            $avatar_path = '/gakumas-sms/assets/images/avatars/idols/' . rawurlencode($avatar_path);
                        }
                    } else {
                        $avatar_path = '/gakumas-sms/assets/images/avatars/default.webp';
                    }

                    $query_params = array_filter([
                        'student' => $student_filter,
                        'class' => $class_filter,
                        'song' => $song_filter,
                        'student_id' => $student_id,
                        'request_id' => $manual_request_id,
                    ], static fn ($value) => $value !== '');

                    // Preserve active filters and jump down to the song section after choosing a student.
                    $student_url = '/gakumas-sms/producer/songs.php?' . http_build_query($query_params) . '#studentSongs';
                    $is_selected = $selected_student && (int) $selected_student['id'] === $student_id;
                    $matching_song_titles = trim((string) ($student['matching_song_titles'] ?? ''));
                    ?>
                    <a class="producer-student-card<?= $is_selected ? ' is-selected' : '' ?>" href="<?= e($student_url) ?>">
                        <img src="<?= e($avatar_path) ?>" alt="<?= e($student['name']) ?>" class="producer-student-avatar">
                        <span class="producer-student-info">
                            <strong><?= e($student['name']) ?></strong>
                            <?php if (!empty($student['name_jp'])): ?>
                                <small lang="ja"><?= e($student['name_jp']) ?></small>
                            <?php endif; ?>
                            <small><?= e($student['school_year'] ?: 'No class') ?> &middot; Rank <?= e($student['rank'] ?: 'Debut') ?></small>
                            <?php if ($song_filter !== '' && $matching_song_titles !== ''): ?>
                                <small class="producer-student-match">
                                    Matched: <?= e($matching_song_titles) ?>
                                </small>
                            <?php endif; ?>
                        </span>
                        <span class="producer-student-song-count">
                            <strong><?= (int) ($song_filter !== '' ? $student['matching_song_count'] : $student['song_count']) ?></strong>
                            <small><?= $song_filter !== '' ? 'matches' : 'songs' ?></small>
                        </span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <?php if (!$selected_student): ?>
        <section class="empty-dashboard-state producer-song-select-empty">
            <strong>Select a student to view songs</strong>
            <p>The song list will appear here after you choose one assigned student.</p>
        </section>
    <?php else: ?>
        <section class="song-hero producer-selected-student" id="studentSongs">
            <div>
                <p class="dashboard-eyebrow">Selected Student</p>
                <h2><?= e($selected_student['name']) ?>'s Songs</h2>
                <p>
                    <?= e($selected_student['school_year'] ?: 'No class') ?>
                    &middot; Rank <?= e($selected_student['rank'] ?: 'Debut') ?>
                    <?php if ($song_filter !== ''): ?>
                        &middot; Showing matches for "<?= e($song_filter) ?>"
                    <?php endif; ?>
                </p>
            </div>

            <div class="song-summary-grid">
                <div>
                    <span><?= $song_filter !== '' ? 'Matching Songs' : 'Total Songs' ?></span>
                    <strong><?= (int) $selected_total_songs ?></strong>
                </div>

                <div>
                    <span>Latest Release</span>
                    <strong><?= e(format_song_date($latest_release)) ?></strong>
                </div>
            </div>
        </section>

        <?php if ($manual_request): ?>
            <section class="producer-song-manage-panel manual-request-panel">
                <div>
                    <p class="dashboard-eyebrow">Manual Request</p>
                    <h3><?= e(producer_request_type_label($manual_request['request_type'] ?? '')) ?></h3>
                    <p><?= e($manual_request['details'] ?: 'No message was included with this request.') ?></p>
                </div>
                <a href="<?= e($manual_request_back_url) ?>" class="manual-request-back" aria-label="Back to request">
                    <i class="bi bi-arrow-left" aria-hidden="true"></i>
                </a>

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

        <section class="producer-song-manage-panel<?= $manual_request && ($manual_request['request_type'] ?? '') === 'song_add' ? ' request-field-highlight' : '' ?>">
            <div>
                <p class="dashboard-eyebrow">Manage Assignment</p>
                <h3>Add existing song</h3>
                <p>Search the global song library, select one result, then add it to this student.</p>
            </div>

            <?php if (empty($available_songs)): ?>
                <div class="producer-song-manage-empty">
                    <i class="bi bi-music-note-list" aria-hidden="true"></i>
                    <span>Every available song is already assigned to this student.</span>
                </div>
            <?php else: ?>
                <form method="post" class="producer-song-add-form" id="producerSongAddForm">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="song_action" value="add">
                    <input type="hidden" name="student_id" value="<?= (int) $selected_student['id'] ?>">
                    <input type="hidden" name="song_id" id="songAddSelectedId">
                    <input type="hidden" name="return_query" value="<?= e($return_query) ?>">
                    <?php if ($manual_request): ?>
                        <input type="hidden" name="request_id" value="<?= (int) $manual_request['id'] ?>">
                    <?php endif; ?>

                    <div class="producer-song-search-picker">
                        <label for="songAddSearch">
                            <span>Search song</span>
                            <input type="search" id="songAddSearch" autocomplete="off" placeholder="Type song title, Japanese title, or artist">
                        </label>

                        <!-- Search results contain only songs that are not already assigned to this student. -->
                        <div class="producer-song-picker-list d-none" id="songAddResults" role="listbox" aria-label="Available songs">
                            <?php foreach ($available_songs as $available_song): ?>
                                <?php
                                $available_search_text = strtolower(
                                    implode(' ', [
                                        $available_song['title'] ?? '',
                                        $available_song['title_jp'] ?? '',
                                        $available_song['artist'] ?? '',
                                        $available_song['song_type'] ?? '',
                                    ])
                                );
                                ?>
                                <button
                                    type="button"
                                    class="producer-song-picker-option"
                                    data-song-option
                                    data-song-id="<?= (int) $available_song['id'] ?>"
                                    data-song-label="<?= e($available_song['title']) ?>"
                                    data-song-search="<?= e($available_search_text) ?>"
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

                        <p class="producer-song-picker-empty d-none" id="songAddEmpty">No available songs match your search.</p>
                    </div>

                    <button type="submit" class="btn btn-primary" id="songAddSubmit" disabled>
                        <i class="bi bi-plus-lg" aria-hidden="true"></i>
                        Add Song
                    </button>
                </form>
            <?php endif; ?>
        </section>

        <?php if (empty($songs)): ?>
            <section class="empty-dashboard-state song-empty-state">
                <strong><?= $song_filter !== '' ? 'No matching songs' : 'No songs assigned yet' ?></strong>
                <p><?= $song_filter !== '' ? 'Try another song title or artist, or reset the filters.' : 'This student\'s songs will appear here after they are added to the profile.' ?></p>
            </section>
        <?php else: ?>
            <section class="song-toolbar">
                <label class="song-search" for="songSearch">
                    <i class="bi bi-search"></i>
                    <input type="search" id="songSearch" placeholder="Search title or artist">
                </label>

                <div class="song-category-pills" aria-label="Song category counts">
                    <?php foreach ($category_order as $category): ?>
                        <?php if (!empty($songs_by_category[$category])): ?>
                            <span>
                                <?= e($category) ?>
                                <strong><?= count($songs_by_category[$category]) ?></strong>
                            </span>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </section>

            <div class="song-library" id="songLibrary">
                <?php foreach ($category_order as $category): ?>
                    <?php if (empty($songs_by_category[$category])): ?>
                        <?php continue; ?>
                    <?php endif; ?>

                    <section class="song-category-section" data-song-category="<?= e($category) ?>">
                        <div class="song-category-heading">
                            <div>
                                <i class="bi <?= e($category_icons[$category] ?? 'bi-music-note-list') ?>"></i>
                                <h2><?= e($category) ?></h2>
                            </div>
                            <span><?= count($songs_by_category[$category]) ?> tracks</span>
                        </div>

                        <div class="song-list" role="list">
                            <div class="song-list-header" aria-hidden="true">
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
                                <span>
                                    Artist
                                </span>
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
                            </div>

                            <?php foreach ($songs_by_category[$category] as $index => $song): ?>
                                <?php
                                // Each song needs a unique collapse target for its detail panel.
                                $collapse_id = 'producerSongDetails' . (int) $song['id'];
                                $search_text = strtolower(
                                    implode(' ', [
                                        $song['title'] ?? '',
                                        $song['title_jp'] ?? '',
                                        $song['artist'] ?? '',
                                        $song['song_type'] ?? '',
                                        $song['notes'] ?? '',
                                    ])
                                );
                                ?>

                                <article class="song-track<?= $manual_request_song_id === (int) $song['id'] ? ' request-field-highlight' : '' ?>"
                                    data-song-search="<?= e($search_text) ?>"
                                    data-sort-number="<?= (int) ($index + 1) ?>"
                                    data-sort-title="<?= e($song['title']) ?>"
                                    data-sort-release="<?= e($song['release_date'] ?: '') ?>"
                                    data-sort-duration="<?= e($song['duration'] ?: '') ?>"
                                    role="listitem">
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
                                        <div class="song-track-details">
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
                                            </dl>

                                            <div class="song-notes">
                                                <span>Notes</span>
                                                <p><?= e($song['notes'] ?: 'No notes available for this song.') ?></p>
                                            </div>

                                            <form method="post" class="producer-song-remove-form">
                                                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                                <input type="hidden" name="song_action" value="remove">
                                                <input type="hidden" name="student_id" value="<?= (int) $selected_student['id'] ?>">
                                                <input type="hidden" name="song_id" value="<?= (int) $song['id'] ?>">
                                                <input type="hidden" name="return_query" value="<?= e($return_query) ?>">
                                                <?php if ($manual_request): ?>
                                                    <input type="hidden" name="request_id" value="<?= (int) $manual_request['id'] ?>">
                                                <?php endif; ?>

                                                <button type="submit" class="producer-song-remove-button" data-confirm-remove="<?= e($song['title']) ?>">
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
                <?php endforeach; ?>
            </div>

            <section class="empty-dashboard-state song-empty-state d-none" id="songSearchEmpty">
                <strong>No matching songs</strong>
                <p>Try another title, artist, or category name.</p>
            </section>
        <?php endif; ?>
    <?php endif; ?>
</main>

<script src="/gakumas-sms/assets/js/producer-songs.js"></script>

<?php require_once '../includes/footer.php'; ?>
