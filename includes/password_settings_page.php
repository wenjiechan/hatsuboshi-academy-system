<?php
$error = $error ?? '';
$password_change_verified = $password_change_verified
    ?? (isset($_SESSION['password_change_verified_at'])
        && (time() - (int) $_SESSION['password_change_verified_at']) <= 300);
?>

<section class="password-settings-panel">
    <header class="password-settings-heading">
        <div class="password-settings-title">
            <span class="password-settings-icon"><i class="bi bi-key"></i></span>
            <div>
                <p class="dashboard-eyebrow">Account Security</p>
                <h2>Change Password</h2>
                <p>Verify your identity before creating a new password.</p>
            </div>
        </div>
    </header>

    <div class="password-steps" aria-label="Password change progress">
        <div class="password-step <?= $password_change_verified ? 'is-complete' : 'is-active' ?>">
            <span class="password-step-number"><?= $password_change_verified ? '<i class="bi bi-check-lg"></i>' : '1' ?></span>
            <span><small>Step 1</small><strong>Verify identity</strong></span>
        </div>
        <span class="password-step-line<?= $password_change_verified ? ' is-complete' : '' ?>"></span>
        <div class="password-step<?= $password_change_verified ? ' is-active' : '' ?>">
            <span class="password-step-number">2</span>
            <span><small>Step 2</small><strong>Set new password</strong></span>
        </div>
    </div>

    <div class="password-settings-body">
    <?php if (!$password_change_verified): ?>
        <div class="password-start-copy<?= $error !== '' ? ' d-none' : '' ?>">
            <i class="bi bi-shield-check"></i>
            <div><strong>Keep your account protected</strong><span>You will need your current password to continue.</span></div>
        </div>
        <button type="button" class="btn btn-primary password-change-start<?= $error !== '' ? ' d-none' : '' ?>" id="passwordChangeStart">
            <i class="bi bi-shield-lock"></i> Change Password
        </button>
        <form method="post" class="password-settings-form<?= $error === '' ? ' d-none' : '' ?>" id="currentPasswordForm">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="settings_action" value="verify_current_password">
            <div>
                <label for="current_password" class="form-label">Current password</label>
                <div class="password-input-wrap">
                    <i class="bi bi-lock password-input-icon"></i>
                    <input type="password" id="current_password" name="current_password" class="form-control" autocomplete="current-password" required>
                    <button type="button" class="password-visibility-toggle" data-password-target="current_password" aria-label="Show current password"><i class="bi bi-eye"></i></button>
                </div>
                <div class="form-text">Enter the password you currently use to sign in.</div>
            </div>
            <div class="password-form-actions">
                <button type="button" class="btn password-cancel-button" id="passwordChangeCancel"><i class="bi bi-x-lg"></i> Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Verify Current Password</button>
            </div>
        </form>
    <?php else: ?>
        <div class="password-verification-success"><i class="bi bi-check-circle-fill"></i><div><strong>Identity verified</strong><span>You can now choose a new password.</span></div></div>
        <form method="post" class="password-settings-form">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
            <div class="password-settings-new">
                <div>
                    <label for="new_password" class="form-label">New password</label>
                    <div class="password-input-wrap"><i class="bi bi-lock password-input-icon"></i><input type="password" id="new_password" name="new_password" class="form-control" autocomplete="new-password" minlength="6" required><button type="button" class="password-visibility-toggle" data-password-target="new_password" aria-label="Show new password"><i class="bi bi-eye"></i></button></div>
                    <div class="form-text">Use at least 6 characters.</div>
                </div>
                <div>
                    <label for="confirm_password" class="form-label">Confirm new password</label>
                    <div class="password-input-wrap"><i class="bi bi-check2-circle password-input-icon"></i><input type="password" id="confirm_password" name="confirm_password" class="form-control" autocomplete="new-password" minlength="6" required><button type="button" class="password-visibility-toggle" data-password-target="confirm_password" aria-label="Show confirmed password"><i class="bi bi-eye"></i></button></div>
                    <div class="form-text">Enter the same password again.</div>
                </div>
            </div>
            <div class="password-form-actions">
                <button type="submit" class="btn password-cancel-button" name="settings_action" value="cancel_password_change" formnovalidate><i class="bi bi-x-lg"></i> Cancel</button>
                <button type="submit" class="btn btn-primary" name="settings_action" value="change_password"><i class="bi bi-shield-lock"></i> Change Password</button>
            </div>
        </form>
    <?php endif; ?>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const start = document.getElementById('passwordChangeStart');
    const form = document.getElementById('currentPasswordForm');
    const cancel = document.getElementById('passwordChangeCancel');
    start?.addEventListener('click', () => { form?.classList.remove('d-none'); start.classList.add('d-none'); document.querySelector('.password-start-copy')?.classList.add('d-none'); document.getElementById('current_password')?.focus(); });
    cancel?.addEventListener('click', () => { form?.reset(); form?.classList.add('d-none'); start?.classList.remove('d-none'); document.querySelector('.password-start-copy')?.classList.remove('d-none'); document.getElementById('settingsErrorAlert')?.classList.add('d-none'); });
    document.querySelectorAll('.password-visibility-toggle').forEach((toggle) => {
        toggle.addEventListener('click', () => {
            const input = document.getElementById(toggle.dataset.passwordTarget);
            if (!input) return;
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            toggle.querySelector('i')?.classList.toggle('bi-eye', !show);
            toggle.querySelector('i')?.classList.toggle('bi-eye-slash', show);
        });
    });
});
</script>
