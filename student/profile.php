<?php
require_once '../includes/auth.php';
require_role('student');

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

$success = '';
$error = '';

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
    WHERE s.user_id = ?
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

// When clicks save change
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf($_POST['csrf_token'] ?? '');

    //Collect editable values
    $hometown = trim($_POST['hometown'] ?? '');
    $special_skill = trim($_POST['special_skill'] ?? '');
    $hobbies = trim($_POST['hobbies'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $avatar_path_to_save = null;

    // If choose an avatar
    if (!empty($_FILES['avatar']['name'])) {
        if (($_FILES['avatar']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            $error = 'Avatar upload failed. Please choose another image.';
        } else {
            // Check the MIME typr
            $allowed_avatar_types = [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp',
            ];
            $avatar_mime = mime_content_type($_FILES['avatar']['tmp_name']);

            // The file size is limit in 2 MB
            if (!isset($allowed_avatar_types[$avatar_mime])) {
                $error = 'Avatar must be a JPG, PNG, or WEBP image.';
            } elseif (($_FILES['avatar']['size'] ?? 0) > 2 * 1024 * 1024) {
                $error = 'Avatar must be smaller than 2 MB.';
            } else {
                // The avatar is saves into the uploads/profiles
                $avatar_directory = dirname(__DIR__) . '/uploads/profiles';

                if (!is_dir($avatar_directory)) {
                    mkdir($avatar_directory, 0775, true);
                }

                $avatar_extension = $allowed_avatar_types[$avatar_mime];
                // Generate a random filename
                $avatar_filename = 'student_' . (int) $_SESSION['id'] . '_' . bin2hex(random_bytes(8)) . '.' . $avatar_extension;
                $avatar_target = $avatar_directory . '/' . $avatar_filename;

                if (move_uploaded_file($_FILES['avatar']['tmp_name'], $avatar_target)) {
                    $avatar_path_to_save = '/gakumas-sms/uploads/profiles/' . $avatar_filename;
                } else {
                    $error = 'Avatar could not be saved. Please try again.';
                }
            }
        }
    }

    // Updating the profile
    if ($error === '') {
        $update_stmt = $pdo->prepare(
            'UPDATE students
             SET hometown = ?, special_skill = ?, hobbies = ?, bio = ?
             WHERE user_id = ?'
        );

        $update_stmt->execute([
            $hometown,
            $special_skill,
            $hobbies,
            $bio,
            $_SESSION['id'],
        ]);

        if ($avatar_path_to_save !== null) {
            $avatar_stmt = $pdo->prepare(
                'UPDATE users
                 SET avatar = ?
                 WHERE id = ?'
            );
            $avatar_stmt->execute([$avatar_path_to_save, $_SESSION['id']]);
            $_SESSION['avatar'] = $avatar_path_to_save;
        }

        $stmt->execute([$_SESSION['id']]);
        $student = $stmt->fetch();
        $success = 'Profile updated successfully.';
    }
}

$_SESSION['student_name'] = $student['name'];
$_SESSION['avatar'] = $student['avatar'] ?? '';
$_SESSION['theme_primary_color'] = $student['theme_primary_color'] ?: DEFAULT_THEME_PRIMARY;
$_SESSION['theme_secondary_color'] = $student['theme_secondary_color'] ?: DEFAULT_THEME_SECONDARY;

//Producer diaplay logic
$producer_status = $student['producer_status'] ?? 'unassigned';
$producer_status_label = ucwords(str_replace('_', ' ', $producer_status));
// If producer status is active or removal pending, show the producer username
$producer_display = in_array($producer_status, ['active', 'removal_pending'], true)
    ? ($student['producer_name'] ?: 'Producer not found')
    : $producer_status_label;

//Avatar diaplay logic
$profile_avatar = trim((string) ($student['avatar'] ?? ''));
$default_student_avatar_path = '/gakumas-sms/assets/images/avatars/default.webp';

if ($profile_avatar !== '') {
    $profile_avatar_path = str_replace('\\', '/', $profile_avatar);

    // Default avatar file path
    if (!str_starts_with($profile_avatar_path, '/') && !preg_match('/^https?:\/\//i', $profile_avatar_path)) {
        $profile_avatar_path = '/gakumas-sms/assets/images/avatars/idols/' . rawurlencode($profile_avatar_path);
    }
} else {
    // Default avatar
    $profile_avatar_path = $default_student_avatar_path;
}

$page_title = 'My Profile';
$page_styles = ['/gakumas-sms/assets/css/pages/profile.css'];
require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

<main class="dashboard-main profile-main">
    <form action="/gakumas-sms/student/profile.php" method="post" class="profile-form" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

    <section class="profile-hero card">
        <div class="profile-avatar-wrap">
            <img src="<?= e($profile_avatar_path) ?>" alt="<?= e($student['name']) ?>" class="profile-avatar" id="profileAvatarPreview">

            <label class="profile-avatar-upload d-none" for="avatarInput" id="profileAvatarUploadLabel">
                <i class="bi bi-camera" aria-hidden="true"></i>
                Change
            </label>

            <input type="file" id="avatarInput" name="avatar" class="profile-avatar-input" accept="image/jpeg,image/png,image/webp" disabled>
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
            <div class="alert alert-success">
                <?= e($success) ?>
            </div>
        <?php endif; ?>

        <?php if ($error !== ''): ?>
            <div class="alert alert-danger">
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
                    <input type="text" id="name" name="name" class="form-control" value="<?= e($student['name']) ?>" readonly>
                </div>

                <div class="col-md-6">
                    <label for="name_jp" class="form-label">Japanese Name</label>
                    <input type="text" id="name_jp" name="name_jp" class="form-control" value="<?= e($student['name_jp']) ?>" readonly>
                </div>

                <div class="col-md-4">
                    <label for="birthday" class="form-label">Birthday</label>
                    <input type="text" id="birthday" name="birthday" class="form-control" value="<?= e(format_profile_birthday($student['birthday'])) ?>" readonly>
                </div>

                <div class="col-md-4">
                    <label for="zodiac" class="form-label">Zodiac</label>
                    <input type="text" id="zodiac" name="zodiac" class="form-control" value="<?= e($student['zodiac']) ?>" readonly>
                </div>

                <div class="col-md-4">
                    <label for="blood_type" class="form-label">Blood Type</label>
                    <input type="text" id="blood_type" name="blood_type" class="form-control" value="<?= e($student['blood_type']) ?>" readonly>
                </div>

                <div class="col-md-4">
                    <label for="hometown" class="form-label">Hometown</label>
                    <input type="text" id="hometown" name="hometown" class="form-control profile-editable" value="<?= e($student['hometown']) ?>" readonly>
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
                    <input type="text" id="height" name="height" class="form-control" value="<?= e((string) $student['height']) ?> cm" readonly>
                </div>

                <div class="col-md-4">
                    <label for="weight" class="form-label">Weight</label>
                    <input type="text" id="weight" name="weight" class="form-control" value="<?= e((string) $student['weight']) ?> kg" readonly>
                </div>

                <div class="col-md-4">
                    <label for="three_size" class="form-label">Three Size</label>
                    <input type="text" id="three_size" name="three_size" class="form-control" value="<?= e($student['three_size']) ?>" readonly>
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
                    <input type="text" id="school_year" name="school_year" class="form-control" value="<?= e($student['school_year']) ?>" readonly>
                </div>

                <div class="col-md-4">
                    <label for="rank" class="form-label">Rank</label>
                    <input type="text" id="rank" name="rank" class="form-control" value="<?= e($student['rank']) ?>" readonly>
                </div>

                <div class="col-md-4">
                    <label for="producer" class="form-label">Producer</label>
                    <input type="text" id="producer" name="producer" class="form-control" value="<?= e($producer_display) ?>" readonly>
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
                    <textarea class="form-control profile-editable" name="special_skill" id="special_skill" rows="4" readonly><?= e($student['special_skill']) ?></textarea>
                </div>

                <div class="col-md-6">
                    <label for="hobbies" class="form-label">Hobbies</label>
                    <textarea class="form-control profile-editable" name="hobbies" id="hobbies" rows="4" readonly><?= e($student['hobbies']) ?></textarea>
                </div>

                <div class="col-12">
                    <label for="bio" class="form-label">Bio</label>
                    <textarea class="form-control profile-editable" name="bio" id="bio" rows="5" readonly><?= e($student['bio']) ?></textarea>
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

<script>
// Initially, editable fields are readonly
const profileEditButton = document.getElementById('profileEditButton');
const profileCancelButton = document.getElementById('profileCancelButton');
const profileFormActions = document.getElementById('profileFormActions');
const profileEditableFields = Array.from(document.querySelectorAll('.profile-editable'));
const profileAvatarInput = document.getElementById('avatarInput');
const profileAvatarUploadLabel = document.getElementById('profileAvatarUploadLabel');
const profileAvatarPreview = document.getElementById('profileAvatarPreview');
const originalProfileValues = new Map(profileEditableFields.map((field) => [field, field.value]));
const originalProfileAvatar = profileAvatarPreview ? profileAvatarPreview.src : '';

// Only inputs with class profile-editable can be editable
function setProfileEditMode(isEditing) {
    profileEditableFields.forEach((field) => {
        field.readOnly = !isEditing;
    });

    if (profileAvatarInput) {
        profileAvatarInput.disabled = !isEditing;
    }

    if (profileAvatarUploadLabel) {
        profileAvatarUploadLabel.classList.toggle('d-none', !isEditing);
    }

    if (profileFormActions) {
        profileFormActions.classList.toggle('d-none', !isEditing);
    }

    if (profileEditButton) {
        profileEditButton.setAttribute('aria-pressed', isEditing ? 'true' : 'false');
        profileEditButton.innerHTML = isEditing
            ? '<i class="bi bi-eye" aria-hidden="true"></i> View Profile'
            : '<i class="bi bi-pen" aria-hidden="true"></i> Edit Profile';
    }
}

if (profileEditButton) {
    profileEditButton.addEventListener('click', () => {
        const isEditing = profileEditButton.getAttribute('aria-pressed') !== 'true';
        setProfileEditMode(isEditing);
    });
}

//When click cancel, it reset the forms
if (profileCancelButton) {
    profileCancelButton.addEventListener('click', () => {
        profileEditableFields.forEach((field) => {
            field.value = originalProfileValues.get(field) ?? '';
        });

        if (profileAvatarInput) {
            profileAvatarInput.value = '';
        }

        if (profileAvatarPreview) {
            profileAvatarPreview.src = originalProfileAvatar;
        }

        setProfileEditMode(false);
    });
}

//When the user selects an image, the avatar preview changes immediately before saving
if (profileAvatarInput && profileAvatarPreview) {
    profileAvatarInput.addEventListener('change', () => {
        const file = profileAvatarInput.files[0];

        if (file) {
            profileAvatarPreview.src = URL.createObjectURL(file);
        }
    });
}
</script>

<?php require_once '../includes/footer.php'; ?>
