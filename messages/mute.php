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
$action = (string) ($_POST['action'] ?? '');

// Validate permission
// Prevent a user from changing another user's conversation by editing the form manually
if (
    !$conversation_id ||
    !in_array($action, ['mute', 'unmute'], true) ||
    !is_conversation_participant($pdo, (int) $conversation_id, $user_id)
) {
    redirect_to_account_issue(
        'Conversation unavailable',
        'You do not have permission to change this conversation.',
        403
    );
}

// Calll the helper function from messages helpers
// Change the mute status for the current student
set_conversation_muted(
    $pdo,
    (int) $conversation_id,
    $user_id,
    $action === 'mute'
);

header('Location: /gakumas-sms/messages/view.php?id=' . (int) $conversation_id);
exit;
