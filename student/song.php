<?php
require_once '../includes/auth.php';
require_role('student');

require_once '../config/database.php';

//Helper for safe output
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

// Format songs duration (from HH:MM:SS to MM:SS)
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

// Format release date (from YY-MM-DD to Month Date, Year)
function format_song_date(?string $date): string
{
    if (!$date) {
        return 'Not dated';
    }

    return date('M j, Y', strtotime($date));
}

// Get current student profile
$stmt = $pdo->prepare(
    'SELECT
        id,
        name,
        theme_primary_color,
        theme_secondary_color
     FROM students
     WHERE user_id = ?
     LIMIT 1'
);

$stmt->execute([$_SESSION['id']]);
$student = $stmt->fetch();

//Redirect to the error page
if (!$student) {
    redirect_to_account_issue(
        'Student profile not found',
        'Your login is active, but no student profile is linked to this account yet. Please log out and ask an administrator to check your student account setup.',
        404
    );
}

$_SESSION['student_name'] = $student['name'];
$_SESSION['theme_primary_color'] = $student['theme_primary_color'] ?: '#FF6B9D';
$_SESSION['theme_secondary_color'] = $student['theme_secondary_color'] ?: '#FFB3D1';

//Get songs assigned to the student
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
     WHERE ss.student_id = ?
     ORDER BY
        FIELD(so.song_type, "Solo", "Group", "Remix", "Cover"),
        so.release_date IS NULL,
        so.release_date DESC,
        so.title ASC'
);

// Group songs by category
$song_stmt->execute([$student['id']]);
$songs = $song_stmt->fetchAll();

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

$total_songs = count($songs);
$latest_release = null;

//Find the latest release date
foreach ($songs as $song) {
    if ($song['release_date'] && (!$latest_release || $song['release_date'] > $latest_release)) {
        $latest_release = $song['release_date'];
    }
}

$page_title = 'My Songs';
$page_styles = ['/gakumas-sms/assets/css/pages/song.css'];
require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

<main class="dashboard-main song-main">
    <section class="song-hero">
        <div>
            <p class="dashboard-eyebrow">Student Repertoire</p>
            <h2><?= e($student['name']) ?>'s Songs</h2>
            <p>
                Browse every song assigned to your idol profile. Open a track to see its full details.
            </p>
        </div>

        <div class="song-summary-grid">
            <div>
                <span>Total Songs</span>
                <strong><?= (int) $total_songs ?></strong>
            </div>

            <div>
                <span>Latest Release</span>
                <strong><?= e(format_song_date($latest_release)) ?></strong>
            </div>
        </div>
    </section>

    <section class="song-toolbar">
        <label class="song-search" for="songSearch">
            <i class="bi bi-search"></i>
            <input type="search" id="songSearch" placeholder="Search title or artist">
        </label>

        <!-- Category counts-->
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

    <!-- Empty state if no songs-->
    <?php if (empty($songs)): ?>
        <section class="empty-dashboard-state song-empty-state">
            <strong>No songs assigned yet</strong>
            <p>Your assigned songs will appear here after a producer adds them to your profile.</p>
        </section>
    <?php else: ?>
        <!-- Song library if songs exist-->
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

                        <!-- Loops through songs in a category-->
                        <?php foreach ($songs_by_category[$category] as $index => $song): ?>
                            <?php
                            //Create unique id for collapse
                            $collapse_id = 'songDetails' . (int) $song['id'];
                            //Create searchable text string for each song
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

                            <article class="song-track" data-song-search="<?= e($search_text) ?>" role="listitem">
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
                                        <!--Japanese title only appears of ot exists and different from the main title-->
                                        <?php if (!empty($song['title_jp']) && $song['title_jp'] !== $song['title']): ?>
                                            <small lang="ja" title="<?= e($song['title_jp']) ?>"><?= e($song['title_jp']) ?></small>
                                        <?php endif; ?>
                                    </span>

                                    <span class="song-track-artist"><?= e($song['artist'] ?: 'Unknown artist') ?></span>
                                    <span class="song-track-release"><?= e(format_song_date($song['release_date'])) ?></span>
                                    <span class="song-track-duration"><?= e(format_song_duration($song['duration'])) ?></span>
                                    <i class="bi bi-chevron-down song-track-chevron"></i>
                                </button>

                                <!-- Song details panel-->
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
</main>

<script>
// Gets search input, all song rows, all category, empty search message
const songSearch = document.getElementById('songSearch');
const songTracks = Array.from(document.querySelectorAll('.song-track'));
const songSections = Array.from(document.querySelectorAll('.song-category-section'));
const songSearchEmpty = document.getElementById('songSearchEmpty');

if (songSearch) {
    // Runs when user types in the search box
    songSearch.addEventListener('input', () => {
        const query = songSearch.value.trim().toLowerCase();
        let visibleTrackCount = 0;

        // If search is empty, show all songs
        // If search has text, check the song's data-song-search contains the query
        // If yes, show the song
        // If no, hide it
        songTracks.forEach((track) => {
            const isVisible = !query || track.dataset.songSearch.includes(query);
            track.classList.toggle('d-none', !isVisible);

            if (isVisible) {
                visibleTrackCount += 1;
            }
        });

        // If all songs in a category are hidden, whole category section is hidden too
        songSections.forEach((section) => {
            const hasVisibleTrack = section.querySelector('.song-track:not(.d-none)');
            section.classList.toggle('d-none', !hasVisibleTrack);
        });

        // When no songs match the search
        if (songSearchEmpty) {
            songSearchEmpty.classList.toggle('d-none', visibleTrackCount > 0);
        }
    });
}
</script>

<?php require_once '../includes/footer.php'; ?>
