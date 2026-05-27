<?php
require_once '../includes/auth.php';
require_role('student');

$page_title = 'Student Dashboard';
require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

<style>
.dashboard-main {
    margin-left: 240px;
    padding: 32px;
}

@media (max-width: 991.98px) {
    .dashboard-main {
        margin-left: 0;
        padding: 24px 16px;
    }
}
</style>
<main class="dashboard-main">
    <h1>Welcome back!</h1>
    <p>Your student dashboard content goes here.</p>
</main>

<?php require_once '../includes/footer.php'; ?>
