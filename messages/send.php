<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/messages_helpers.php';
require_once __DIR__ . '/../includes/notifications_helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /gakumas-sms/messages/inbox.php');
    exit;
}

verify_csrf((string) ($_POST['csrf_token'] ?? ''));

$sender_id = (int) $_SESSION['id'];
$conversation_id = filter_input(INPUT_POST, 'conversation_id', FILTER_VALIDATE_INT);
$body = trim((string) ($_POST['body'] ?? ''));

// Check whether the user actually belongs to the conversation
if (!$conversation_id || !is_conversation_participant($pdo, (int) $conversation_id, $sender_id)) {
    redirect_to_account_issue(
        'Conversation unavailable',
        'You do not have permission to send a message to this conversation.',
        403
    );
}

$conversation = get_conversation_details($pdo, (int) $conversation_id, $sender_id);

// Blocks replies to system conversations
if (!$conversation || $conversation['conversation_type'] === 'system') {
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
    foreach (get_conversation_recipient_ids($pdo, (int) $conversation_id, $sender_id) as $recipient_id) {
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
} catch (InvalidArgumentException | RuntimeException $exception) {
    $_SESSION['message_error'] = $exception->getMessage();
}

header('Location: /gakumas-sms/messages/view.php?id=' . (int) $conversation_id);
exit;
