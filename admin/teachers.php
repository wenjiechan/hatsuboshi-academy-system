<?php
require_once '../includes/auth.php';
require_role('admin');

// This is a placeholder admin page; teacher account management will be added here later.
$page_title = 'Teachers';
require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

<main class="dashboard-main">
    <!-- Empty state keeps the navigation destination usable before the full feature exists. -->
    <section class="today-schedule">
        <div class="section-heading">
            <div>
                <p class="dashboard-eyebrow">School Admin</p>
                <h2>Teachers</h2>
            </div>
        </div>

        <div class="empty-dashboard-state">
            <strong>Teacher administration coming next</strong>
            <p>This page will create teacher accounts and manage teaching profiles.</p>
        </div>
    </section>
</main>

<?php require_once '../includes/footer.php'; ?>
