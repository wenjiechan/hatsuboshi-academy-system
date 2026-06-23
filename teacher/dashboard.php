<?php
require_once '../includes/auth.php';
require_role('teacher');

require_once '../config/database.php';
require_once '../includes/birthday_banner_helpers.php';
require_once '../includes/notifications_helpers.php';

generate_automatic_notifications($pdo);

$birthday_students = get_dashboard_birthday_students($pdo);

$page_title = 'Teacher Dashboard';
require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

<main class="dashboard-main">
    <?php require '../includes/birthday_banner.php'; ?>

    <?php if (empty($birthday_students)): ?>
    <section class="empty-dashboard-state">
        <strong>No idol birthdays today</strong>
        <p>The birthday banner will appear here automatically on each idol's birthday.</p>
    </section>
    <?php endif; ?>
</main>

<?php require_once '../includes/footer.php'; ?>
