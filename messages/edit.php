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
$body = (string) ($_POST['body'] ?? '');

if (!$message_id || !$conversation_id) {
    redirect_to_account_issue(
        'Message unavailable',
        'The message could not be found.',
        404
    );
}

// Checks whether the user belongs to the conversation
if (!is_conversation_participant($pdo, (int) $conversation_id, $sender_id)) {
    redirect_to_account_issue(
        'Conversation unavailable',
        'You do not have permission to edit messages in this conversation.',
        403
    );
}

// Only allow the sender to edit their own text message within the edit time limit.
try {
    edit_conversation_message(
        $pdo,
        (int) $message_id,
        (int) $conversation_id,
        $sender_id,
        $body
    );
} catch (InvalidArgumentException | RuntimeException $exception) {
    $_SESSION['message_error'] = $exception->getMessage();
}

// Return JSON for AJAX editing, otherwise redirect back to the conversation.
header('Location: /gakumas-sms/messages/view.php?id=' . (int) $conversation_id);
exit;
