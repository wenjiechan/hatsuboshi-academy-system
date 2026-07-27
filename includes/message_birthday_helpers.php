<?php

// Automatically sends birthday messages to students whose birthday is today
function generate_automatic_birthday_messages(
    PDO $pdo,
    ?DateTimeImmutable $now = null
): int {
    $now = $now ?: new DateTimeImmutable('now', new DateTimeZone('Asia/Kuala_Lumpur'));
    $month_day = $now->format('m-d');
    $year = $now->format('Y');

    $admin_stmt = $pdo->query(
        'SELECT id
         FROM users
         WHERE role = "admin"
           AND is_active = 1
         ORDER BY id
         LIMIT 1'
    );
    $admin_user_id = (int) $admin_stmt->fetchColumn();

    if ($admin_user_id <= 0) {
        return 0;
    }

    $students_stmt = $pdo->prepare(
        'SELECT s.id, s.user_id, s.name
         FROM students s
         INNER JOIN users student_user
            ON student_user.id = s.user_id
           AND student_user.is_active = 1
         WHERE s.birthday IS NOT NULL
           AND DATE_FORMAT(s.birthday, "%m-%d") = ?'
    );
    $students_stmt->execute([$month_day]);

    // If another request created the conversation first, reuse the existing conversation.
    $duplicate_stmt = $pdo->prepare(
        'SELECT id FROM messages WHERE dedupe_key = ? LIMIT 1'
    );
    $created = 0;

    foreach ($students_stmt->fetchAll() as $student) {
        $dedupe_key = 'birthday:student:' . (int) $student['id'] . ':' . $year;
        $duplicate_stmt->execute([$dedupe_key]);

        if ($duplicate_stmt->fetchColumn()) {
            continue;
        }

        $body = sprintf(
            'Happy birthday, %s! Everyone at Hatsuboshi Gakuen wishes you a wonderful day.',
            $student['name']
        );

        try {
            $conversation_id = find_or_create_system_conversation(
                $pdo,
                $admin_user_id,
                (int) $student['user_id']
            );
            $message_id = send_conversation_message(
                $pdo,
                $conversation_id,
                $admin_user_id,
                $body,
                MESSAGE_TYPE_BIRTHDAY,
                'student',
                (int) $student['id'],
                $dedupe_key
            );

            if (
                function_exists('create_notification') &&
                defined('NOTIFICATION_TYPE_NEW_MESSAGE') &&
                !is_conversation_muted($pdo, $conversation_id, (int) $student['user_id'])
            ) {
                create_notification(
                    $pdo,
                    (int) $student['user_id'],
                    NOTIFICATION_TYPE_NEW_MESSAGE,
                    'New birthday message',
                    'Admin sent you a birthday message.',
                    'message',
                    $message_id,
                    '/gakumas-sms/messages/view.php?id=' . $conversation_id,
                    'new_message:' . $message_id . ':' . (int) $student['user_id']
                );
            }

            $created++;
        } catch (PDOException $exception) {
            if ((string) $exception->getCode() !== '23000') {
                throw $exception;
            }
        }
    }

    return $created;
}
