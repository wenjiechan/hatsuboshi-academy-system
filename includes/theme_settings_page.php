<?php
require_once '../includes/auth.php';
require_once '../config/database.php';
require_once '../includes/theme_settings_helpers.php';

$page_title = 'Settings';
$success = '';
$error = '';
$current_role = $_SESSION['role'] ?? '';

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

    $primary = normalize_theme_color($_POST['theme_primary_color'] ?? '', DEFAULT_THEME_PRIMARY);
    $secondary = normalize_theme_color($_POST['theme_secondary_color'] ?? '', DEFAULT_THEME_SECONDARY);

    save_user_theme($pdo, (int) $_SESSION['id'], $primary, $secondary);

    $current_primary = $primary;
    $current_secondary = $secondary;
    apply_theme_session($primary, $secondary);
    $success = 'Theme updated successfully.';
}

apply_theme_session($current_primary, $current_secondary);

require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

<main class="dashboard-main">
    <section class="settings-panel">
        <div class="section-heading">
            <div>
                <p class="dashboard-eyebrow">Personalization</p>
                <h2>Theme Colors</h2>
            </div>
        </div>

        <?php if ($success !== ''): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?>
        </div>
        <?php endif; ?>

        <?php if ($error !== ''): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        </div>
        <?php endif; ?>

        <form method="post" class="settings-form">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">

            <div class="theme-preview" style="--preview-primary: <?= htmlspecialchars($current_primary, ENT_QUOTES, 'UTF-8') ?>; --preview-secondary: <?= htmlspecialchars($current_secondary, ENT_QUOTES, 'UTF-8') ?>;">
                <span></span>
                <strong><?= htmlspecialchars($_SESSION['user_name'], ENT_QUOTES, 'UTF-8') ?></strong>
            </div>

            <div class="settings-grid">
                <label class="form-label">
                    Primary color
                    <input type="color" name="theme_primary_color" class="form-control form-control-color"
                        value="<?= htmlspecialchars($current_primary, ENT_QUOTES, 'UTF-8') ?>">
                </label>

                <label class="form-label">
                    Secondary color
                    <input type="color" name="theme_secondary_color" class="form-control form-control-color"
                        value="<?= htmlspecialchars($current_secondary, ENT_QUOTES, 'UTF-8') ?>">
                </label>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save"></i>
                Save Theme
            </button>
        </form>
    </section>
</main>

<?php require_once '../includes/footer.php'; ?>
