<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

$user_id = (int) ($_SESSION['id'] ?? 0);

if ($user_id <= 0) {
    $_SESSION['account_issue'] = [
        'title' => 'Session expired',
        'message' => 'Your login session has expired. Please log in again to continue receiving notifications.',
        'status_code' => 401,
    ];

    http_response_code(401);
    echo json_encode([
        'error' => 'Unauthenticated',
        'redirect_url' => '/gakumas-sms/account_issue.php',
    ]);
    exit;
}

require_once '../config/database.php';
require_once '../includes/notifications_helpers.php';

$count = get_unread_notification_count($pdo, $user_id);

ensure_notifications_table_columns($pdo);

$stmt = $pdo->prepare(
    'SELECT id, type, title, body, action_url, created_at
     FROM notifications
     WHERE user_id = ?
       AND is_read = 0
     ORDER BY created_at DESC
     LIMIT 5'
);
$stmt->execute([$user_id]);

echo json_encode([
    'unread_count' => $count,
    'notifications' => $stmt->fetchAll(),
]);
