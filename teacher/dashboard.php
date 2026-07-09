<?php
require_once '../includes/auth.php';
require_role('teacher');

require_once '../config/database.php';
require_once '../includes/birthday_banner_helpers.php';
require_once '../includes/notifications_helpers.php';
require_once '../includes/messages_helpers.php';

// Run automatic background-style checks when the teacher dashboard loads.
generate_automatic_notifications($pdo);
generate_automatic_birthday_messages($pdo);

// The birthday banner is shared across roles, so load today's birthday list first.
$birthday_students = get_dashboard_birthday_students($pdo);

// Load the shared teacher layout.
$page_title = 'Teacher Dashboard';
require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

<main class="dashboard-main">
    <?php require '../includes/birthday_banner.php'; ?>

    <?php if (empty($birthday_students)): ?>
    <!-- Show a simple empty state when the shared birthday banner has nothing to render. -->
    <section class="empty-dashboard-state">
        <strong>No idol birthdays today</strong>
        <p>The birthday banner will appear here automatically on each idol's birthday.</p>
    </section>
    <?php endif; ?>
</main>

<?php require_once '../includes/footer.php'; ?>
