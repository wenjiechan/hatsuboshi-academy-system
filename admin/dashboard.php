<?php
require_once '../includes/auth.php';
require_role('admin');

$page_title = 'Admin Dashboard';
require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

<main class="dashboard-main">
    <section class="today-schedule">
        <div class="section-heading">
            <div>
                <p class="dashboard-eyebrow">School Admin</p>
                <h2>Admin Dashboard</h2>
            </div>
        </div>

        <div class="empty-dashboard-state">
            <strong>Admin area ready</strong>
            <p>Use this area later to manage users, students, producers, teachers, and requests.</p>
        </div>
    </section>
</main>

<?php require_once '../includes/footer.php'; ?>
