<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/messages_helpers.php';
require_once __DIR__ . '/../includes/notifications_helpers.php';

$sender_id = (int) $_SESSION['id'];
$student_id = filter_input(INPUT_GET, 'student_id', FILTER_VALIDATE_INT);

// This link is generated from birthday-day notifications for related users only.
if (!$student_id) {
    redirect_to_account_issue(
        'Birthday message unavailable',
        'The birthday message link is missing the student details.',
        400
    );
}

$student_stmt = $pdo->prepare(
    'SELECT
        s.id,
        s.user_id,
        s.name,
        s.producer_id,
        s.producer_status,
        student_user.is_active AS student_is_active
     FROM students s
     INNER JOIN users student_user
        ON student_user.id = s.user_id
     WHERE s.id = ?
       AND s.birthday IS NOT NULL
       AND DATE_FORMAT(s.birthday, "%m-%d") = DATE_FORMAT(CURDATE(), "%m-%d")
     LIMIT 1'
);
$student_stmt->execute([(int) $student_id]);
$student = $student_stmt->fetch();

// Birthday greetings can only be sent on the student's birthday.
if (
    !$student
    || empty($student['student_is_active'])
    || (int) $student['user_id'] === $sender_id
) {
    redirect_to_account_issue(
        'Birthday message unavailable',
        'This birthday message link is not available for your account.',
        403
    );
}

$sender_stmt = $pdo->prepare(
    'SELECT id, role
     FROM users
     WHERE id = ?
       AND is_active = 1
       AND (
            role IN ("student", "teacher", "admin")
            OR (
                role = "producer"
                AND id = ?
                AND ? = "active"
            )
       )
     LIMIT 1'
);
$sender_stmt->execute([
    $sender_id,
    (int) ($student['producer_id'] ?? 0),
    (string) ($student['producer_status'] ?? ''),
]);

// Allow all related users, but not unrelated producers or the birthday student.
if (!$sender_stmt->fetch()) {
    redirect_to_account_issue(
        'Birthday message unavailable',
        'This birthday message link is not available for your account.',
        403
    );
}

$year = date('Y');
$dedupe_key = 'birthday_greeting:student:' . (int) $student['id'] . ':sender:' . $sender_id . ':' . $year;

// One birthday greeting per sender/student/year keeps repeated clicks from sending duplicates.
$duplicate_stmt = $pdo->prepare(
    'SELECT
        m.id,
        m.conversation_id
     FROM messages m
     WHERE m.dedupe_key = ?
     LIMIT 1'
);
$duplicate_stmt->execute([$dedupe_key]);
$duplicate_message = $duplicate_stmt->fetch();

if ($duplicate_message) {
    header('Location: /gakumas-sms/messages/view.php?id=' . (int) $duplicate_message['conversation_id']);
    exit;
}

$body = sprintf('Happy birthday, %s!', $student['name']);
$conversation_id = find_or_create_direct_conversation(
    $pdo,
    $sender_id,
    (int) $student['user_id']
);

$message_id = send_conversation_message(
    $pdo,
    $conversation_id,
    $sender_id,
    $body,
    MESSAGE_TYPE_BIRTHDAY,
    'student',
    (int) $student['id'],
    $dedupe_key
);

if (!is_conversation_muted($pdo, $conversation_id, (int) $student['user_id'])) {
    $sender = get_message_user($pdo, $sender_id);
    $sender_name = $sender['display_name'] ?? $_SESSION['user_name'] ?? 'Someone';

    create_notification(
        $pdo,
        (int) $student['user_id'],
        NOTIFICATION_TYPE_NEW_MESSAGE,
        'New birthday message',
        $sender_name . ' sent you a birthday message.',
        'message',
        $message_id,
        '/gakumas-sms/messages/view.php?id=' . $conversation_id,
        'new_message:' . $message_id . ':' . (int) $student['user_id']
    );
}

header('Location: /gakumas-sms/messages/view.php?id=' . $conversation_id);
exit;
