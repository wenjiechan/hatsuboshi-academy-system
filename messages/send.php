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

// Compares the submittes CSRF token with the session token
$submitted_csrf = (string) ($_POST['csrf_token'] ?? '');

if (!hash_equals($_SESSION['csrf_token'] ?? '', $submitted_csrf)) {
    //Check the AJAX request
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
        403
    );
}

$conversation = get_conversation_details($pdo, (int) $conversation_id, $sender_id);

// Blocks replies to system conversations
if (!$conversation || $conversation['conversation_type'] === 'system') {
    //Check the AJAX request
    if ($expects_json) {
        send_message_response(['error' => 'System messages cannot be replied to.'], 403);
    }

    $_SESSION['message_error'] = 'System messages cannot be replied to.';
    header('Location: /gakumas-sms/messages/view.php?id=' . (int) $conversation_id);
    exit;
}

// If everything valid, it sends the message
try {
    $message_id = send_conversation_message(
        $pdo,
        (int) $conversation_id,
        $sender_id,
        $body
    );

    $sender = get_message_user($pdo, $sender_id);
    $sender_name = $sender['display_name'] ?? $_SESSION['user_name'] ?? 'Someone';

    // Create a notification for each recipient
    foreach (get_conversation_notification_recipient_ids($pdo, (int) $conversation_id, $sender_id) as $recipient_id) {
        create_notification(
            $pdo,
            $recipient_id,
            NOTIFICATION_TYPE_NEW_MESSAGE,
            'New message',
            $sender_name . ' sent you a message.',
            'message',
            $message_id,
            '/gakumas-sms/messages/view.php?id=' . (int) $conversation_id,
            'new_message:' . $message_id . ':' . $recipient_id
        );
    }

    // Return JSON for AJAX request
    if ($expects_json) {
        $message_stmt = $pdo->prepare(
            'SELECT id, body, message_type, created_at, edited_at, deleted_at
             FROM messages
             WHERE id = ?
               AND conversation_id = ?
             LIMIT 1'
        );
        $message_stmt->execute([$message_id, (int) $conversation_id]);
        $message = $message_stmt->fetch();

        send_message_response([
            'success' => true,
            'message' => [
                'id' => (int) $message['id'],
                'body' => (string) $message['body'],
                'message_type' => (string) $message['message_type'],
                'created_at' => (string) $message['created_at'],
                'edited_at' => $message['edited_at'],
                'deleted_at' => $message['deleted_at'],
                'is_own' => true,
                'can_edit' => true,
                'can_delete' => true,
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
