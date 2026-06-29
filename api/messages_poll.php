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
$user_role = (string) ($_SESSION['role'] ?? '');

// Update the producer-student request and create the response message.
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

// Check that the user is logged in before polling messages.
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

// Get messages newer than the last message ID received by the browser.
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
        request.id AS request_id,
        request.request_type AS request_type,
        request.status AS request_status,
        request.producer_id AS request_producer_id,
        request.student_id AS request_student_id,
/*
 * A message can be edited only when:
 * - it belongs to the current user
 * - it is a text message
 * - it is not deleted
 * - it was sent within the last 15 minutes
 */
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
     LEFT JOIN producer_student_requests request
        ON request.id = m.related_id
       AND m.related_type = "producer_student_request"
     WHERE m.conversation_id = ?
       AND m.id > ?
     ORDER BY m.id ASC
     LIMIT 100'
);
$stmt->execute([$user_id, $user_id, (int) $conversation_id, (int) $after_id]);
$messages = $stmt->fetchAll();
// Get messages that were edited or deleted after the browser's last polling cursor.
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

$request_status_stmt = $pdo->prepare(
    'SELECT
        request.id,
        request.status
     FROM messages m
     INNER JOIN producer_student_requests request
        ON request.id = m.related_id
       AND m.related_type = "producer_student_request"
     WHERE m.conversation_id = ?
       AND m.message_type IN ("producer_add_request", "producer_remove_request")'
);
$request_status_stmt->execute([(int) $conversation_id]);
$request_statuses = $request_status_stmt->fetchAll();

// Use the current database time as the next edit/delete polling cursor.
$poll_cursor = (string) $pdo->query('SELECT NOW()')->fetchColumn();

// Use the current database time as the next edit/delete polling cursor.
mark_conversation_read($pdo, (int) $conversation_id, $user_id);

// Use the current database time as the next edit/delete polling cursor.
$next_after_id = (int) $after_id;
$response_messages = [];

// Mark the conversation as read after the user receives the latest updates.
foreach ($messages as $message) {
    $message_id = (int) $message['id'];
    $next_after_id = max($next_after_id, $message_id);

    $response_messages[] = [
        'id' => $message_id,
        'body' => empty($message['deleted_at']) ? (string) $message['body'] : '',
        'message_type' => (string) $message['message_type'],
        'related_type' => $message['related_type'],
        'related_id' => $message['related_id'] !== null ? (int) $message['related_id'] : null,
        'request_id' => $message['request_id'] !== null ? (int) $message['request_id'] : null,
        'request_type' => $message['request_type'],
        'request_status' => $message['request_status'],
        'created_at' => (string) $message['created_at'],
        'edited_at' => $message['edited_at'],
        'deleted_at' => $message['deleted_at'],
        'is_own' => (int) ($message['sender_id'] ?? 0) === $user_id,
        'can_edit' => (bool) $message['can_edit'],
        'can_delete' => (bool) $message['can_delete'],
        'can_respond_request' => $user_role === 'student'
            && (int) ($message['sender_id'] ?? 0) !== $user_id
            && in_array($message['message_type'], [
                MESSAGE_TYPE_PRODUCER_ADD_REQUEST,
                MESSAGE_TYPE_PRODUCER_REMOVE_REQUEST,
            ], true)
            && $message['related_type'] === 'producer_student_request'
            && in_array($message['request_type'], ['add', 'remove'], true)
            && $message['request_status'] === 'pending',
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
    'request_statuses' => array_map(
        static fn(array $request): array => [
            'request_id' => (int) $request['id'],
            'request_status' => (string) $request['status'],
        ],
        $request_statuses
    ),
    'next_after_id' => $next_after_id,
    'edited_cursor' => $poll_cursor,
    'deleted_cursor' => $poll_cursor,
]);
