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

try {
    update_group_conversation_details(
        $pdo,
        (int) $conversation_id,
        $user_id,
        (string) ($_POST['group_name'] ?? ''),
        $_FILES['group_avatar'] ?? null
    );
} catch (InvalidArgumentException | RuntimeException $exception) {
    $_SESSION['message_error'] = $exception->getMessage();
}

header('Location: /gakumas-sms/messages/view.php?id=' . (int) $conversation_id);
exit;
