<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/messages_helpers.php';
require_once __DIR__ . '/../includes/notifications_helpers.php';

$user_id = (int) ($_SESSION['id'] ?? 0);
$source_conversation_id = filter_input(INPUT_POST, 'source_conversation_id', FILTER_VALIDATE_INT);
$source_message_id = filter_input(INPUT_POST, 'source_message_id', FILTER_VALIDATE_INT);
$target_conversation_id = filter_input(INPUT_POST, 'target_conversation_id', FILTER_VALIDATE_INT);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /gakumas-sms/messages/inbox.php');
    exit;
}

verify_csrf((string) ($_POST['csrf_token'] ?? ''));

if (!$source_conversation_id || !$source_message_id || !$target_conversation_id) {
    $_SESSION['message_error'] = 'Choose a message and conversation to forward.';
    header('Location: /gakumas-sms/messages/inbox.php');
    exit;
}

try {
    $forwarded_message_id = forward_conversation_message(
        $pdo,
        (int) $source_conversation_id,
        (int) $source_message_id,
        (int) $target_conversation_id,
        $user_id
    );

    $sender_name = message_user_display_name($pdo, $user_id);
    $target_conversation = get_conversation_details($pdo, (int) $target_conversation_id, $user_id);
    $is_group_conversation = ($target_conversation['conversation_type'] ?? '') === 'group';
    $group_name = $is_group_conversation
        ? group_conversation_name($pdo, (int) $target_conversation_id)
        : '';

    foreach (get_conversation_notification_recipient_ids($pdo, (int) $target_conversation_id, $user_id) as $recipient_id) {
        create_notification(
            $pdo,
            $recipient_id,
            NOTIFICATION_TYPE_NEW_MESSAGE,
            $is_group_conversation ? 'Forwarded message in ' . $group_name : 'Forwarded message',
            $is_group_conversation
                ? $sender_name . ' forwarded a message to ' . $group_name . '.'
                : $sender_name . ' forwarded you a message.',
            'message',
            $forwarded_message_id,
            '/gakumas-sms/messages/view.php?id=' . (int) $target_conversation_id,
            'forwarded_message:' . $forwarded_message_id . ':' . $recipient_id
        );
    }

    $_SESSION['message_success'] = 'Message forwarded.';
} catch (Throwable $exception) {
    $_SESSION['message_error'] = $exception->getMessage();
}

header('Location: /gakumas-sms/messages/view.php?id=' . (int) $source_conversation_id);
exit;
