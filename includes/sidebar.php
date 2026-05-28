<?php
$current_page = basename($_SERVER['PHP_SELF']);
$role = $_SESSION['role'] ?? 'student';

$student_nav = [
    ['label' => 'Home', 'url' => '/gakumas-sms/student/dashboard.php', 'icon' => 'bi-house-heart', 'page' => 'dashboard.php'],
    ['label' => 'Lessons', 'url' => '/gakumas-sms/student/lessons.php', 'icon' => 'bi-journal-bookmark', 'page' => 'lessons.php'],
    ['label' => 'Schedule', 'url' => '/gakumas-sms/student/schedule.php', 'icon' => 'bi-calendar-event', 'page' => 'schedule.php'],
    ['label' => 'Profile', 'url' => '/gakumas-sms/student/profile.php', 'icon' => 'bi-person-circle', 'page' => 'profile.php'],
    ['label' => 'Stats', 'url' => '/gakumas-sms/student/stats.php', 'icon' => 'bi-bar-chart-line', 'page' => 'stats.php'],
    ['label' => 'Settings', 'url' => '/gakumas-sms/student/settings.php', 'icon' => 'bi-gear', 'page' => 'settings.php'],
];

$panel_label = match ($role) {
    'producer' => 'Producer Panel',
    'teacher' => 'Teacher Panel',
    default => 'Student Panel',
};
?>

<aside class="app-sidebar offcanvas-xl offcanvas-start" tabindex="-1" id="mobileSidebar"
    aria-labelledby="mobileSidebarLabel">
    <div class="sidebar-brand">
        <img src="/gakumas-sms/assets/images/logo_small.png" alt="Hatsuboshi logo" class="sidebar-logo">
        <div>
            <strong id="mobileSidebarLabel">Hatsuboshi</strong>
            <span><?= htmlspecialchars($panel_label, ENT_QUOTES, 'UTF-8') ?></span>
        </div>
        <button type="button" class="btn-close ms-auto d-xl-none" data-bs-dismiss="offcanvas"
            data-bs-target="#mobileSidebar" aria-label="Close navigation"></button>
    </div>

    <nav class="sidebar-nav">
        <?php foreach ($student_nav as $item): ?>
        <a href="<?= htmlspecialchars($item['url']) ?>"
            class="sidebar-link <?= $current_page === $item['page'] ? 'active' : '' ?>">
            <i class="bi <?= htmlspecialchars($item['icon']) ?>"></i>
            <span><?= htmlspecialchars($item['label']) ?></span>
        </a>
        <?php endforeach; ?>
    </nav>

    <div class="sidebar-footer">
        <a href="/gakumas-sms/logout.php" class="sidebar-link logout">
            <i class="bi bi-box-arrow-right"></i>
            <span>Logout</span>
        </a>
    </div>
</aside>
