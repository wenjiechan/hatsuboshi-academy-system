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

$user_id = (int) $_SESSION['id'];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf((string) ($_POST['csrf_token'] ?? ''));

    $notification_action = (string) ($_POST['notification_action'] ?? '');
    $notification_ids = $_POST['notification_ids'] ?? [];

    if (!is_array($notification_ids)) {
        $notification_ids = [];
    }

    // Opening a grouped card marks every notification inside that group as read first.
    if ($notification_action === 'open_group') {
        mark_notifications_read($pdo, $user_id, $notification_ids);
        $action_url = normalize_notification_action_url((string) ($_POST['action_url'] ?? ''));
        header('Location: ' . $action_url);
        exit;
    }

    // Bulk actions receive comma-separated grouped IDs from each selected card.
    if ($notification_action === 'mark_selected_read') {
        mark_notifications_read($pdo, $user_id, $_POST['selected_notifications'] ?? []);
        $success = 'Selected notifications marked as read.';
    } elseif ($notification_action === 'delete_selected') {
        delete_notifications($pdo, $user_id, $_POST['selected_notifications'] ?? []);
        $success = 'Selected notifications deleted.';
    } elseif ($notification_action === 'mark_all_read') {
        mark_all_notifications_read($pdo, $user_id);
        $success = 'All notifications marked as read.';
    }
}

// Gets all notification for the current log in user
$notifications = get_user_notifications($pdo, $user_id);
// Group only for display, while keeping the original rows for read/delete history.
$notification_groups = group_user_notifications($notifications);
$unread_count = count(array_filter(
    $notifications,
    static fn(array $notification): bool => empty($notification['is_read'])
));

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
        NOTIFICATION_TYPE_NEW_MESSAGE => 'bi-envelope',
        NOTIFICATION_TYPE_STUDENT_REQUEST => 'bi-inbox',
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
        NOTIFICATION_TYPE_NEW_MESSAGE => 'message',
        NOTIFICATION_TYPE_STUDENT_REQUEST => 'request',
        default => 'schedule',
    };
}

function format_notification_date(string $date): string
{
    $timestamp = strtotime($date);

    return $timestamp ? date('M j, Y \a\t g:i A', $timestamp) : $date;
}

function is_birthday_message_action(array $notification): bool
{
    return (string) $notification['type'] === NOTIFICATION_TYPE_BIRTHDAY_TODAY
        && str_contains((string) $notification['action_url'], '/messages/send_birthday.php');
}

function render_notification_body(array $notification): string
{
    $body = (string) ($notification['body'] ?? '');

    if (!is_birthday_message_action($notification)) {
        return htmlspecialchars($body, ENT_QUOTES, 'UTF-8');
    }

    $action_label = 'Click here to send a birthday message!';
    // The card footer renders the action button, so remove the old inline action text.
    return htmlspecialchars(trim(str_replace($action_label, '', $body)), ENT_QUOTES, 'UTF-8');
}

function notification_group_ids_input(array $notification_ids): string
{
    $html = '';
    foreach ($notification_ids as $notification_id) {
        // One hidden input per row lets the open action mark the whole group as read.
        $html .= '<input type="hidden" name="notification_ids[]" value="' . (int) $notification_id . '">';
    }

    return $html;
}

function normalize_notification_action_url(string $action_url): string
{
    // Keep notification redirects inside this app.
    if ($action_url === '' || !str_starts_with($action_url, '/gakumas-sms/')) {
        return '/gakumas-sms/notifications.php';
    }

    return $action_url;
}

$page_title = 'Notifications';
require_once 'includes/header.php';
require_once 'includes/sidebar.php';
?>

<main class="dashboard-main notifications-main">
    <?php if ($success !== ''): ?>
        <div class="alert alert-success notifications-alert"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <section class="notifications-hero">
        <div>
            <p class="dashboard-eyebrow">Updates & Reminders</p>
            <h2>Notifications</h2>
            <p>Grouped updates from schedules, lessons, birthdays, messages, and system notices.</p>
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

    <section class="notifications-actions-panel" aria-label="Notification actions">
        <div class="notifications-actions-heading">
            <span class="notifications-actions-icon"><i class="bi bi-ui-checks"></i></span>
            <div>
                <strong>Bulk actions</strong>
            </div>
        </div>

        <form method="post" class="notifications-bulk-actions" id="bulkNotificationForm">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">

            <button type="submit" name="notification_action" value="mark_selected_read" class="btn btn-outline-primary">
                <i class="bi bi-check2-circle"></i> Mark selected read
            </button>
            <button type="submit" name="notification_action" value="delete_selected" class="btn btn-outline-danger">
                <i class="bi bi-trash3"></i> Delete selected
            </button>
            <button type="submit" name="notification_action" value="mark_all_read" class="btn btn-primary">
                <i class="bi bi-check-all"></i> Mark all read
            </button>
        </form>
    </section>

    <!--display empty state-->
    <?php if (empty($notification_groups)): ?>
        <div class="empty-dashboard-state notifications-empty-state">
            <i class="bi bi-bell-slash"></i>
            <strong>No notifications yet</strong>
            <p>New updates will appear here.</p>
        </div>
    <?php else: ?>
        <!--Show all notification-->
        <section class="notifications-card-grid" aria-label="Your notifications">
            <?php foreach ($notification_groups as $group): ?>
                <?php
                $notification = $group['latest'];
                $type_class = notification_type_class((string) $notification['type']);
                $is_unread = (int) $group['unread_count'] > 0;
                $is_content_action = is_birthday_message_action($notification);
                $group_count = (int) $group['count'];
                $group_unread_count = (int) $group['unread_count'];
                $action_label = match ((string) $notification['type']) {
                    NOTIFICATION_TYPE_BIRTHDAY_TODAY => $is_content_action
                        ? 'Click here to send a birthday message!'
                        : 'View',
                    NOTIFICATION_TYPE_STUDENT_REQUEST => 'View Request',
                    default => 'View',
                };
                ?>
                <article class="notification-card notification-card--<?= htmlspecialchars($type_class, ENT_QUOTES, 'UTF-8') ?><?= $is_unread ? ' unread' : '' ?>">
                    <input
                        class="notification-card-checkbox"
                        type="checkbox"
                        name="selected_notifications[]"
                        value="<?= htmlspecialchars(implode(',', array_map('intval', $group['ids'])), ENT_QUOTES, 'UTF-8') ?>"
                        form="bulkNotificationForm"
                        aria-label="Select <?= htmlspecialchars($notification['title'], ENT_QUOTES, 'UTF-8') ?>"
                    >

                    <div class="notification-card-top">
                        <div class="notification-card-icon">
                            <i class="bi <?= htmlspecialchars(notification_icon((string) $notification['type']), ENT_QUOTES, 'UTF-8') ?>"></i>
                        </div>

                        <div class="notification-card-badges">
                            <?php if ($group_count > 1): ?>
                                <span class="notification-count-badge"><?= $group_count ?> updates</span>
                            <?php endif; ?>

                            <?php if ($is_unread): ?>
                                <span class="notification-new-badge"><?= $group_unread_count > 1 ? $group_unread_count . ' new' : 'New' ?></span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="notification-card-content">
                        <h3><?= htmlspecialchars($notification['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                        <?php if (!empty($notification['body'])): ?>
                            <p><?= render_notification_body($notification) ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="notification-card-footer">
                        <time datetime="<?= htmlspecialchars($notification['created_at'], ENT_QUOTES, 'UTF-8') ?>">
                            <i class="bi bi-clock"></i>
                            <?= htmlspecialchars(format_notification_date((string) $notification['created_at']), ENT_QUOTES, 'UTF-8') ?>
                        </time>

                        <?php if (!empty($notification['action_url']) && !$is_content_action): ?>
                            <form method="post" class="notification-open-form">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                                <input type="hidden" name="notification_action" value="open_group">
                                <input type="hidden" name="action_url" value="<?= htmlspecialchars((string) $notification['action_url'], ENT_QUOTES, 'UTF-8') ?>">
                                <?= notification_group_ids_input($group['ids']) ?>
                                <button type="submit" class="notification-card-link">
                                    <?= htmlspecialchars($action_label, ENT_QUOTES, 'UTF-8') ?>
                                    <i class="bi bi-arrow-right"></i>
                                </button>
                            </form>
                        <?php elseif ($is_content_action): ?>
                            <form method="post" class="notification-open-form">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                                <input type="hidden" name="notification_action" value="open_group">
                                <input type="hidden" name="action_url" value="<?= htmlspecialchars((string) $notification['action_url'], ENT_QUOTES, 'UTF-8') ?>">
                                <?= notification_group_ids_input($group['ids']) ?>
                                <button type="submit" class="notification-card-link">
                                <?= htmlspecialchars($action_label, ENT_QUOTES, 'UTF-8') ?>
                                <i class="bi bi-arrow-right"></i>
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </section>
    <?php endif; ?>
</main>

<?php require_once 'includes/footer.php'; ?>
