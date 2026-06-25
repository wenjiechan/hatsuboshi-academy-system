<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

// Sends a JSON response and stops the script
function messages_poll_response(array $payload, int $status_code = 200): void
{
    http_response_code($status_code);
    echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Allow: GET');
    messages_poll_response(['error' => 'Method not allowed'], 405);
}
$user_id = (int) ($_SESSION['id'] ?? 0);

// CHeck login user
if ($user_id <= 0) {
    messages_poll_response([
        'error' => 'Unauthenticated',
        'redirect_url' => '/gakumas-sms/login.php',
    ], 401);
}

$conversation_id = filter_input(INPUT_GET, 'conversation_id', FILTER_VALIDATE_INT);
$after_id = filter_input(INPUT_GET, 'after_id', FILTER_VALIDATE_INT);
$edited_after = (string) ($_GET['edited_after'] ?? '1970-01-01 00:00:00');
$deleted_after = (string) ($_GET['deleted_after'] ?? '1970-01-01 00:00:00');

// Validate request
if (
    !$conversation_id ||
    $conversation_id <= 0 ||
    $after_id === false ||
    $after_id === null ||
    $after_id < 0 ||
    !preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $edited_after) ||
    !preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $deleted_after)
) {
    messages_poll_response(['error' => 'Invalid polling request'], 422);
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/messages_helpers.php';

if (!is_conversation_participant($pdo, (int) $conversation_id, $user_id)) {
    messages_poll_response(['error' => 'Conversation unavailable'], 403);
}

// Get new messages
$stmt = $pdo->prepare(
    'SELECT
        m.id,
        m.sender_id,
        m.message_type,
        m.body,
        m.related_type,
        m.related_id,
        m.created_at,
        m.edited_at,
        m.deleted_at,
        /*A message can be edited only 
        message belongs to current user, 
        message type is text
        message is not deleted
        message was sent within 15 minutes*/
        CASE
            WHEN m.sender_id = ?
             AND m.message_type = "text"
             AND m.deleted_at IS NULL
             AND m.created_at >= DATE_SUB(NOW(), INTERVAL 15 MINUTE)
            THEN 1
            ELSE 0
        END AS can_edit,
        /*A message can be deleted only 
        message belongs to current user
        message type is text
        message is not deleted*/
        CASE
            WHEN m.sender_id = ?
             AND m.message_type = "text"
             AND m.deleted_at IS NULL
            THEN 1
            ELSE 0
        END AS can_delete
     FROM messages m
     WHERE m.conversation_id = ?
       AND m.id > ?
     ORDER BY m.id ASC
     LIMIT 100'
);
$stmt->execute([$user_id, $user_id, (int) $conversation_id, (int) $after_id]);
$messages = $stmt->fetchAll();
// Get edited and deleted messages
$edited_messages = get_edited_conversation_messages(
    $pdo,
    (int) $conversation_id,
    $user_id,
    $edited_after
);
$deleted_messages = get_deleted_conversation_messages(
    $pdo,
    (int) $conversation_id,
    $user_id,
    $deleted_after
);
// Get the current database time as cursor
$poll_cursor = (string) $pdo->query('SELECT NOW()')->fetchColumn();

// Mark conversation as read
mark_conversation_read($pdo, (int) $conversation_id, $user_id);

// Loops through new messages
$next_after_id = (int) $after_id;
$response_messages = [];

// Updates the latest message ID
foreach ($messages as $message) {
    $message_id = (int) $message['id'];
    $next_after_id = max($next_after_id, $message_id);

    $response_messages[] = [
        'id' => $message_id,
        'body' => empty($message['deleted_at']) ? (string) $message['body'] : '',
        'message_type' => (string) $message['message_type'],
        'related_type' => $message['related_type'],
        'related_id' => $message['related_id'] !== null ? (int) $message['related_id'] : null,
        'created_at' => (string) $message['created_at'],
        'edited_at' => $message['edited_at'],
        'deleted_at' => $message['deleted_at'],
        'is_own' => (int) ($message['sender_id'] ?? 0) === $user_id,
        'can_edit' => (bool) $message['can_edit'],
        'can_delete' => (bool) $message['can_delete'],
    ];
}

messages_poll_response([
    'success' => true,
    'messages' => $response_messages,
    'edited_messages' => array_map(
        static fn(array $message): array => [
            'id' => (int) $message['id'],
            'body' => (string) $message['body'],
            'edited_at' => (string) $message['edited_at'],
        ],
        $edited_messages
    ),
    'deleted_messages' => array_map(
        static fn(array $message): array => [
            'id' => (int) $message['id'],
            'message_type' => (string) $message['message_type'],
            'created_at' => (string) $message['created_at'],
            'deleted_at' => (string) $message['deleted_at'],
            'is_own' => (int) ($message['sender_id'] ?? 0) === $user_id,
        ],
        $deleted_messages
    ),
    'next_after_id' => $next_after_id,
    'edited_cursor' => $poll_cursor,
    'deleted_cursor' => $poll_cursor,
]);