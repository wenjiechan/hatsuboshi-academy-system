<?php
$current_page = basename($_SERVER['PHP_SELF']);
$role = $_SESSION['role'] ?? 'student';

//List of sidebar navigation, with label, url. icon and page
$student_nav = [
    ['label' => 'Home', 'url' => '/gakumas-sms/student/dashboard.php', 'icon' => 'bi-house-heart', 'page' => 'dashboard.php'],
    ['label' => 'Lessons', 'url' => '/gakumas-sms/student/lessons.php', 'icon' => 'bi-journal-bookmark', 'page' => 'lessons.php'],
    ['label' => 'Schedule', 'url' => '/gakumas-sms/student/schedule.php', 'icon' => 'bi-calendar-event', 'page' => 'schedule.php'],
    ['label' => 'Profile', 'url' => '/gakumas-sms/student/profile.php', 'icon' => 'bi-person-circle', 'page' => 'profile.php'],
    ['label' => 'Song', 'url' => '/gakumas-sms/student/song.php', 'icon' => 'bi-music-note-list', 'page' => 'song.php'],
    ['label' => 'Stats', 'url' => '/gakumas-sms/student/stats.php', 'icon' => 'bi-bar-chart-line', 'page' => 'stats.php'],
    ['label' => 'Settings', 'url' => '/gakumas-sms/student/settings.php', 'icon' => 'bi-gear', 'page' => 'settings.php'],
];

$producer_nav = [
    ['label' => 'Home', 'url' => '/gakumas-sms/admin/dashboard.php', 'icon' => 'bi-house-heart', 'page' => 'dashboard.php'],
    ['label' => 'Students', 'url' => '/gakumas-sms/admin/students.php', 'icon' => 'bi-people', 'page' => 'students.php'],
    ['label' => 'Schedules', 'url' => '/gakumas-sms/admin/schedules.php', 'icon' => 'bi-calendar-event', 'page' => 'schedules.php'],
    ['label' => 'Lessons', 'url' => '/gakumas-sms/admin/lessons.php', 'icon' => 'bi-journal-bookmark', 'page' => 'lessons.php'],
    ['label' => 'Songs', 'url' => '/gakumas-sms/admin/songs.php', 'icon' => 'bi-music-note-list', 'page' => 'songs.php'],
    ['label' => 'Events', 'url' => '/gakumas-sms/admin/events.php', 'icon' => 'bi-stars', 'page' => 'events.php'],
    ['label' => 'Messages', 'url' => '/gakumas-sms/messages/inbox.php', 'icon' => 'bi-envelope', 'page' => 'inbox.php'],
    ['label' => 'Reports', 'url' => '/gakumas-sms/admin/reports.php', 'icon' => 'bi-bar-chart-line', 'page' => 'reports.php'],
    ['label' => 'Settings', 'url' => '/gakumas-sms/admin/settings.php', 'icon' => 'bi-gear', 'page' => 'settings.php'],
];

$teacher_nav = [
    ['label' => 'Home', 'url' => '/gakumas-sms/teacher/dashboard.php', 'icon' => 'bi-house-heart', 'page' => 'dashboard.php'],
    ['label' => 'Lessons', 'url' => '/gakumas-sms/teacher/lessons.php', 'icon' => 'bi-journal-bookmark', 'page' => 'lessons.php'],
    ['label' => 'Schedules', 'url' => '/gakumas-sms/teacher/schedules.php', 'icon' => 'bi-calendar-event', 'page' => 'schedules.php'],
    ['label' => 'Students', 'url' => '/gakumas-sms/teacher/students.php', 'icon' => 'bi-people', 'page' => 'students.php'],
    ['label' => 'Messages', 'url' => '/gakumas-sms/messages/inbox.php', 'icon' => 'bi-envelope', 'page' => 'inbox.php'],
    ['label' => 'Settings', 'url' => '/gakumas-sms/teacher/settings.php', 'icon' => 'bi-gear', 'page' => 'settings.php'],
];

$panel_label = match ($role) {
    'producer' => 'Producer Panel',
    'teacher' => 'Teacher Panel',
    default => 'Student Panel',
};

$nav_items = match ($role) {
    'producer' => $producer_nav,
    'teacher' => $teacher_nav,
    default => $student_nav,
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

    <!--Sidebar navigation-->
    <nav class="sidebar-nav">
        <?php foreach ($nav_items as $item): ?>
        <a href="<?= htmlspecialchars($item['url']) ?>"
            class="sidebar-link <?= $current_page === $item['page'] ? 'active' : '' ?>">
            <i class="bi <?= htmlspecialchars($item['icon'], ENT_QUOTES, 'UTF-8') ?>"></i>
            <span><?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?></span>
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
