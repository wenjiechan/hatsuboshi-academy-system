<?php
require_once '../includes/auth.php';
require_role('producer');

require_once '../config/database.php';
require_once '../includes/theme_settings_helpers.php';

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

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

function format_song_date(?string $date): string
{
    if (!$date) {
        return 'Not dated';
    }

    return date('M j, Y', strtotime($date));
}

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

$student_filter = trim((string) ($_GET['student'] ?? ''));
$class_filter = trim((string) ($_GET['class'] ?? ''));
$song_filter = trim((string) ($_GET['song'] ?? ''));
$selected_student_id = isset($_GET['student_id']) ? max(0, (int) $_GET['student_id']) : 0;
$song_like = '%' . $song_filter . '%';

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

if ($selected_student_id > 0) {
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
    }
}

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
                    ], static fn ($value) => $value !== '');
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
                                <span>#</span>
                                <span>Title</span>
                                <span>Artist</span>
                                <span>Release</span>
                                <span>Time</span>
                            </div>

                            <?php foreach ($songs_by_category[$category] as $index => $song): ?>
                                <?php
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

                                <article class="song-track"
                                    data-song-search="<?= e($search_text) ?>"
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

<script>
const songSearch = document.getElementById('songSearch');
const songTracks = Array.from(document.querySelectorAll('.song-track'));
const songSections = Array.from(document.querySelectorAll('.song-category-section'));
const songSearchEmpty = document.getElementById('songSearchEmpty');

if (songSearch) {
    songSearch.addEventListener('input', () => {
        const query = songSearch.value.trim().toLowerCase();
        let visibleTrackCount = 0;

        songTracks.forEach((track) => {
            const isVisible = !query || track.dataset.songSearch.includes(query);
            track.classList.toggle('d-none', !isVisible);

            if (isVisible) {
                visibleTrackCount += 1;
            }
        });

        songSections.forEach((section) => {
            const hasVisibleTrack = section.querySelector('.song-track:not(.d-none)');
            section.classList.toggle('d-none', !hasVisibleTrack);
        });

        if (songSearchEmpty) {
            songSearchEmpty.classList.toggle('d-none', visibleTrackCount > 0);
        }
    });
}
</script>

<?php require_once '../includes/footer.php'; ?>
