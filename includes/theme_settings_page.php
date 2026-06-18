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

//Loads current theme
$user_theme = load_user_theme($pdo, (int) $_SESSION['id']);
$current_primary = $user_theme['primary'];
$current_secondary = $user_theme['secondary'];

//When save theme, check csrf token and save colors in database after read and validate
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

//Apply the theme immediately
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

            <div class="theme-settings-layout">
                <div class="theme-preview" style="--preview-primary: <?= htmlspecialchars($current_primary, ENT_QUOTES, 'UTF-8') ?>; --preview-secondary: <?= htmlspecialchars($current_secondary, ENT_QUOTES, 'UTF-8') ?>;">
                    <span class="theme-preview-avatar"></span>
                    <div class="theme-preview-copy">
                        <small>Live preview</small>
                        <strong><?= htmlspecialchars($_SESSION['user_name'], ENT_QUOTES, 'UTF-8') ?></strong>
                    </div>
                    <div class="theme-preview-pills" aria-hidden="true">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </div>

                <div class="theme-controls">
                    <label class="theme-color-control">
                        <span class="theme-control-heading">
                            <span>Primary color</span>
                            <code class="js-theme-primary-value"><?= htmlspecialchars($current_primary, ENT_QUOTES, 'UTF-8') ?></code>
                        </span>
                        <input type="color" name="theme_primary_color" class="form-control form-control-color js-theme-primary"
                            value="<?= htmlspecialchars($current_primary, ENT_QUOTES, 'UTF-8') ?>">
                    </label>

                    <label class="theme-color-control">
                        <span class="theme-control-heading">
                            <span>Secondary color</span>
                            <code class="js-theme-secondary-value"><?= htmlspecialchars($current_secondary, ENT_QUOTES, 'UTF-8') ?></code>
                        </span>
                        <input type="color" name="theme_secondary_color" class="form-control form-control-color js-theme-secondary"
                            value="<?= htmlspecialchars($current_secondary, ENT_QUOTES, 'UTF-8') ?>">
                    </label>

                    <div class="theme-auto-panel">
                        <span class="theme-auto-title">Auto secondary</span>
                        <span class="theme-auto-actions" aria-label="Automatic secondary color options">
                            <button type="button" class="btn btn-sm btn-outline-primary js-secondary-brighter">
                                <i class="bi bi-brightness-high"></i>
                                20% brighter
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-primary js-secondary-darker">
                                <i class="bi bi-moon"></i>
                                20% darker
                            </button>
                        </span>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save"></i>
                Save Theme
            </button>
        </form>
    </section>
</main>

<script>
document.addEventListener('DOMContentLoaded', () => {
//Get the important elements
    const preview = document.querySelector('.theme-preview');
    const primaryInput = document.querySelector('.js-theme-primary');
    const secondaryInput = document.querySelector('.js-theme-secondary');
    const primaryValue = document.querySelector('.js-theme-primary-value');
    const secondaryValue = document.querySelector('.js-theme-secondary-value');
    const brighterButton = document.querySelector('.js-secondary-brighter');
    const darkerButton = document.querySelector('.js-secondary-darker');

    if (!preview || !primaryInput || !secondaryInput || !brighterButton || !darkerButton) {
        return;
    }

    const clampChannel = (value) => Math.max(0, Math.min(255, Math.round(value)));
    //Convert HEX to RGB
    const hexToRgb = (hex) => {
        const normalized = hex.replace('#', '');

        return {
            r: parseInt(normalized.slice(0, 2), 16),
            g: parseInt(normalized.slice(2, 4), 16),
            b: parseInt(normalized.slice(4, 6), 16),
        };
    };
    const channelToHex = (value) => clampChannel(value).toString(16).padStart(2, '0').toUpperCase();
    //Convert RGB back to HEX
    const rgbToHex = ({ r, g, b }) => `#${channelToHex(r)}${channelToHex(g)}${channelToHex(b)}`;
    // Main color fixing function
    const mixColor = (hex, target, amount) => {
        const source = hexToRgb(hex);

        return rgbToHex({
            r: source.r + (target.r - source.r) * amount,
            g: source.g + (target.g - source.g) * amount,
            b: source.b + (target.b - source.b) * amount,
        });
    };
    const updatePreview = () => {
        const primary = primaryInput.value.toUpperCase();
        const secondary = secondaryInput.value.toUpperCase();

        primaryInput.value = primary;
        secondaryInput.value = secondary;
        preview.style.setProperty('--preview-primary', primary);
        preview.style.setProperty('--preview-secondary', secondary);

        if (primaryValue) {
            primaryValue.textContent = primary;
        }

        if (secondaryValue) {
            secondaryValue.textContent = secondary;
        }
    };
    const setSecondaryFromPrimary = (target) => {
        secondaryInput.value = mixColor(primaryInput.value, target, 0.2);
        updatePreview();
    };

    primaryInput.addEventListener('input', updatePreview);
    secondaryInput.addEventListener('input', updatePreview);
    // Get 20% lighter or darker button
    brighterButton.addEventListener('click', () => setSecondaryFromPrimary({ r: 255, g: 255, b: 255 }));
    darkerButton.addEventListener('click', () => setSecondaryFromPrimary({ r: 0, g: 0, b: 0 }));
});
</script>

<?php require_once '../includes/footer.php'; ?>
