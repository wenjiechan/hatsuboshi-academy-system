<?php
require_once '../includes/auth.php';
require_once '../config/database.php';
require_once '../includes/theme_settings_helpers.php';
require_once '../includes/password_settings_helpers.php';

$page_title = 'Settings';
$page_styles = ['/gakumas-sms/assets/css/pages/password-settings.css'];
$success = '';
$error = '';
$current_role = $_SESSION['role'] ?? '';
$password_change_verified = isset($_SESSION['password_change_verified_at'])
    && (time() - (int) $_SESSION['password_change_verified_at']) <= 300;

if (!$password_change_verified) {
    unset($_SESSION['password_change_verified_at']);
}

if ($current_role === 'student') {
    $stmt = $pdo->prepare('SELECT id FROM students WHERE user_id = ? LIMIT 1');
    $stmt->execute([$_SESSION['id']]);

    if (!$stmt->fetch()) {
        redirect_to_account_issue(
            'Student profile not found',
            'Your login is active, but no student profile is linked to this account yet.',
            404
        );
    }
}

$user_theme = load_user_theme($pdo, (int) $_SESSION['id']);
$current_primary = $user_theme['primary'];
$current_secondary = $user_theme['secondary'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf($_POST['csrf_token'] ?? '');
    $settings_action = $_POST['settings_action'] ?? '';

    if ($settings_action === 'save_theme') {
        $current_primary = normalize_theme_color($_POST['theme_primary_color'] ?? '', DEFAULT_THEME_PRIMARY);
        $current_secondary = normalize_theme_color($_POST['theme_secondary_color'] ?? '', DEFAULT_THEME_SECONDARY);
        save_user_theme($pdo, (int) $_SESSION['id'], $current_primary, $current_secondary);
        apply_theme_session($current_primary, $current_secondary);
        $success = 'Theme updated successfully.';
    } elseif ($settings_action === 'cancel_password_change') {
        unset($_SESSION['password_change_verified_at']);
        $password_change_verified = false;
    } elseif ($settings_action === 'verify_current_password') {
        if (verify_current_user_password($pdo, (int) $_SESSION['id'], (string) ($_POST['current_password'] ?? ''))) {
            $_SESSION['password_change_verified_at'] = time();
            $password_change_verified = true;
        } else {
            $error = 'The current password is incorrect.';
        }
    } elseif ($settings_action === 'change_password') {
        if (!$password_change_verified) {
            $error = 'Please verify your current password before choosing a new password.';
        } else {
            $error = change_verified_user_password(
                $pdo,
                (int) $_SESSION['id'],
                (string) ($_POST['new_password'] ?? ''),
                (string) ($_POST['confirm_password'] ?? '')
            );
        }

        if ($error === '') {
            $success = 'Password changed successfully.';
            unset($_SESSION['password_change_verified_at']);
            $password_change_verified = false;
        }
    }
}

apply_theme_session($current_primary, $current_secondary);
require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

<main class="dashboard-main">
    <?php if ($success !== ''): ?>
    <div class="alert alert-success settings-alert"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <?php if ($error !== ''): ?>
    <div class="alert alert-danger settings-alert" id="settingsErrorAlert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <?php require '../includes/theme_settings_page.php'; ?>
    <?php require '../includes/password_settings_page.php'; ?>
</main>

<?php require_once '../includes/footer.php'; ?>
