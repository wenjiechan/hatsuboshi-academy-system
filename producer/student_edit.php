<?php
require_once '../includes/auth.php';
require_role('producer');

require_once '../config/database.php';
require_once '../includes/theme_settings_helpers.php';
require_once '../includes/student_edit_validation.php';

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

// Load the assigned student for this producer.
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

if (!$student) {
    redirect_to_account_issue(
        'Student profile not found',
        'This student was not found, or this student is not assigned to your producer account.',
        404
    );
}

$producer_status = $student['producer_status'] ?? 'unassigned';
$producer_status_label = ucwords(str_replace('_', ' ', $producer_status));

$producer_display = in_array($producer_status, ['active', 'removal_pending'], true)
    ? ($student['producer_name'] ?: 'Producer not found')
    : $producer_status_label;

$error = '';
$success = '';

// Save producer-managed profile fields.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf($_POST['csrf_token'] ?? '');

    $validation = validate_student_edit_profile($_POST, $student);
    $error = $validation['error'];
    $student_values = $validation['data'];

    if ($error === '') {
        $update_stmt = $pdo->prepare(
            'UPDATE students
             SET name = ?,
                 name_jp = ?,
                 birthday = ?,
                 zodiac = ?,
                 blood_type = ?,
                 height = ?,
                 weight = ?,
                 three_size = ?,
                 school_year = ?,
                 rank = ?,
                 vocal = ?,
                 dance = ?,
                 visual = ?
             WHERE id = ?
             AND producer_id = ?'
        );

        $update_stmt->execute([
            $student_values['name'],
            $student_values['name_jp'] !== '' ? $student_values['name_jp'] : null,
            $student_values['birthday'] !== '' ? $student_values['birthday'] : null,
            $student_values['zodiac'] !== '' ? $student_values['zodiac'] : null,
            $student_values['blood_type'] !== '' ? $student_values['blood_type'] : null,
            $student_values['height'] !== '' ? (int) $student_values['height'] : null,
            $student_values['weight'] !== '' ? (int) $student_values['weight'] : null,
            $student_values['three_size'] !== '' ? $student_values['three_size'] : null,
            $student_values['school_year'] !== '' ? $student_values['school_year'] : null,
            $student_values['rank'] !== '' ? $student_values['rank'] : 'Debut',
            $student_values['vocal'],
            $student_values['dance'],
            $student_values['visual'],
            $student_id,
            $_SESSION['id'],
        ]);

        $stmt->execute([$student_id, $_SESSION['id']]);
        $student = $stmt->fetch();
        $success = 'Student profile updated successfully.';
    }
}

// Prepare view data.
$profile_avatar = trim((string) ($student['avatar'] ?? ''));

if ($profile_avatar !== '') {
    $profile_avatar_path = str_replace('\\', '/', $profile_avatar);

    if (!str_starts_with($profile_avatar_path, '/') && !preg_match('/^https?:\/\//i', $profile_avatar_path)) {
        $profile_avatar_path = '/gakumas-sms/assets/images/avatars/idols/' . rawurlencode($profile_avatar_path);
    }
} else {
    $profile_avatar_path = '/gakumas-sms/assets/images/avatars/idols/' . rawurlencode($student['name']) . '.png';
}

$page_title = 'Student Details';
$page_styles = ['/gakumas-sms/assets/css/pages/profile.css'];
require_once '../includes/header.php';
require_once '../includes/sidebar.php';

$student_theme_primary = $student['theme_primary_color'] ?: DEFAULT_THEME_PRIMARY;
$student_theme_secondary = $student['theme_secondary_color'] ?: DEFAULT_THEME_SECONDARY;
$birthday_timestamp = !empty($student['birthday']) ? strtotime($student['birthday']) : false;
// Display birthday as month and day only
$birthday_display = $birthday_timestamp ? date('F d', $birthday_timestamp) : '';
$birthday_month_value = $birthday_timestamp ? (int) date('n', $birthday_timestamp) : 1;
$birthday_day_value = $birthday_timestamp ? (int) date('j', $birthday_timestamp) : 1;
$birthday_months = student_edit_month_options();
$blood_type_options = student_edit_blood_type_options();
$rank_options = student_edit_rank_options();
$three_size_parts = student_edit_split_three_size($student['three_size'] ?? '');
$school_year_code = student_edit_class_code($student['school_year'] ?? '');
?>

<main class="dashboard-main profile-main"
    style="--primary: <?= e($student_theme_primary) ?>; --secondary: <?= e($student_theme_secondary) ?>;">
    <form action="/gakumas-sms/producer/student_edit.php?id=<?= $student_id ?>" method="post" class="profile-form">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

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

            <button type="button" class="profile-edit-button" id="profileEditButton" aria-pressed="false">
                <i class="bi bi-pen" aria-hidden="true"></i>
                Edit Profile
            </button>
        </section>

        <?php if ($success !== ''): ?>
            <div class="alert alert-success" role="alert">
                <?= e($success) ?>
            </div>
        <?php endif; ?>

        <?php if ($error !== ''): ?>
            <div class="alert alert-danger" role="alert">
                <?= e($error) ?>
            </div>
        <?php endif; ?>

        <section class="card profile-card">
            <div class="profile-card-heading">
                <i class="bi bi-person-badge" aria-hidden="true"></i>
                <h2>Identity</h2>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" id="name" name="name" class="form-control profile-editable"
                        value="<?= e($student['name']) ?>" maxlength="100" required readonly>
                </div>

                <div class="col-md-6">
                    <label for="name_jp" class="form-label">Japanese Name</label>
                    <input type="text" id="name_jp" name="name_jp" class="form-control profile-editable"
                        value="<?= e($student['name_jp']) ?>" maxlength="100" readonly>
                </div>

                <div class="col-md-4">
                    <label for="birthday" class="form-label">Birthday</label>
                    <div class="profile-birthday-picker" data-selected-month="<?= $birthday_month_value ?>"
                        data-selected-day="<?= $birthday_day_value ?>">
                        <input type="hidden" id="birthday" name="birthday" value="<?= e($birthday_display) ?>">
                        <button type="button" class="form-control profile-birthday-button" id="birthdayPickerButton"
                            disabled>
                            <span id="birthdayPickerLabel"><?= e($birthday_display ?: 'Choose birthday') ?></span>
                            <i class="bi bi-calendar3" aria-hidden="true"></i>
                        </button>

                        <div class="profile-birthday-popover d-none" id="birthdayPickerPopover">
                            <div class="profile-birthday-controls">
                                <button type="button" class="profile-calendar-nav" id="birthdayPrevMonth"
                                    aria-label="Previous month">
                                    <i class="bi bi-chevron-left" aria-hidden="true"></i>
                                </button>

                                <select class="form-select profile-birthday-month" id="birthdayMonthSelect"
                                    aria-label="Birthday month">
                                    <?php foreach ($birthday_months as $month_number => $month_name): ?>
                                        <option value="<?= $month_number ?>" <?= $month_number === $birthday_month_value ? 'selected' : '' ?>>
                                            <?= e($month_name) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>

                                <button type="button" class="profile-calendar-nav" id="birthdayNextMonth"
                                    aria-label="Next month">
                                    <i class="bi bi-chevron-right" aria-hidden="true"></i>
                                </button>
                            </div>

                            <div class="profile-calendar-weekdays" aria-hidden="true">
                                <span>Sun</span>
                                <span>Mon</span>
                                <span>Tue</span>
                                <span>Wed</span>
                                <span>Thu</span>
                                <span>Fri</span>
                                <span>Sat</span>
                            </div>

                            <div class="profile-calendar-days" id="birthdayDayGrid"></div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <label for="zodiac" class="form-label">Zodiac</label>
                    <input type="text" id="zodiac" name="zodiac" class="form-control"
                        value="<?= e($student['zodiac']) ?>" readonly>
                </div>

                <div class="col-md-4">
                    <label for="blood_type" class="form-label">Blood Type</label>
                    <select id="blood_type" name="blood_type" class="form-select profile-editable" disabled>
                        <option value="">Not set</option>
                        <?php foreach ($blood_type_options as $blood_type_option): ?>
                            <option value="<?= e($blood_type_option) ?>" <?= $student['blood_type'] === $blood_type_option ? 'selected' : '' ?>>
                                <?= e($blood_type_option) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
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
                    <input type="text" id="height" name="height" class="form-control profile-editable"
                        value="<?= e((string) $student['height']) ?> cm"
                        data-display-value="<?= e((string) $student['height']) ?> cm"
                        data-edit-value="<?= e((string) $student['height']) ?>" data-edit-type="number" min="100"
                        max="220" readonly>
                </div>

                <div class="col-md-4">
                    <label for="weight" class="form-label">Weight</label>
                    <input type="text" id="weight" name="weight" class="form-control profile-editable"
                        value="<?= e((string) $student['weight']) ?> kg"
                        data-display-value="<?= e((string) $student['weight']) ?> kg"
                        data-edit-value="<?= e((string) $student['weight']) ?>" data-edit-type="number" min="25"
                        max="150" readonly>
                </div>

                <div class="col-md-4">
                    <label for="three_size" class="form-label">Three Size</label>
                    <input type="hidden" id="three_size" name="three_size" value="<?= e($student['three_size']) ?>">
                    <div class="profile-three-size">
                        <input type="number" id="three_size_bust" name="three_size_bust"
                            class="form-control profile-editable profile-three-size-input"
                            value="<?= e($three_size_parts[0] ?? '') ?>" min="40" max="150" readonly>
                        <span class="profile-three-size-separator">/</span>
                        <input type="number" id="three_size_waist" name="three_size_waist"
                            class="form-control profile-editable profile-three-size-input"
                            value="<?= e($three_size_parts[1] ?? '') ?>" min="40" max="150" readonly>
                        <span class="profile-three-size-separator">/</span>
                        <input type="number" id="three_size_hip" name="three_size_hip"
                            class="form-control profile-editable profile-three-size-input"
                            value="<?= e($three_size_parts[2] ?? '') ?>" min="40" max="150" readonly>
                    </div>
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
                    <div class="input-group profile-fixed-prefix">
                        <span class="input-group-text">Class</span>
                        <input type="text" id="school_year" name="school_year" class="form-control profile-editable"
                            value="<?= e($school_year_code) ?>" maxlength="10" pattern="\d+-\d+" readonly>
                    </div>
                </div>

                <div class="col-md-4">
                    <label for="rank" class="form-label">Rank</label>
                    <select id="rank" name="rank" class="form-select profile-editable" disabled>
                        <?php foreach ($rank_options as $rank_option): ?>
                            <option value="<?= e($rank_option) ?>" <?= $student['rank'] === $rank_option ? 'selected' : '' ?>>
                                <?= e($rank_option) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
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
                    <label for="vocal">Vocal</label>
                    <input type="number" id="vocal" name="vocal" class="form-control profile-editable"
                        value="<?= (int) $student['vocal'] ?>" min="0" readonly>
                </div>

                <div class="profile-stat dance">
                    <label for="dance">Dance</label>
                    <input type="number" id="dance" name="dance" class="form-control profile-editable"
                        value="<?= (int) $student['dance'] ?>" min="0" readonly>
                </div>

                <div class="profile-stat visual">
                    <label for="visual">Visual</label>
                    <input type="number" id="visual" name="visual" class="form-control profile-editable"
                        value="<?= (int) $student['visual'] ?>" min="0" readonly>
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

        <div class="profile-form-actions d-none" id="profileFormActions">
            <button type="button" class="btn btn-outline-secondary" id="profileCancelButton">
                <i class="bi bi-x-lg" aria-hidden="true"></i>
                Cancel
            </button>

            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save" aria-hidden="true"></i>
                Save Changes
            </button>
        </div>
    </form>
</main>

<script src="/gakumas-sms/assets/js/student-edit-profile.js"></script>

<?php require_once '../includes/footer.php'; ?>
