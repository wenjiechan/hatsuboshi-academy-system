<?php
require_once 'includes/auth.php';

require_once 'config/database.php';
require_once 'includes/theme_settings_helpers.php';
require_once 'includes/notifications_helpers.php';

//Redirect to the error page
if (empty($_SESSION['id'])) {
    redirect_to_account_issue(
        'Profile not found',
        'Your login is active, but no profile is linked to this account yet. Please log out and try again.',
        404
    );
}

// Gets all notification for the current log in user
$notifications = get_user_notifications($pdo, (int) $_SESSION['id']);
$unread_count = count(array_filter(
    $notifications,
    static fn(array $notification): bool => empty($notification['is_read'])
));
// When the user opens the notification page, all their notification become read
mark_all_notifications_read($pdo, (int) $_SESSION['id']);

function notification_icon(string $type): string
{
    return match ($type) {
        NOTIFICATION_TYPE_SCHEDULE_START => 'bi-calendar-event',
        NOTIFICATION_TYPE_LESSON_START => 'bi-journal-check',
        NOTIFICATION_TYPE_BIRTHDAY_UPCOMING,
        NOTIFICATION_TYPE_BIRTHDAY_TODAY => 'bi-cake2',
        NOTIFICATION_TYPE_SCHEDULE_CREATED => 'bi-calendar-plus',
        NOTIFICATION_TYPE_SCHEDULE_UPDATED => 'bi-calendar2-check',
        NOTIFICATION_TYPE_SCHEDULE_CANCELLED => 'bi-calendar-x',
        NOTIFICATION_TYPE_LESSON_UPDATED => 'bi-journal-text',
        default => 'bi-bell',
    };
}

function notification_type_class(string $type): string
{
    return match ($type) {
        NOTIFICATION_TYPE_BIRTHDAY_UPCOMING,
        NOTIFICATION_TYPE_BIRTHDAY_TODAY => 'birthday',
        NOTIFICATION_TYPE_SCHEDULE_CANCELLED => 'cancelled',
        NOTIFICATION_TYPE_LESSON_START,
        NOTIFICATION_TYPE_LESSON_UPDATED => 'lesson',
        default => 'schedule',
    };
}

function format_notification_date(string $date): string
{
    $timestamp = strtotime($date);

    return $timestamp ? date('M j, Y \a\t g:i A', $timestamp) : $date;
}

$page_title = 'Notifications';
require_once 'includes/header.php';
require_once 'includes/sidebar.php';
?>

<main class="dashboard-main notifications-main">
    <section class="notifications-hero">
        <div>
            <p class="dashboard-eyebrow">Updates & Reminders</p>
            <h2>Notifications</h2>
            <p>Recent updates from schedules, lessons, birthdays, messages, and system notices.</p>
        </div>

        <div class="notifications-summary-grid">
            <div>
                <span>Total</span>
                <strong><?= count($notifications) ?></strong>
            </div>

            <div>
                <span>New</span>
                <strong><?= $unread_count ?></strong>
            </div>
        </div>
    </section>

    <!--display empty state-->
    <?php if (empty($notifications)): ?>
        <div class="empty-dashboard-state notifications-empty-state">
            <i class="bi bi-bell-slash"></i>
            <strong>No notifications yet</strong>
            <p>New updates will appear here.</p>
        </div>
    <?php else: ?>
        <!--Show all notification-->
        <section class="notifications-card-grid" aria-label="Your notifications">
            <?php foreach ($notifications as $notification): ?>
                <?php
                $type_class = notification_type_class((string) $notification['type']);
                $is_unread = empty($notification['is_read']);
                ?>
                <article class="notification-card notification-card--<?= htmlspecialchars($type_class, ENT_QUOTES, 'UTF-8') ?><?= $is_unread ? ' unread' : '' ?>">
                    <div class="notification-card-top">
                        <div class="notification-card-icon">
                            <i class="bi <?= htmlspecialchars(notification_icon((string) $notification['type']), ENT_QUOTES, 'UTF-8') ?>"></i>
                        </div>

                        <?php if ($is_unread): ?>
                            <span class="notification-new-badge">New</span>
                        <?php endif; ?>
                    </div>

                    <div class="notification-card-content">
                        <h3><?= htmlspecialchars($notification['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                        <?php if (!empty($notification['body'])): ?>
                            <p><?= htmlspecialchars($notification['body'], ENT_QUOTES, 'UTF-8') ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="notification-card-footer">
                        <time datetime="<?= htmlspecialchars($notification['created_at'], ENT_QUOTES, 'UTF-8') ?>">
                            <i class="bi bi-clock"></i>
                            <?= htmlspecialchars(format_notification_date((string) $notification['created_at']), ENT_QUOTES, 'UTF-8') ?>
                        </time>

                        <?php if (!empty($notification['action_url'])): ?>
                            <a href="<?= htmlspecialchars($notification['action_url'], ENT_QUOTES, 'UTF-8') ?>" class="notification-card-link">
                                View
                                <i class="bi bi-arrow-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </section>
    <?php endif; ?>
</main>

<?php require_once 'includes/footer.php'; ?>

