<?php
require_once '../includes/auth.php';
require_role('producer');

require_once '../config/database.php';
require_once '../includes/theme_settings_helpers.php';

//Helper for safe output
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

//Format date as Month day, for example June 03
function format_profile_birthday(?string $birthday): string
{
    if (!$birthday) {
        return '';
    }

    return date('F d', strtotime($birthday));
}

// Get current student profile
$stmt = $pdo->prepare(
    'SELECT
    s.*,
    u.username,
    u.avatar,
    u.theme_primary_color,
    u.theme_secondary_color,
    p.username AS producer_name
    FROM students s
    INNER JOIN users u ON u.id = s.user_id
    LEFT JOIN users p ON p.id = s.producer_id
    WHERE s.id = ?
    AND s.producer_id = ?
    LIMIT 1'
);

$student_id = (int) ($_GET['id'] ?? 0);

$stmt->execute([
    $student_id,
    $_SESSION['id']
]);
$student = $stmt->fetch();

//Redirect to the error page
if (!$student) {
    redirect_to_account_issue(
        'Student profile not found',
        'This student was not found, or this student is not assigned to your producer account.',
        404
    );
}

//Producer diaplay logic
$producer_status = $student['producer_status'] ?? 'unassigned';
$producer_status_label = ucwords(str_replace('_', ' ', $producer_status));
// If producer status is active or removal pending, show the producer username
$producer_display = in_array($producer_status, ['active', 'removal_pending'], true)
    ? ($student['producer_name'] ?: 'Producer not found')
    : $producer_status_label;

//Avatar diaplay logic
$profile_avatar = trim((string) ($student['avatar'] ?? ''));

if ($profile_avatar !== '') {
    $profile_avatar_path = str_replace('\\', '/', $profile_avatar);

    // Default avatar file path
    if (!str_starts_with($profile_avatar_path, '/') && !preg_match('/^https?:\/\//i', $profile_avatar_path)) {
        $profile_avatar_path = '/gakumas-sms/assets/images/avatars/idols/' . rawurlencode($profile_avatar_path);
    }
} else {
    // Default avatar
    $profile_avatar_path = '/gakumas-sms/assets/images/avatars/idols/' . rawurlencode($student['name']) . '.png';
}

$page_title = 'Student Details';
$page_styles = ['/gakumas-sms/assets/css/pages/profile.css'];
require_once '../includes/header.php';
require_once '../includes/sidebar.php';

$student_theme_primary = $student['theme_primary_color'] ?: DEFAULT_THEME_PRIMARY;
$student_theme_secondary = $student['theme_secondary_color'] ?: DEFAULT_THEME_SECONDARY;
?>

<main class="dashboard-main profile-main" style="--primary: <?= e($student_theme_primary) ?>; --secondary: <?= e($student_theme_secondary) ?>;">
    <div class="profile-form">

        <section class="profile-hero card">
            <div class="profile-avatar-wrap">
                <img src="<?= e($profile_avatar_path) ?>" alt="<?= e($student['name']) ?>" class="profile-avatar"
                    id="profileAvatarPreview">

            </div>

            <div class="profile-hero-copy">
                <p class="dashboard-eyebrow">Student Profile</p>
                <h2><?= e($student['name']) ?></h2>
                <?php if (!empty($student['name_jp'])): ?>
                <p lang="ja"><?= e($student['name_jp']) ?></p>
                <?php endif; ?>
            </div>

        </section>

        <section class="card profile-card">
            <div class="profile-card-heading">
                <i class="bi bi-person-badge" aria-hidden="true"></i>
                <h2>Identity</h2>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" id="name" name="name" class="form-control" value="<?= e($student['name']) ?>"
                        readonly>
                </div>

                <div class="col-md-6">
                    <label for="name_jp" class="form-label">Japanese Name</label>
                    <input type="text" id="name_jp" name="name_jp" class="form-control"
                        value="<?= e($student['name_jp']) ?>" readonly>
                </div>

                <div class="col-md-4">
                    <label for="birthday" class="form-label">Birthday</label>
                    <input type="text" id="birthday" name="birthday" class="form-control"
                        value="<?= e(format_profile_birthday($student['birthday'])) ?>" readonly>
                </div>

                <div class="col-md-4">
                    <label for="zodiac" class="form-label">Zodiac</label>
                    <input type="text" id="zodiac" name="zodiac" class="form-control"
                        value="<?= e($student['zodiac']) ?>" readonly>
                </div>

                <div class="col-md-4">
                    <label for="hometown" class="form-label">Hometown</label>
                    <input type="text" id="hometown" name="hometown" class="form-control profile"
                        value="<?= e($student['hometown']) ?>" readonly>
                </div>
            </div>
        </section>

        <section class="card profile-card">
            <div class="profile-card-heading">
                <i class="bi bi-rulers" aria-hidden="true"></i>
                <h2>Physical Details</h2>
            </div>

            <div class="row g-3">
                <div class="col-md-4">
                    <label for="height" class="form-label">Height</label>
                    <input type="text" id="height" name="height" class="form-control"
                        value="<?= e((string) $student['height']) ?> cm" readonly>
                </div>

                <div class="col-md-4">
                    <label for="weight" class="form-label">Weight</label>
                    <input type="text" id="weight" name="weight" class="form-control"
                        value="<?= e((string) $student['weight']) ?> kg" readonly>
                </div>

                <div class="col-md-4">
                    <label for="three_size" class="form-label">Three Size</label>
                    <input type="text" id="three_size" name="three_size" class="form-control"
                        value="<?= e($student['three_size']) ?>" readonly>
                </div>
            </div>
        </section>

        <section class="card profile-card">
            <div class="profile-card-heading">
                <i class="bi bi-mortarboard" aria-hidden="true"></i>
                <h2>Academy Status</h2>
            </div>

            <div class="row g-3">
                <div class="col-md-4">
                    <label for="school_year" class="form-label">Class</label>
                    <input type="text" id="school_year" name="school_year" class="form-control"
                        value="<?= e($student['school_year']) ?>" readonly>
                </div>

                <div class="col-md-4">
                    <label for="rank" class="form-label">Rank</label>
                    <input type="text" id="rank" name="rank" class="form-control" value="<?= e($student['rank']) ?>"
                        readonly>
                </div>

                <div class="col-md-4">
                    <label for="producer" class="form-label">Producer</label>
                    <input type="text" id="producer" name="producer" class="form-control"
                        value="<?= e($producer_display) ?>" readonly>
                </div>
            </div>
        </section>

        <section class="card profile-card">
            <div class="profile-card-heading">
                <i class="bi bi-bar-chart-line" aria-hidden="true"></i>
                <h2>Performance Stats</h2>
            </div>

            <div class="profile-stat-grid">
                <div class="profile-stat vocal">
                    <span>Vocal</span>
                    <strong><?= (int) $student['vocal'] ?></strong>
                </div>

                <div class="profile-stat dance">
                    <span>Dance</span>
                    <strong><?= (int) $student['dance'] ?></strong>
                </div>

                <div class="profile-stat visual">
                    <span>Visual</span>
                    <strong><?= (int) $student['visual'] ?></strong>
                </div>
            </div>
        </section>

        <section class="card profile-card">
            <div class="profile-card-heading">
                <i class="bi bi-journal-text" aria-hidden="true"></i>
                <h2>Personal Notes</h2>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <label for="special_skill" class="form-label">Special Skill</label>
                    <textarea class="form-control profile" name="special_skill" id="special_skill" rows="4"
                        readonly><?= e($student['special_skill']) ?></textarea>
                </div>

                <div class="col-md-6">
                    <label for="hobbies" class="form-label">Hobbies</label>
                    <textarea class="form-control profile" name="hobbies" id="hobbies" rows="4"
                        readonly><?= e($student['hobbies']) ?></textarea>
                </div>

                <div class="col-12">
                    <label for="bio" class="form-label">Bio</label>
                    <textarea class="form-control profile" name="bio" id="bio" rows="5"
                        readonly><?= e($student['bio']) ?></textarea>
                </div>
            </div>
        </section>

        
        </div>
</main>


<?php require_once '../includes/footer.php'; ?>
