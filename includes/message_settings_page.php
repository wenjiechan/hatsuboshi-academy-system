<?php
$message_settings = $message_settings ?? [
    'message_background' => DEFAULT_MESSAGE_BACKGROUND,
    'message_text_size' => DEFAULT_MESSAGE_TEXT_SIZE,
    'compact_layout' => DEFAULT_MESSAGE_COMPACT_LAYOUT,
];
?>

<section class="settings-panel theme-settings-panel message-settings-panel">
    <div class="section-heading settings-card-heading">
        <div class="settings-card-title">
            <span class="settings-card-icon"><i class="bi bi-chat-dots"></i></span>
            <div>
                <p class="dashboard-eyebrow">Messaging</p>
                <h2>Message Settings</h2>
                <p class="settings-card-description">Adjust how conversations look while keeping your theme colors.</p>
            </div>
        </div>
    </div>

    <div class="settings-card-body">
        <form method="post" class="settings-form message-settings-form">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="settings_action" value="save_message_settings">

            <div class="message-settings-layout">
                <div class="message-settings-controls">
                    <fieldset class="message-setting-group">
                        <legend>Message background</legend>
                        <div class="message-option-grid">
                            <?php foreach (message_background_options() as $value => $label): ?>
                                <label class="message-choice-card">
                                    <input
                                        type="radio"
                                        name="message_background"
                                        value="<?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?>"
                                        <?= $message_settings['message_background'] === $value ? 'checked' : '' ?>
                                    >
                                    <span class="message-bg-preview message-bg-preview-<?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?>"></span>
                                    <strong><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></strong>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </fieldset>

                    <fieldset class="message-setting-group">
                        <legend>Message text size</legend>
                        <div class="message-segmented-options">
                            <?php foreach (message_text_size_options() as $value => $label): ?>
                                <label>
                                    <input
                                        type="radio"
                                        name="message_text_size"
                                        value="<?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?>"
                                        <?= $message_settings['message_text_size'] === $value ? 'checked' : '' ?>
                                    >
                                    <span><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </fieldset>

                    <label class="message-toggle-setting">
                        <span>
                            <strong>Compact layout</strong>
                            <small>Reduce message spacing and bubble padding.</small>
                        </span>
                        <input type="checkbox" name="compact_layout" value="1" <?= !empty($message_settings['compact_layout']) ? 'checked' : '' ?>>
                    </label>
                </div>

                <div
                    class="message-settings-preview message-settings-preview-<?= htmlspecialchars($message_settings['message_background'], ENT_QUOTES, 'UTF-8') ?> message-settings-preview-text-<?= htmlspecialchars($message_settings['message_text_size'], ENT_QUOTES, 'UTF-8') ?><?= !empty($message_settings['compact_layout']) ? ' is-compact' : '' ?>"
                    aria-label="Message settings preview"
                    data-message-settings-preview
                >
                    <div class="message-settings-preview-header">
                        <span></span>
                        <strong>Preview</strong>
                    </div>
                    <div class="message-settings-preview-thread">
                        <p class="preview-bubble">Theme colors stay in sync.</p>
                        <p class="preview-bubble own">Your messages keep the primary tint.</p>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Save Message Settings</button>
        </form>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('.message-settings-form');
    const preview = document.querySelector('[data-message-settings-preview]');

    if (!form || !preview) return;

    const backgrounds = ['default', 'primary', 'secondary', 'gradient', 'plain'];
    const sizes = ['small', 'normal', 'large'];
    const updatePreview = () => {
        const background = form.querySelector('input[name="message_background"]:checked')?.value || 'default';
        const textSize = form.querySelector('input[name="message_text_size"]:checked')?.value || 'normal';
        const compact = form.querySelector('input[name="compact_layout"]')?.checked || false;

        backgrounds.forEach((value) => preview.classList.toggle(`message-settings-preview-${value}`, value === background));
        sizes.forEach((value) => preview.classList.toggle(`message-settings-preview-text-${value}`, value === textSize));
        preview.classList.toggle('is-compact', compact);
    };

    form.addEventListener('change', updatePreview);
});
</script>
