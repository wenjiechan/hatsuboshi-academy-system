<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/messages_helpers.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

function message_reaction_response(array $payload, int $status_code = 200): void
{
    http_response_code($status_code);
    echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    message_reaction_response(['error' => 'Method not allowed'], 405);
}

$submitted_csrf = (string) ($_POST['csrf_token'] ?? '');

if (!hash_equals($_SESSION['csrf_token'] ?? '', $submitted_csrf)) {
    message_reaction_response(['error' => 'The security check could not be verified.'], 403);
}

$user_id = (int) ($_SESSION['id'] ?? 0);
$conversation_id = filter_input(INPUT_POST, 'conversation_id', FILTER_VALIDATE_INT);
$message_id = filter_input(INPUT_POST, 'message_id', FILTER_VALIDATE_INT);
$emoji = (string) ($_POST['emoji'] ?? '');

if ($user_id <= 0) {
    message_reaction_response([
        'error' => 'Unauthenticated',
        'redirect_url' => '/gakumas-sms/login.php',
    ], 401);
}

if (!$conversation_id || !$message_id) {
    message_reaction_response(['error' => 'Invalid reaction request.'], 422);
}

try {
    $reactions = set_message_reaction($pdo, (int) $message_id, (int) $conversation_id, $user_id, $emoji);

    message_reaction_response([
        'success' => true,
        'message_id' => (int) $message_id,
        'reactions' => $reactions,
    ]);
} catch (InvalidArgumentException | RuntimeException $exception) {
    message_reaction_response(['error' => $exception->getMessage()], 422);
} catch (Throwable $exception) {
    message_reaction_response(['error' => 'The reaction could not be saved.'], 500);
}
