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
$message_id = filter_input(INPUT_POST, 'message_id', FILTER_VALIDATE_INT);
$action = (string) ($_POST['action'] ?? 'pin');

if (!$conversation_id || !$message_id || !in_array($action, ['pin', 'unpin'], true)) {
    header('Location: /gakumas-sms/messages/inbox.php');
    exit;
}

try {
    set_conversation_message_pinned(
        $pdo,
        (int) $message_id,
        (int) $conversation_id,
        $user_id,
        $action === 'pin'
    );
} catch (InvalidArgumentException | RuntimeException $exception) {
    $_SESSION['message_error'] = $exception->getMessage();
}

header('Location: /gakumas-sms/messages/view.php?id=' . (int) $conversation_id);
exit;
