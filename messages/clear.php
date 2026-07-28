<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/messages_helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /gakumas-sms/messages/inbox.php');
    exit;
}

verify_csrf((string) ($_POST['csrf_token'] ?? ''));

$user_id = (int) $_SESSION['id'];
$conversation_id = filter_input(INPUT_POST, 'conversation_id', FILTER_VALIDATE_INT);

if (!$conversation_id) {
    header('Location: /gakumas-sms/messages/inbox.php');
    exit;
}

$destination = '/gakumas-sms/messages/view.php?id=' . (int) $conversation_id;

try {
    $conversation = get_conversation_details($pdo, (int) $conversation_id, $user_id);

    // System conversations are app notices, so only normal direct/group chats can be cleared.
    if (!$conversation || ($conversation['conversation_type'] ?? '') === 'system') {
        throw new RuntimeException('This conversation cannot be cleared.');
    }

    clear_conversation_for_user($pdo, (int) $conversation_id, $user_id);
    $_SESSION['message_success'] = 'Chat cleared for you.';
} catch (Throwable $exception) {
    $_SESSION['message_error'] = $exception->getMessage();
}

header('Location: ' . $destination);
exit;
