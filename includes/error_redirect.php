<?php

// Stores the error details
function redirect_to_account_issue(
    string $title,
    string $message,
    int $status_code = 400,
    ?string $action_url = null,
    ?string $action_label = null,
    bool $show_logout = true
): void {
    $_SESSION['account_issue'] = [
        'title' => $title,
        'message' => $message,
        'status_code' => $status_code,
        'action_url' => $action_url,
        'action_label' => $action_label,
        'show_logout' => $show_logout,
    ];

    header('Location: /gakumas-sms/account_issue.php');
    exit();
}
