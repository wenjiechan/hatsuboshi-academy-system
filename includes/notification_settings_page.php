<?php
$notification_settings = $notification_settings ?? array_fill_keys(array_keys(notification_category_options()), true);
?>

<section class="settings-panel theme-settings-panel notification-settings-panel">
    <div class="section-heading settings-card-heading">
        <div class="settings-card-title">
            <span class="settings-card-icon"><i class="bi bi-bell"></i></span>
            <div>
                <p class="dashboard-eyebrow">Notifications</p>
                <h2>Notification Settings</h2>
                <p class="settings-card-description">Choose which updates should appear in your notification feed.</p>
            </div>
        </div>
    </div>

    <div class="settings-card-body">
        <form method="post" class="settings-form notification-settings-form">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="settings_action" value="save_notification_settings">

            <div class="notification-settings-grid">
                <?php foreach (notification_category_options() as $category => $label): ?>
                    <label class="notification-choice-card">
                        <span class="notification-choice-icon">
                            <i class="bi <?= htmlspecialchars(notification_settings_icon($category), ENT_QUOTES, 'UTF-8') ?>"></i>
                        </span>
                        <span>
                            <strong><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></strong>
                            <small><?= htmlspecialchars(notification_settings_description($category), ENT_QUOTES, 'UTF-8') ?></small>
                        </span>
                        <input
                            type="checkbox"
                            name="notification_categories[]"
                            value="<?= htmlspecialchars($category, ENT_QUOTES, 'UTF-8') ?>"
                            <?= !empty($notification_settings[$category]) ? 'checked' : '' ?>
                        >
                    </label>
                <?php endforeach; ?>
            </div>

            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Save Notification Settings</button>
        </form>
    </div>
</section>

<?php
function notification_settings_icon(string $category): string
{
    return match ($category) {
        NOTIFICATION_CATEGORY_MESSAGES => 'bi-chat-dots',
        NOTIFICATION_CATEGORY_SCHEDULES => 'bi-calendar-event',
        NOTIFICATION_CATEGORY_LESSONS => 'bi-journal-check',
        NOTIFICATION_CATEGORY_BIRTHDAYS => 'bi-cake2',
        NOTIFICATION_CATEGORY_REQUESTS => 'bi-inbox',
        default => 'bi-bell',
    };
}

function notification_settings_description(string $category): string
{
    return match ($category) {
        NOTIFICATION_CATEGORY_MESSAGES => 'Chat, group, forwarded, and mention updates.',
        NOTIFICATION_CATEGORY_SCHEDULES => 'Created, updated, cancelled, and starting schedule alerts.',
        NOTIFICATION_CATEGORY_LESSONS => 'Lesson start and lesson detail updates.',
        NOTIFICATION_CATEGORY_BIRTHDAYS => 'Birthday reminders and birthday message prompts.',
        NOTIFICATION_CATEGORY_REQUESTS => 'Student and producer request updates.',
        default => 'General account and system notices.',
    };
}
?>
