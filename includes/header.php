<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('Asia/Kuala_Lumpur');

//Set title and body class
$page_title = $page_title ?? 'Hatsuboshi Gakuen';
$body_class = $body_class ?? '';

//Apply student theme class
$student_theme = $_SESSION['theme_class'] ?? '';
$full_body_class = trim($body_class . ' ' . $student_theme);

//Set theme colors
$theme_primary = $_SESSION['theme_primary_color'] ?? '#FF6B9D';
$theme_secondary = $_SESSION['theme_secondary_color'] ?? '#FFB3D1';

$body_style = sprintf(
    '--primary: %s; --secondary: %s;',
    htmlspecialchars($theme_primary, ENT_QUOTES, 'UTF-8'),
    htmlspecialchars($theme_secondary, ENT_QUOTES, 'UTF-8')
);

//Get user information from session and create avatar path
$role = $_SESSION['role'] ?? 'student';
$username = $_SESSION['user_name'] ?? 'Guest';
$student_name = $_SESSION['student_name'] ?? $username;
$avatar_path = '/gakumas-sms/assets/images/avatars/idols/' . rawurlencode($student_name) . '.png';

// Choose home URL based on role
$home_url = match ($role) {
    'producer' => '/gakumas-sms/admin/dashboard.php',
    'teacher' => '/gakumas-sms/teacher/dashboard.php',
    default => '/gakumas-sms/student/dashboard.php',
};

//Choose role label
$role_label = match ($role) {
    'producer' => 'Producer',
    'teacher' => 'Teacher',
    default => 'Student',
};
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8') ?></title>

    <link rel="icon" href="/gakumas-sms/assets/images/favicon.ico">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <!-- App Styles -->
    <link rel="stylesheet" href="/gakumas-sms/assets/css/theme.css">
    <link rel="stylesheet" href="/gakumas-sms/assets/css/components.css">
    <link rel="stylesheet" href="/gakumas-sms/assets/css/pages/dashboard.css">
    <?php foreach (($page_styles ?? []) as $style_href): ?>
        <link rel="stylesheet" href="<?= htmlspecialchars($style_href, ENT_QUOTES, 'UTF-8') ?>">
    <?php endforeach; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" defer></script>
    <script src="/gakumas-sms/assets/js/click-sparkle.js" defer></script>
</head>

<body class="<?= htmlspecialchars($full_body_class, ENT_QUOTES, 'UTF-8') ?>" style="<?= $body_style ?>">

    <header class="mobile-topbar d-xl-none">
        <button class="btn sidebar-toggle" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar"
            aria-controls="mobileSidebar" aria-label="Open navigation">
            <i class="bi bi-list"></i>
        </button>

        <a href="<?= htmlspecialchars($home_url, ENT_QUOTES, 'UTF-8') ?>" class="mobile-brand">
            Hatsuboshi
        </a>

        <a href="/gakumas-sms/messages/inbox.php" class="mobile-icon-link" aria-label="Messages">
            <i class="bi bi-envelope"></i>
        </a>

        <a href="/gakumas-sms/notifications.php" class="mobile-icon-link" aria-label="Notifications">
            <i class="bi bi-bell"></i>
        </a>

        <div class="topbar-user">
            <img src="<?= htmlspecialchars($avatar_path, ENT_QUOTES, 'UTF-8') ?>"
                alt="<?= htmlspecialchars($student_name, ENT_QUOTES, 'UTF-8') ?>" class="topbar-avatar">
            <div class="topbar-user-text">
                <span class="topbar-username">
                    <?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?>
                </span>
                <span class="role-badge">
                    <?= htmlspecialchars($role_label, ENT_QUOTES, 'UTF-8') ?>
                </span>
            </div>
        </div>
    </header>

    <header class="app-topbar d-none d-xl-flex">
        <div>
            <a href="<?= htmlspecialchars($home_url, ENT_QUOTES, 'UTF-8') ?>" class="app-topbar-brand">
                Hatsuboshi
            </a>
            <h1><?= htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8') ?></h1>
        </div>

        <div class="app-topbar-actions">
            <a href="/gakumas-sms/messages/inbox.php" class="topbar-icon-link" aria-label="Messages">
                <i class="bi bi-envelope"></i>
            </a>

            <a href="/gakumas-sms/notifications.php" class="topbar-icon-link" aria-label="Notifications">
                <i class="bi bi-bell"></i>
            </a>

            <div class="topbar-user">
                <img src="<?= htmlspecialchars($avatar_path, ENT_QUOTES, 'UTF-8') ?>"
                    alt="<?= htmlspecialchars($student_name, ENT_QUOTES, 'UTF-8') ?>" class="topbar-avatar">
                <div class="topbar-user-text">
                    <span class="topbar-username">
                        <?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?>
                    </span>
                    <span class="role-badge">
                        <?= htmlspecialchars($role_label, ENT_QUOTES, 'UTF-8') ?>
                    </span>
                </div>
            </div>

        </div>
    </header>
