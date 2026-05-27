<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$page_title = $page_title ?? 'Hatsuboshi Gakuen';
$body_class = $body_class ?? '';

$student_theme = $_SESSION['theme_class'] ?? '';
$full_body_class = trim($body_class . ' ' . $student_theme);

$theme_primary = $_SESSION['theme_primary_color'] ?? '#FF6B9D';
$theme_secondary = $_SESSION['theme_secondary_color'] ?? '#FFB3D1';

$body_style = sprintf(
    '--primary: %s; --secondary: %s;',
    htmlspecialchars($theme_primary, ENT_QUOTES, 'UTF-8'),
    htmlspecialchars($theme_secondary, ENT_QUOTES, 'UTF-8')
);
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" defer></script>
</head>

<body class="<?= htmlspecialchars($full_body_class, ENT_QUOTES, 'UTF-8') ?>" style="<?= $body_style ?>">

    <header class="mobile-topbar d-lg-none">
        <button class="btn sidebar-toggle" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar"
            aria-controls="mobileSidebar" aria-label="Open navigation">
            <i class="bi bi-list"></i>
        </button>

        <a href="/gakumas-sms/student/dashboard.php" class="mobile-brand">
            Hatsuboshi
        </a>

        <a href="/gakumas-sms/messages/inbox.php" class="mobile-icon-link" aria-label="Messages">
            <i class="bi bi-envelope"></i>
        </a>
    </header>