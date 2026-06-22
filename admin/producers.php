<?php
require_once '../includes/auth.php';
require_role('admin');

$page_title = 'Producers';
require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

<main class="dashboard-main">
    <section class="today-schedule">
        <div class="section-heading">
            <div>
                <p class="dashboard-eyebrow">School Admin</p>
                <h2>Producers</h2>
            </div>
        </div>

        <div class="empty-dashboard-state">
            <strong>Producer administration coming next</strong>
            <p>This page will create producer accounts and review their student rosters.</p>
        </div>
    </section>
</main>

<?php require_once '../includes/footer.php'; ?>
