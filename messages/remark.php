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
$target_user_id = filter_input(INPUT_POST, 'target_user_id', FILTER_VALIDATE_INT);
$remark = (string) ($_POST['remark_name'] ?? '');

if (!$conversation_id || !$target_user_id) {
    header('Location: /gakumas-sms/messages/inbox.php');
    exit;
}

$destination = '/gakumas-sms/messages/view.php?id=' . (int) $conversation_id;

try {
    set_message_contact_remark($pdo, (int) $conversation_id, $user_id, (int) $target_user_id, $remark);
    $_SESSION['message_success'] = trim($remark) === '' ? 'Remark removed.' : 'Remark saved.';
} catch (Throwable $exception) {
    $_SESSION['message_error'] = $exception->getMessage();
}

header('Location: ' . $destination);
exit;
