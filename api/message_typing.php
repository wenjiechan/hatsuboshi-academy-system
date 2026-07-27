<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

function message_typing_response(array $payload, int $status_code = 200): void
{
    http_response_code($status_code);
    echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    message_typing_response(['error' => 'Method not allowed'], 405);
}

$user_id = (int) ($_SESSION['id'] ?? 0);

if ($user_id <= 0) {
    message_typing_response([
        'error' => 'Unauthenticated',
        'redirect_url' => '/gakumas-sms/login.php',
    ], 401);
}

if (!hash_equals((string) ($_SESSION['csrf_token'] ?? ''), (string) ($_POST['csrf_token'] ?? ''))) {
    message_typing_response(['error' => 'The security check could not be verified.'], 403);
}

$conversation_id = filter_input(INPUT_POST, 'conversation_id', FILTER_VALIDATE_INT);
$is_typing = (string) ($_POST['is_typing'] ?? '0') === '1';

if (!$conversation_id || $conversation_id <= 0) {
    message_typing_response(['error' => 'Invalid typing request'], 422);
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/messages_helpers.php';

try {
    set_conversation_typing_status($pdo, (int) $conversation_id, $user_id, $is_typing);
} catch (RuntimeException $exception) {
    message_typing_response(['error' => $exception->getMessage()], 403);
}

message_typing_response(['success' => true]);
