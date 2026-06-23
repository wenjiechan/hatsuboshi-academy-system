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

$notifications = get_user_notifications($pdo, (int) $_SESSION['id']);
mark_all_notifications_read($pdo, (int) $_SESSION['id']);

$page_title = 'Notifications';
require_once 'includes/header.php';
require_once 'includes/sidebar.php';
?>

<main class="dashboard-main">
    <section class="section-heading">
        <div>
            <h2>Notifications</h2>
            <p>Recent updates from schedules, lessons, birthdays, messages, and system notices.</p>
        </div>
    </section>

    <?php if (empty($notifications)): ?>
        <div class="empty-dashboard-state">
            <strong>No notifications yet</strong>
            <p>New updates will appear here.</p>
        </div>
    <?php else: ?>
        <section class="notifications-list">
            <?php foreach ($notifications as $notification): ?>
                <article class="notification-item <?= $notification['is_read'] ? '' : 'unread' ?>">
                    <div class="notification-icon">
                        <i class="bi bi-bell"></i>
                    </div>

                    <div class="notification-content">
                        <h3><?= htmlspecialchars($notification['title'], ENT_QUOTES, 'UTF-8') ?></h3>

                        <?php if (!empty($notification['body'])): ?>
                            <p><?= htmlspecialchars($notification['body'], ENT_QUOTES, 'UTF-8') ?></p>
                        <?php endif; ?>

                        <time datetime="<?= htmlspecialchars($notification['created_at'], ENT_QUOTES, 'UTF-8') ?>">
                            <?= htmlspecialchars($notification['created_at'], ENT_QUOTES, 'UTF-8') ?>
                        </time>
                    </div>

                    <?php if (!empty($notification['action_url'])): ?>
                        <a href="<?= htmlspecialchars($notification['action_url'], ENT_QUOTES, 'UTF-8') ?>" class="btn btn-sm btn-outline-primary">
                            View
                        </a>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </section>
    <?php endif; ?>
</main>

<?php require_once 'includes/footer.php'; ?>

