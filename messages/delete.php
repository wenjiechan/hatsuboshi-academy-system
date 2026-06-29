<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/messages_helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /gakumas-sms/messages/inbox.php');
    exit;
}

verify_csrf((string) ($_POST['csrf_token'] ?? ''));

$sender_id = (int) $_SESSION['id'];
$message_id = filter_input(INPUT_POST, 'message_id', FILTER_VALIDATE_INT);
$conversation_id = filter_input(INPUT_POST, 'conversation_id', FILTER_VALIDATE_INT);

// Only allow the sender to delete their own text message.
if (
    !$message_id ||
    !$conversation_id ||
    !is_conversation_participant($pdo, (int) $conversation_id, $sender_id)
) {
    redirect_to_account_issue(
        'Message unavailable',
        'You do not have permission to delete this message.',
        403
    );
}

// Soft-delete the message so the conversation history remains consistent.
try {
    delete_conversation_message(
        $pdo,
        (int) $message_id,
        (int) $conversation_id,
        $sender_id
    );
    // Store the mmessage if delete fails
} catch (RuntimeException $exception) {
    $_SESSION['message_error'] = $exception->getMessage();
}

header('Location: /gakumas-sms/messages/view.php?id=' . (int) $conversation_id);
exit;
