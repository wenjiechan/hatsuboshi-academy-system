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

// Validate permission and action
// Prevent a user from changing another user's conversation by editing the form manually
if (
    !$conversation_id ||
    !in_array($action, ['archive', 'restore'], true) ||
    !is_conversation_participant($pdo, (int) $conversation_id, $user_id)
) {
    redirect_to_account_issue(
        'Conversation unavailable',
        'You do not have permission to change this conversation.',
        403,
        '/gakumas-sms/messages/inbox.php',
        'Back to Inbox',
        false
    );
}

// Call the helper function
// Only archives/restores the conversation for the current user
set_conversation_archived(
    $pdo,
    (int) $conversation_id,
    $user_id,
    $action === 'archive'
);

// If the user archived the conversation, they will go back to inbox
// If the user restore the conversation, they will go back to that conversation page
$destination = $action === 'archive'
    ? '/gakumas-sms/messages/inbox.php'
    : '/gakumas-sms/messages/view.php?id=' . (int) $conversation_id;

header('Location: ' . $destination);
exit;
