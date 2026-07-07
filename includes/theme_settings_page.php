<?php
require_once '../includes/auth.php';
require_once '../config/database.php';
require_once '../includes/theme_settings_helpers.php';

$page_title = 'Settings';
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

//Loads current theme
$user_theme = load_user_theme($pdo, (int) $_SESSION['id']);
$current_primary = $user_theme['primary'];
$current_secondary = $user_theme['secondary'];

//When save theme, check csrf token and save colors in database after read and validate
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf($_POST['csrf_token'] ?? '');
    $settings_action = $_POST['settings_action'] ?? '';

    if ($settings_action === 'cancel_password_change') {
        unset($_SESSION['password_change_verified_at']);
        $password_change_verified = false;
    } elseif ($settings_action === 'verify_current_password') {
        if (verify_current_user_password(
            $pdo,
            (int) $_SESSION['id'],
            (string) ($_POST['current_password'] ?? '')
        )) {
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
    } elseif ($settings_action === 'save_theme') {
        $primary = normalize_theme_color($_POST['theme_primary_color'] ?? '', DEFAULT_THEME_PRIMARY);
        $secondary = normalize_theme_color($_POST['theme_secondary_color'] ?? '', DEFAULT_THEME_SECONDARY);

        save_user_theme($pdo, (int) $_SESSION['id'], $primary, $secondary);

        $current_primary = $primary;
        $current_secondary = $secondary;
        apply_theme_session($primary, $secondary);
        $success = 'Theme updated successfully.';
    }
}

//Apply the theme immediately
apply_theme_session($current_primary, $current_secondary);

require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

<main class="dashboard-main">
    <?php if ($success !== ''): ?>
    <div class="alert alert-success settings-alert">
        <?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?>
    </div>
    <?php endif; ?>

    <?php if ($error !== ''): ?>
    <div class="alert alert-danger settings-alert" id="settingsErrorAlert">
        <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
    </div>
    <?php endif; ?>

    <div class="settings-layout-grid">
    <section class="settings-panel theme-settings-panel">
        <div class="section-heading settings-card-heading">
            <div class="settings-card-title">
                <span class="settings-card-icon" aria-hidden="true"><i class="bi bi-palette"></i></span>
                <div>
                <p class="dashboard-eyebrow">Personalization</p>
                <h2>Theme Colors</h2>
                    <p class="settings-card-description">Choose colors that personalize your workspace.</p>
                </div>
            </div>
        </div>

        <div class="settings-card-body">
        <form method="post" class="settings-form">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="settings_action" value="save_theme">

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
        </div>
    </section>

    <section class="settings-panel password-settings-panel">
        <div class="section-heading password-settings-heading">
            <div class="password-settings-title">
                <span class="password-settings-icon" aria-hidden="true"><i class="bi bi-key"></i></span>
                <div>
                <p class="dashboard-eyebrow">Account Security</p>
                <h2>Change Password</h2>
                    <p class="password-settings-description">Verify your identity before creating a new password.</p>
                </div>
            </div>
        </div>

        <div class="password-steps" aria-label="Password change progress">
            <div class="password-step <?= $password_change_verified ? 'is-complete' : 'is-active' ?>">
                <span class="password-step-number"><?= $password_change_verified ? '<i class="bi bi-check-lg"></i>' : '1' ?></span>
                <span><small>Step 1</small><strong>Verify identity</strong></span>
            </div>
            <span class="password-step-line<?= $password_change_verified ? ' is-complete' : '' ?>" aria-hidden="true"></span>
            <div class="password-step<?= $password_change_verified ? ' is-active' : '' ?>">
                <span class="password-step-number">2</span>
                <span><small>Step 2</small><strong>Set new password</strong></span>
            </div>
        </div>

        <div class="password-settings-body">

        <?php if (!$password_change_verified): ?>
        <div class="password-start-copy<?= $error !== '' ? ' d-none' : '' ?>">
            <i class="bi bi-shield-check" aria-hidden="true"></i>
            <div>
                <strong>Keep your account protected</strong>
                <span>You will need your current password to continue.</span>
            </div>
        </div>
        <button type="button" class="btn btn-primary password-change-start<?= $error !== '' ? ' d-none' : '' ?>" id="passwordChangeStart"
            aria-controls="currentPasswordForm" aria-expanded="<?= $error !== '' ? 'true' : 'false' ?>">
            <i class="bi bi-shield-lock"></i>
            Change Password
        </button>

        <form method="post" class="settings-form password-settings-form<?= $error === '' ? ' d-none' : '' ?>"
            id="currentPasswordForm">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="settings_action" value="verify_current_password">

            <div class="password-settings-fields password-settings-current">
                <div>
                    <label for="current_password" class="form-label">Current password</label>
                    <div class="password-input-wrap">
                        <i class="bi bi-lock password-input-icon" aria-hidden="true"></i>
                        <input type="password" id="current_password" name="current_password" class="form-control"
                            autocomplete="current-password" required>
                        <button type="button" class="password-visibility-toggle" data-password-target="current_password"
                            aria-label="Show current password"><i class="bi bi-eye"></i></button>
                    </div>
                    <div class="form-text">Enter the password you currently use to sign in.</div>
                </div>
            </div>

            <div class="password-form-actions">
                <button type="button" class="btn password-cancel-button password-change-cancel" id="passwordChangeCancel">
                    <i class="bi bi-x-lg" aria-hidden="true"></i>
                    Cancel
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i>
                    Verify Current Password
                </button>
            </div>
        </form>
        <?php else: ?>
        <div class="password-verification-success">
            <i class="bi bi-check-circle-fill"></i>
            <div><strong>Identity verified</strong><span>You can now choose a new password.</span></div>
        </div>

        <form method="post" class="settings-form password-settings-form">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">

            <div class="password-settings-fields password-settings-new">
                <div>
                    <label for="new_password" class="form-label">New password</label>
                    <div class="password-input-wrap">
                        <i class="bi bi-lock password-input-icon" aria-hidden="true"></i>
                        <input type="password" id="new_password" name="new_password" class="form-control"
                            autocomplete="new-password" minlength="6" required>
                        <button type="button" class="password-visibility-toggle" data-password-target="new_password"
                            aria-label="Show new password"><i class="bi bi-eye"></i></button>
                    </div>
                    <div class="form-text">Use at least 6 characters.</div>
                </div>

                <div>
                    <label for="confirm_password" class="form-label">Confirm new password</label>
                    <div class="password-input-wrap">
                        <i class="bi bi-check2-circle password-input-icon" aria-hidden="true"></i>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control"
                            autocomplete="new-password" minlength="6" required>
                        <button type="button" class="password-visibility-toggle" data-password-target="confirm_password"
                            aria-label="Show confirmed password"><i class="bi bi-eye"></i></button>
                    </div>
                    <div class="form-text">Enter the same password again.</div>
                </div>
            </div>

            <div class="password-form-actions">
                <button type="submit" class="btn password-cancel-button" name="settings_action"
                    value="cancel_password_change" formnovalidate>
                    <i class="bi bi-x-lg" aria-hidden="true"></i>
                    Cancel
                </button>
                <button type="submit" class="btn btn-primary" name="settings_action" value="change_password">
                    <i class="bi bi-shield-lock"></i>
                    Change Password
                </button>
            </div>
        </form>
        <?php endif; ?>
        </div>
    </section>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const passwordChangeStart = document.getElementById('passwordChangeStart');
    const currentPasswordForm = document.getElementById('currentPasswordForm');
    const passwordChangeCancel = document.getElementById('passwordChangeCancel');

    if (passwordChangeStart && currentPasswordForm) {
        passwordChangeStart.addEventListener('click', () => {
            currentPasswordForm.classList.remove('d-none');
            passwordChangeStart.classList.add('d-none');
            passwordChangeStart.setAttribute('aria-expanded', 'true');
            document.getElementById('current_password')?.focus();
        });
    }

    if (passwordChangeCancel && passwordChangeStart && currentPasswordForm) {
        passwordChangeCancel.addEventListener('click', () => {
            currentPasswordForm.reset();
            currentPasswordForm.classList.add('d-none');
            passwordChangeStart.classList.remove('d-none');
            passwordChangeStart.setAttribute('aria-expanded', 'false');
            document.querySelector('.password-start-copy')?.classList.remove('d-none');
            document.getElementById('settingsErrorAlert')?.classList.add('d-none');
        });
    }

    document.querySelectorAll('.password-visibility-toggle').forEach((toggle) => {
        toggle.addEventListener('click', () => {
            const input = document.getElementById(toggle.dataset.passwordTarget);

            if (!input) return;

            const showPassword = input.type === 'password';
            input.type = showPassword ? 'text' : 'password';
            toggle.querySelector('i')?.classList.toggle('bi-eye', !showPassword);
            toggle.querySelector('i')?.classList.toggle('bi-eye-slash', showPassword);
            toggle.setAttribute('aria-label', showPassword ? 'Hide password' : 'Show password');
        });
    });

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
