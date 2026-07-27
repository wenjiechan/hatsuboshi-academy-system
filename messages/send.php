<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/messages_helpers.php';
require_once __DIR__ . '/../includes/notifications_helpers.php';

// Detect whether Javascript excepts JSON
$expects_json = str_contains(
    strtolower((string) ($_SERVER['HTTP_ACCEPT'] ?? '')),
    'application/json'
);

// Returns JSON and stops the script
function send_message_response(array $payload, int $status_code = 200): void
{
    http_response_code($status_code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // If it is an AJAX request, returns JSON error
    if ($expects_json) {
        header('Allow: POST');
        send_message_response(['error' => 'Method not allowed'], 405);
    }

    header('Location: /gakumas-sms/messages/inbox.php');
    exit;
}

// Compare the submitted CSRF token with the token stored in the session.
$submitted_csrf = (string) ($_POST['csrf_token'] ?? '');

if (!hash_equals($_SESSION['csrf_token'] ?? '', $submitted_csrf)) {
    // Return a JSON error response for AJAX requests.
    if ($expects_json) {
        send_message_response(['error' => 'The security check could not be verified.'], 403);
    }

    verify_csrf($submitted_csrf);
}

$sender_id = (int) $_SESSION['id'];
$conversation_id = filter_input(INPUT_POST, 'conversation_id', FILTER_VALIDATE_INT);
$body = trim((string) ($_POST['body'] ?? ''));

// Check whether the user actually belongs to the conversation
if (!$conversation_id || !is_conversation_participant($pdo, (int) $conversation_id, $sender_id)) {
    //Check the AJAX request
    if ($expects_json) {
        send_message_response([
            'error' => 'You do not have permission to send a message to this conversation.',
        ], 403);
    }

    redirect_to_account_issue(
        'Conversation unavailable',
        'You do not have permission to send a message to this conversation.',
        403,
        '/gakumas-sms/messages/inbox.php',
        'Back to Inbox',
        false
    );
}

$conversation = get_conversation_details($pdo, (int) $conversation_id, $sender_id);

// Block replies to system conversations because they are read-only.
if (!$conversation || $conversation['conversation_type'] === 'system') {
    //Check the AJAX request
    if ($expects_json) {
        send_message_response(['error' => 'System messages cannot be replied to.'], 403);
    }

    $_SESSION['message_error'] = 'System messages cannot be replied to.';
    header('Location: /gakumas-sms/messages/view.php?id=' . (int) $conversation_id);
    exit;
}

// Send the message after all permission and validation checks pass.
try {
    $message_id = send_conversation_message(
        $pdo,
        (int) $conversation_id,
        $sender_id,
        $body
    );

    $sender_name = message_user_display_name($pdo, $sender_id);
    $is_group_conversation = ($conversation['conversation_type'] ?? '') === 'group';
    $group_name = $is_group_conversation
        ? group_conversation_name($pdo, (int) $conversation_id)
        : '';
    $mention_targets = $is_group_conversation
        ? get_group_message_mention_targets($pdo, (int) $conversation_id, $sender_id, $body)
        : [
            'everyone' => false,
            'user_ids' => [],
        ];
    $mentioned_user_ids = array_map('intval', $mention_targets['user_ids']);
    $mentions_everyone = !empty($mention_targets['everyone']);

    // Notify each recipient who has not muted this conversation.
    foreach (get_conversation_notification_recipient_ids($pdo, (int) $conversation_id, $sender_id) as $recipient_id) {
        $is_mentioned = $is_group_conversation && (
            $mentions_everyone || in_array((int) $recipient_id, $mentioned_user_ids, true)
        );

        create_notification(
            $pdo,
            $recipient_id,
            NOTIFICATION_TYPE_NEW_MESSAGE,
            $is_mentioned
                ? ($mentions_everyone ? 'Everyone mentioned in ' . $group_name : 'Mentioned in ' . $group_name)
                : ($is_group_conversation ? 'New message in ' . $group_name : 'New message'),
            $is_mentioned
                ? ($mentions_everyone
                    ? $sender_name . ' mentioned everyone in ' . $group_name . '.'
                    : $sender_name . ' mentioned you in ' . $group_name . '.')
                : ($is_group_conversation
                    ? $sender_name . ' sent a message in ' . $group_name . '.'
                    : $sender_name . ' sent you a message.'),
            'message',
            $message_id,
            '/gakumas-sms/messages/view.php?id=' . (int) $conversation_id,
            ($is_mentioned ? 'mention:' : 'new_message:') . $message_id . ':' . $recipient_id
        );
    }

    // Return JSON for AJAX request
    if ($expects_json) {
        ensure_message_pin_schema($pdo);
        ensure_message_presence_schema($pdo);

        $message_stmt = $pdo->prepare(
            'SELECT id, body, message_type, created_at, edited_at, deleted_at, pinned_at, pinned_by
             FROM messages
             WHERE id = ?
               AND conversation_id = ?
             LIMIT 1'
        );
        $message_stmt->execute([$message_id, (int) $conversation_id]);
        $message = $message_stmt->fetch();
        $read_receipt = $is_group_conversation
            ? group_message_read_receipt_for_message($pdo, (int) $message_id, (int) $conversation_id, $sender_id)
            : [
                'message_id' => (int) $message_id,
                'read_count' => 0,
                'read_names' => '',
                'read_users' => [],
            ];

        send_message_response([
            'success' => true,
            'message' => [
                'id' => (int) $message['id'],
                'body' => (string) $message['body'],
                'message_type' => (string) $message['message_type'],
                'created_at' => (string) $message['created_at'],
                'edited_at' => $message['edited_at'],
                'deleted_at' => $message['deleted_at'],
                'pinned_at' => $message['pinned_at'],
                'pinned_by' => $message['pinned_by'] !== null ? (int) $message['pinned_by'] : null,
                'is_own' => true,
                'can_edit' => true,
                'can_delete' => true,
                'can_pin' => true,
                'read_receipt' => $read_receipt,
            ],
        ]);
    }
} catch (InvalidArgumentException | RuntimeException $exception) {
    // Only runs for normal non-AJAX form submissions
    if ($expects_json) {
        send_message_response(['error' => $exception->getMessage()], 422);
    }

    $_SESSION['message_error'] = $exception->getMessage();
}

header('Location: /gakumas-sms/messages/view.php?id=' . (int) $conversation_id);
exit;
