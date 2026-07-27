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
$group_conversation_id = filter_input(INPUT_POST, 'group_conversation_id', FILTER_VALIDATE_INT);
$member_id = filter_input(INPUT_POST, 'member_id', FILTER_VALIDATE_INT);

if (!$group_conversation_id || !$member_id || $member_id === $user_id) {
    header('Location: /gakumas-sms/messages/view.php?id=' . (int) $group_conversation_id);
    exit;
}

if (
    !is_group_conversation_participant($pdo, (int) $group_conversation_id, $user_id) ||
    !is_group_conversation_participant($pdo, (int) $group_conversation_id, (int) $member_id)
) {
    header('Location: /gakumas-sms/messages/view.php?id=' . (int) $group_conversation_id);
    exit;
}

$active_member_stmt = $pdo->prepare(
    'SELECT 1
     FROM users
     WHERE id = ?
       AND is_active = 1
     LIMIT 1'
);
$active_member_stmt->execute([(int) $member_id]);

if (!$active_member_stmt->fetchColumn()) {
    $_SESSION['message_error'] = 'This member is unavailable.';
    header('Location: /gakumas-sms/messages/view.php?id=' . (int) $group_conversation_id);
    exit;
}

$conversation_id = find_or_create_direct_conversation($pdo, $user_id, (int) $member_id);

header('Location: /gakumas-sms/messages/view.php?id=' . $conversation_id);
exit;
