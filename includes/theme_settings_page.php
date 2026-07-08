<?php
$current_primary = $current_primary ?? ($_SESSION['theme_primary_color'] ?? DEFAULT_THEME_PRIMARY);
$current_secondary = $current_secondary ?? ($_SESSION['theme_secondary_color'] ?? DEFAULT_THEME_SECONDARY);
?>

<section class="settings-panel theme-settings-panel">
    <div class="section-heading settings-card-heading">
        <div class="settings-card-title">
            <span class="settings-card-icon"><i class="bi bi-palette"></i></span>
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
                <div class="theme-preview-copy"><small>Live preview</small><strong><?= htmlspecialchars($_SESSION['user_name'], ENT_QUOTES, 'UTF-8') ?></strong></div>
                <div class="theme-preview-pills" aria-hidden="true"><span></span><span></span><span></span></div>
            </div>
            <div class="theme-controls">
                <label class="theme-color-control">
                    <span class="theme-control-heading"><span>Primary color</span><code class="js-theme-primary-value"><?= htmlspecialchars($current_primary, ENT_QUOTES, 'UTF-8') ?></code></span>
                    <input type="color" name="theme_primary_color" class="form-control form-control-color js-theme-primary" value="<?= htmlspecialchars($current_primary, ENT_QUOTES, 'UTF-8') ?>">
                </label>
                <label class="theme-color-control">
                    <span class="theme-control-heading"><span>Secondary color</span><code class="js-theme-secondary-value"><?= htmlspecialchars($current_secondary, ENT_QUOTES, 'UTF-8') ?></code></span>
                    <input type="color" name="theme_secondary_color" class="form-control form-control-color js-theme-secondary" value="<?= htmlspecialchars($current_secondary, ENT_QUOTES, 'UTF-8') ?>">
                </label>
                <div class="theme-auto-panel">
                    <span class="theme-auto-title">Auto secondary</span>
                    <span class="theme-auto-actions">
                        <button type="button" class="btn btn-sm btn-outline-primary js-secondary-brighter"><i class="bi bi-brightness-high"></i> 20% brighter</button>
                        <button type="button" class="btn btn-sm btn-outline-primary js-secondary-darker"><i class="bi bi-moon"></i> 20% darker</button>
                    </span>
                </div>
            </div>
        </div>
        <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Save Theme</button>
    </form>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const preview = document.querySelector('.theme-preview');
    const primaryInput = document.querySelector('.js-theme-primary');
    const secondaryInput = document.querySelector('.js-theme-secondary');
    const primaryValue = document.querySelector('.js-theme-primary-value');
    const secondaryValue = document.querySelector('.js-theme-secondary-value');
    const brighterButton = document.querySelector('.js-secondary-brighter');
    const darkerButton = document.querySelector('.js-secondary-darker');
    if (!preview || !primaryInput || !secondaryInput || !brighterButton || !darkerButton) return;

    const clamp = (value) => Math.max(0, Math.min(255, Math.round(value)));
    const toRgb = (hex) => ({ r: parseInt(hex.slice(1, 3), 16), g: parseInt(hex.slice(3, 5), 16), b: parseInt(hex.slice(5, 7), 16) });
    const channel = (value) => clamp(value).toString(16).padStart(2, '0').toUpperCase();
    const toHex = ({ r, g, b }) => `#${channel(r)}${channel(g)}${channel(b)}`;
    const mix = (hex, target) => {
        const source = toRgb(hex);
        return toHex({ r: source.r + (target.r - source.r) * .2, g: source.g + (target.g - source.g) * .2, b: source.b + (target.b - source.b) * .2 });
    };
    const update = () => {
        const primary = primaryInput.value.toUpperCase();
        const secondary = secondaryInput.value.toUpperCase();
        preview.style.setProperty('--preview-primary', primary);
        preview.style.setProperty('--preview-secondary', secondary);
        if (primaryValue) primaryValue.textContent = primary;
        if (secondaryValue) secondaryValue.textContent = secondary;
    };
    primaryInput.addEventListener('input', update);
    secondaryInput.addEventListener('input', update);
    brighterButton.addEventListener('click', () => { secondaryInput.value = mix(primaryInput.value, { r: 255, g: 255, b: 255 }); update(); });
    darkerButton.addEventListener('click', () => { secondaryInput.value = mix(primaryInput.value, { r: 0, g: 0, b: 0 }); update(); });
});
</script>
