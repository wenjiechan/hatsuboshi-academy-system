<?php
require_once 'includes/auth.php';

//Read the error form the session
$issue = $_SESSION['account_issue'] ?? null;
unset($_SESSION['account_issue']);

// Create a default error if none exists
if (!is_array($issue)) {
    $issue = [
        'title' => 'Account needs attention',
        'message' => 'We could not finish loading your account page. Please log out and contact the school office if this keeps happening.',
        'status_code' => 400,
    ];
}

// Able the page return the correct error type by sets the HTTP status code
$status_code = (int) ($issue['status_code'] ?? 400);
if ($status_code < 400 || $status_code > 599) {
    $status_code = 400;
}

http_response_code($status_code);

$page_title = $issue['title'] ?? 'Account needs attention';

// Use the theme colors saved in the session
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/gakumas-sms/assets/css/theme.css">
    <link rel="stylesheet" href="/gakumas-sms/assets/css/components.css">
</head>

<body class="account-issue-page" style="<?= $body_style ?>">
    <main class="account-issue-main">
        <section class="account-issue-panel" aria-labelledby="accountIssueTitle">
            <div class="account-issue-icon" aria-hidden="true">
                <i class="bi bi-person-exclamation"></i>
            </div>

            <p class="account-issue-label">Account Status</p>
            <h1 id="accountIssueTitle">
                <?= htmlspecialchars($issue['title'], ENT_QUOTES, 'UTF-8') ?>
            </h1>
            <p>
                <?= htmlspecialchars($issue['message'], ENT_QUOTES, 'UTF-8') ?>
            </p>

            <div class="account-issue-actions">
                <a href="/gakumas-sms/logout.php" class="btn btn-primary">
                    <i class="bi bi-box-arrow-right"></i>
                    Logout
                </a>
            </div>
        </section>
    </main>
</body>

</html>
