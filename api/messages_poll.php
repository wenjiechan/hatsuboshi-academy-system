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
$visible_message_ids = array_values(array_filter(array_map(
    'intval',
    explode(',', (string) ($_GET['visible_message_ids'] ?? ''))
)));
$visible_message_ids = array_slice(array_values(array_unique($visible_message_ids)), 0, 200);

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

ensure_message_pin_schema($pdo);
ensure_message_presence_schema($pdo);
ensure_message_reply_schema($pdo);
ensure_message_clear_schema($pdo);
ensure_message_reaction_schema($pdo);
ensure_message_attachment_schema($pdo);
ensure_message_sticker_schema($pdo);

$conversation_type_stmt = $pdo->prepare(
    'SELECT conversation_type
     FROM conversations
     WHERE id = ?
     LIMIT 1'
);
$conversation_type_stmt->execute([(int) $conversation_id]);
$conversation_type = (string) $conversation_type_stmt->fetchColumn();

// Get messages newer than the last message ID received by the browser.
$stmt = $pdo->prepare(
    'SELECT
        m.id,
        m.sender_id,
        m.message_type,
        m.body,
        m.related_type,
        m.related_id,
        m.sticker_key,
        m.created_at,
        m.edited_at,
        m.deleted_at,
        m.pinned_at,
        m.pinned_by,
        m.reply_to_message_id,
        m.forwarded_from_label,
        m.forwarded_from_message_id,
        reply.body AS reply_body,
        reply.message_type AS reply_message_type,
        reply.sticker_key AS reply_sticker_key,
        reply.deleted_at AS reply_deleted_at,
        (
            SELECT reply_attachment.original_name
            FROM message_attachments reply_attachment
            WHERE reply_attachment.message_id = reply.id
            ORDER BY reply_attachment.id ASC
            LIMIT 1
        ) AS reply_attachment_name,
        COALESCE(reply_student.name, reply_teacher.name, reply_user.username) AS reply_sender_display_name,
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
             AND TRIM(m.body) <> ""
             AND m.created_at >= DATE_SUB(NOW(), INTERVAL 15 MINUTE)
            THEN 1
            ELSE 0
        END AS can_edit,
        /*A message can be deleted only 
        message belongs to current user
        message type is text or sticker
        message is not deleted*/
        CASE
            WHEN m.sender_id = ?
             AND m.message_type IN ("text", "sticker")
             AND m.deleted_at IS NULL
            THEN 1
            ELSE 0
        END AS can_delete,
        CASE
            WHEN m.deleted_at IS NULL THEN 1
            ELSE 0
        END AS can_pin,
        COALESCE(sender_student.name, sender_teacher.name, sender.username) AS sender_display_name
     FROM messages m
     INNER JOIN conversation_participants current_participant
        ON current_participant.conversation_id = m.conversation_id
       AND current_participant.user_id = ?
       AND current_participant.deleted_at IS NULL
     INNER JOIN conversations c
        ON c.id = m.conversation_id
     LEFT JOIN users sender ON sender.id = m.sender_id
     LEFT JOIN students sender_student ON sender_student.user_id = sender.id
     LEFT JOIN teachers sender_teacher ON sender_teacher.user_id = sender.id
     LEFT JOIN messages reply
        ON reply.id = m.reply_to_message_id
       AND reply.conversation_id = m.conversation_id
     LEFT JOIN users reply_user ON reply_user.id = reply.sender_id
     LEFT JOIN students reply_student ON reply_student.user_id = reply_user.id
     LEFT JOIN teachers reply_teacher ON reply_teacher.user_id = reply_user.id
     LEFT JOIN producer_student_requests request
        ON request.id = m.related_id
       AND m.related_type = "producer_student_request"
     WHERE m.conversation_id = ?
       AND m.id > ?
       AND (
           c.conversation_type <> "group"
           OR m.created_at >= current_participant.joined_at
       )
       -- Polling should only return messages still visible after this participant clear point.
       AND (
           current_participant.cleared_at IS NULL
           OR m.created_at > current_participant.cleared_at
       )
     ORDER BY m.id ASC
     LIMIT 100'
);
$stmt->execute([$user_id, $user_id, $user_id, (int) $conversation_id, (int) $after_id]);
$messages = hydrate_message_attachments($pdo, $stmt->fetchAll());
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

$read_receipts = $conversation_type === 'group'
    ? get_group_message_read_receipts($pdo, (int) $conversation_id, $user_id)
    : ($conversation_type === 'direct'
        ? get_direct_message_read_receipts($pdo, (int) $conversation_id, $user_id)
        : []);
$typing_users = get_conversation_typing_users($pdo, (int) $conversation_id, $user_id);
$new_message_ids = array_map(static fn(array $message): int => (int) $message['id'], $messages);
$reaction_message_ids = array_values(array_unique(array_merge($visible_message_ids, $new_message_ids)));
$reaction_summaries = get_message_reaction_summaries(
    $pdo,
    (int) $conversation_id,
    $user_id,
    $reaction_message_ids
);

// Use the current database time as the next edit/delete polling cursor.
$next_after_id = (int) $after_id;
$response_messages = [];

// Mark the conversation as read after the user receives the latest updates.
foreach ($messages as $message) {
    $message_id = (int) $message['id'];
    $next_after_id = max($next_after_id, $message_id);
    $reply_body = trim((string) ($message['reply_body'] ?? ''));

    if (($message['reply_message_type'] ?? '') === MESSAGE_TYPE_STICKER) {
        $reply_body = message_sticker_preview_label((string) ($message['reply_sticker_key'] ?? ''));
    } elseif ($reply_body === '' && !empty($message['reply_attachment_name'])) {
        $reply_body = 'Attachment: ' . (string) $message['reply_attachment_name'];
    }

    $response_messages[] = [
        'id' => $message_id,
        'body' => empty($message['deleted_at']) ? (string) $message['body'] : '',
        'message_type' => (string) $message['message_type'],
        // Send the resolved asset data so newly polled stickers render immediately.
        'sticker_key' => $message['sticker_key'] !== null ? (string) $message['sticker_key'] : '',
        'sticker' => empty($message['deleted_at']) ? message_sticker_public_data($message['sticker_key'] ?? null) : null,
        'related_type' => $message['related_type'],
        'related_id' => $message['related_id'] !== null ? (int) $message['related_id'] : null,
        'request_id' => $message['request_id'] !== null ? (int) $message['request_id'] : null,
        'request_type' => $message['request_type'],
        'request_status' => $message['request_status'],
        'created_at' => (string) $message['created_at'],
        'edited_at' => $message['edited_at'],
        'deleted_at' => $message['deleted_at'],
        'pinned_at' => $message['pinned_at'],
        'pinned_by' => $message['pinned_by'] !== null ? (int) $message['pinned_by'] : null,
        'reply_to_message_id' => $message['reply_to_message_id'] !== null ? (int) $message['reply_to_message_id'] : null,
        'forwarded_from_label' => $message['forwarded_from_label'] !== null ? (string) $message['forwarded_from_label'] : '',
        'forwarded_from_message_id' => $message['forwarded_from_message_id'] !== null ? (int) $message['forwarded_from_message_id'] : null,
        'reply_preview' => $message['reply_to_message_id'] !== null ? [
            'message_id' => (int) $message['reply_to_message_id'],
            'sender_display_name' => (string) ($message['reply_sender_display_name'] ?? 'Someone'),
            'body' => empty($message['reply_deleted_at'])
                ? $reply_body
                : 'This message was deleted.',
            'is_deleted' => !empty($message['reply_deleted_at']),
            'message_type' => (string) ($message['reply_message_type'] ?? MESSAGE_TYPE_TEXT),
            'sticker_key' => (string) ($message['reply_sticker_key'] ?? ''),
        ] : null,
        'sender_id' => (int) ($message['sender_id'] ?? 0),
        'is_own' => (int) ($message['sender_id'] ?? 0) === $user_id,
        'sender_display_name' => $message['sender_display_name'],
        'can_edit' => (bool) $message['can_edit'],
        'can_delete' => (bool) $message['can_delete'],
        'can_pin' => (bool) $message['can_pin'],
        'can_reply' => empty($message['deleted_at'])
            && (string) $message['message_type'] !== MESSAGE_TYPE_SYSTEM,
        'can_forward' => empty($message['deleted_at'])
            && (string) $message['message_type'] !== MESSAGE_TYPE_SYSTEM,
        'attachments' => $message['attachments'] ?? [],
        'reactions' => $reaction_summaries[$message_id] ?? [],
        'read_receipt' => $read_receipts[$message_id] ?? [
            'message_id' => $message_id,
            'read_count' => 0,
            'read_names' => '',
            'read_users' => [],
            'is_read' => false,
            'read_at' => '',
        ],
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
            'attachments' => $message['attachments'] ?? [],
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
    'read_receipts' => array_values($read_receipts),
    'reaction_summaries' => array_map(
        static fn(int $message_id): array => [
            'message_id' => $message_id,
            'reactions' => $reaction_summaries[$message_id] ?? [],
        ],
        $reaction_message_ids
    ),
    'typing_users' => array_map(
        static fn(array $typing_user): array => [
            'user_id' => (int) $typing_user['user_id'],
            'display_name' => (string) $typing_user['display_name'],
        ],
        $typing_users
    ),
    'next_after_id' => $next_after_id,
    'edited_cursor' => $poll_cursor,
    'deleted_cursor' => $poll_cursor,
]);
