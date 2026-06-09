<?php

// Stores the error details
function redirect_to_account_issue(
    string $title,
    string $message,
    int $status_code = 400
): void {
    $_SESSION['account_issue'] = [
        'title' => $title,
        'message' => $message,
        'status_code' => $status_code,
    ];

    header('Location: /gakumas-sms/account_issue.php');
    exit();
}
