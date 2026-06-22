<?php
require_once '../includes/auth.php';
require_role('admin');

$page_title = 'Users';
require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

<main class="dashboard-main">
    <section class="today-schedule">
        <div class="section-heading">
            <div>
                <p class="dashboard-eyebrow">School Admin</p>
                <h2>Users</h2>
            </div>
        </div>

        <div class="empty-dashboard-state">
            <strong>User management coming next</strong>
            <p>This page will create and manage admin, producer, teacher, and student accounts.</p>
        </div>
    </section>
</main>

<?php require_once '../includes/footer.php'; ?>
