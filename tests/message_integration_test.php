<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/messages_helpers.php';

$databaseName = 'gakumas_sms_message_test_' . date('Ymd_His') . '_' . random_int(1000, 9999);
$server = new PDO(
    'mysql:host=127.0.0.1;charset=utf8mb4',
    'root',
    '',
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
);

$results = [];

function test_result(array &$results, string $name, bool $passed, string $detail = ''): void
{
    $results[] = [
        'name' => $name,
        'passed' => $passed,
        'detail' => $detail,
    ];
}

function expect_exception(callable $callback, string $expectedMessage): bool
{
    try {
        $callback();
    } catch (Throwable $exception) {
        return str_contains($exception->getMessage(), $expectedMessage);
    }

    return false;
}

try {
    $server->exec(
        'CREATE DATABASE `' . $databaseName . '`
         CHARACTER SET utf8mb4
         COLLATE utf8mb4_unicode_ci'
    );

    $pdo = new PDO(
        'mysql:host=127.0.0.1;dbname=' . $databaseName . ';charset=utf8mb4',
        'root',
        '',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );

    $pdo->exec(
        'CREATE TABLE users (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(100) NOT NULL,
            role ENUM("admin", "producer", "teacher", "student") NOT NULL,
            avatar VARCHAR(255) DEFAULT NULL,
            is_active TINYINT(1) NOT NULL DEFAULT 1
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        CREATE TABLE students (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL UNIQUE,
            name VARCHAR(100) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        CREATE TABLE teachers (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL UNIQUE,
            name VARCHAR(100) NOT NULL,
            specialty ENUM("vocal", "dance", "visual") DEFAULT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        CREATE TABLE conversations (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            conversation_type ENUM("direct", "system") NOT NULL DEFAULT "direct",
            direct_key VARCHAR(50) DEFAULT NULL UNIQUE,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        CREATE TABLE conversation_participants (
            conversation_id INT NOT NULL,
            user_id INT NOT NULL,
            last_read_at DATETIME DEFAULT NULL,
            is_archived TINYINT(1) NOT NULL DEFAULT 0,
            is_muted TINYINT(1) NOT NULL DEFAULT 0,
            joined_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            deleted_at DATETIME DEFAULT NULL,
            PRIMARY KEY (conversation_id, user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        CREATE TABLE messages (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            conversation_id INT NOT NULL,
            sender_id INT DEFAULT NULL,
            message_type ENUM("text", "birthday", "producer_add_request", "producer_remove_request", "system")
                NOT NULL DEFAULT "text",
            body TEXT NOT NULL,
            related_type VARCHAR(50) DEFAULT NULL,
            related_id INT DEFAULT NULL,
            dedupe_key VARCHAR(190) DEFAULT NULL UNIQUE,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            edited_at DATETIME DEFAULT NULL,
            deleted_at DATETIME DEFAULT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );

    $pdo->exec(
        'INSERT INTO users (id, username, role) VALUES
            (1, "Admin", "admin"),
            (2, "Producer", "producer"),
            (3, "Saki", "student"),
            (4, "Outsider", "student");
         INSERT INTO students (user_id, name) VALUES
            (3, "Saki Hanami"),
            (4, "Outside Student")'
    );

    $directConversationId = create_direct_conversation($pdo, 2, 3);

    test_result(
        $results,
        'Empty conversation is hidden from the sender inbox',
        get_user_conversations($pdo, 2) === []
    );
    test_result(
        $results,
        'Empty conversation is hidden from the recipient inbox',
        get_user_conversations($pdo, 3) === []
    );

    test_result(
        $results,
        'Membership accepts a participant',
        is_conversation_participant($pdo, $directConversationId, 2)
    );
    test_result(
        $results,
        'Membership rejects an outsider',
        !is_conversation_participant($pdo, $directConversationId, 4)
    );

    $unicodeBody = 'こんにちは 咲季 — 你好 — 안녕하세요 — 🎉';
    $messageId = send_conversation_message($pdo, $directConversationId, 2, $unicodeBody);
    $storedBody = $pdo->query('SELECT body FROM messages WHERE id = ' . $messageId)->fetchColumn();
    test_result($results, 'Unicode message storage', $storedBody === $unicodeBody);
    test_result(
        $results,
        'Conversation becomes visible to both users after the first message',
        count(get_user_conversations($pdo, 2)) === 1
            && count(get_user_conversations($pdo, 3)) === 1
    );

    set_conversation_archived($pdo, $directConversationId, 3, true);
    $studentConversations = get_user_conversations($pdo, 3);
    $studentConversationsIncludingArchived = get_user_conversations($pdo, 3, true);
    test_result(
        $results,
        'Archive hides the conversation only for that user',
        $studentConversations === []
            && count($studentConversationsIncludingArchived) === 1
            && (int) $studentConversationsIncludingArchived[0]['is_archived'] === 1
            && count(get_user_conversations($pdo, 2)) === 1
    );

    $messageId = send_conversation_message($pdo, $directConversationId, 2, 'New message restores archive');
    $studentConversationAfterMessage = get_conversation_details($pdo, $directConversationId, 3);
    test_result(
        $results,
        'A new message restores an archived conversation',
        (int) $studentConversationAfterMessage['is_archived'] === 0
            && count(get_user_conversations($pdo, 3)) === 1
    );

    set_conversation_muted($pdo, $directConversationId, 3, true);
    test_result(
        $results,
        'Muted recipient is excluded from message notifications',
        is_conversation_muted($pdo, $directConversationId, 3)
            && get_conversation_notification_recipient_ids($pdo, $directConversationId, 2) === []
            && get_conversation_notification_recipient_ids($pdo, $directConversationId, 3) === [2]
    );
    set_conversation_muted($pdo, $directConversationId, 3, false);
    test_result(
        $results,
        'Unmuted recipient receives message notifications again',
        !is_conversation_muted($pdo, $directConversationId, 3)
            && get_conversation_notification_recipient_ids($pdo, $directConversationId, 2) === [3]
    );

    $unreadBefore = get_unread_message_count($pdo, 3);
    $readUpdated = mark_conversation_read($pdo, $directConversationId, 3);
    $unreadAfter = get_unread_message_count($pdo, 3);
    test_result(
        $results,
        'Read tracking clears unread messages',
        $unreadBefore === 2 && $readUpdated && $unreadAfter === 0,
        "before=$unreadBefore, after=$unreadAfter"
    );

    edit_conversation_message($pdo, $messageId, $directConversationId, 2, 'Edited safely');
    $edited = $pdo->query(
        'SELECT body, edited_at FROM messages WHERE id = ' . $messageId
    )->fetch();
    test_result(
        $results,
        'Sender can edit a recent text message',
        $edited['body'] === 'Edited safely' && $edited['edited_at'] !== null
    );
    $editedPollResults = get_edited_conversation_messages(
        $pdo,
        $directConversationId,
        3,
        '1970-01-01 00:00:00'
    );
    test_result(
        $results,
        'Edited message is returned for live polling',
        count($editedPollResults) === 1
            && (int) $editedPollResults[0]['id'] === $messageId
            && $editedPollResults[0]['body'] === 'Edited safely'
    );
    $recipientInboxAfterEdit = get_user_conversations($pdo, 3);
    test_result(
        $results,
        'Inbox preview uses the edited latest message',
        count($recipientInboxAfterEdit) === 1
            && $recipientInboxAfterEdit[0]['latest_message_body'] === 'Edited safely'
    );

    test_result(
        $results,
        'Another user cannot edit the message',
        expect_exception(
            fn() => edit_conversation_message($pdo, $messageId, $directConversationId, 3, 'Not allowed'),
            'cannot edit'
        )
    );

    $pdo->exec(
        'UPDATE messages
         SET created_at = DATE_SUB(NOW(), INTERVAL 16 MINUTE)
         WHERE id = ' . $messageId
    );
    test_result(
        $results,
        'A message older than 15 minutes cannot be edited',
        expect_exception(
            fn() => edit_conversation_message($pdo, $messageId, $directConversationId, 2, 'Too late'),
            'within 15 minutes'
        )
    );

    $deleteMessageId = send_conversation_message(
        $pdo,
        $directConversationId,
        2,
        'Delete this message'
    );
    test_result(
        $results,
        'Another user cannot delete the message',
        expect_exception(
            fn() => delete_conversation_message($pdo, $deleteMessageId, $directConversationId, 3),
            'cannot delete'
        )
    );
    delete_conversation_message($pdo, $deleteMessageId, $directConversationId, 2);
    $deletedMessage = $pdo->query(
        'SELECT deleted_at FROM messages WHERE id = ' . $deleteMessageId
    )->fetch();
    $deletedPollResults = get_deleted_conversation_messages(
        $pdo,
        $directConversationId,
        3,
        '1970-01-01 00:00:00'
    );
    $conversationMessagesAfterDelete = get_conversation_messages($pdo, $directConversationId, 3);
    $deletedRenderedMessage = array_values(array_filter(
        $conversationMessagesAfterDelete,
        static fn(array $message): bool => (int) $message['id'] === $deleteMessageId
    ));
    test_result(
        $results,
        'Sender can soft-delete a text message',
        $deletedMessage['deleted_at'] !== null
            && count($deletedPollResults) === 1
            && (int) $deletedPollResults[0]['id'] === $deleteMessageId
            && count($deletedRenderedMessage) === 1
            && !empty($deletedRenderedMessage[0]['deleted_at'])
            && empty($deletedRenderedMessage[0]['can_edit'])
            && empty($deletedRenderedMessage[0]['can_delete'])
    );
    $inboxAfterDelete = get_user_conversations($pdo, 3);
    test_result(
        $results,
        'Deleted latest message keeps the conversation visible with a safe preview',
        count($inboxAfterDelete) === 1
            && $inboxAfterDelete[0]['latest_message_body'] === 'This message was deleted.'
    );

    $birthdayMessageId = send_conversation_message(
        $pdo,
        $directConversationId,
        2,
        'Happy birthday!',
        MESSAGE_TYPE_BIRTHDAY
    );
    test_result(
        $results,
        'Special messages cannot be edited',
        expect_exception(
            fn() => edit_conversation_message($pdo, $birthdayMessageId, $directConversationId, 2, 'Changed'),
            'Only active text messages'
        )
    );
    test_result(
        $results,
        'Special messages cannot be deleted',
        expect_exception(
            fn() => delete_conversation_message($pdo, $birthdayMessageId, $directConversationId, 2),
            'Only text messages'
        )
    );

    $systemConversationId = find_or_create_system_conversation($pdo, 1, 3);
    $systemConversation = get_conversation_details($pdo, $systemConversationId, 3);
    test_result(
        $results,
        'System conversation is identified as read-only by the send route',
        $systemConversation !== null && $systemConversation['conversation_type'] === 'system'
    );

    $pdo->prepare(
        'UPDATE conversation_participants
         SET deleted_at = NOW()
         WHERE conversation_id = ? AND user_id = ?'
    )->execute([$directConversationId, 3]);
    test_result(
        $results,
        'Deleted participant membership is rejected',
        !is_conversation_participant($pdo, $directConversationId, 3)
    );

    $failed = array_filter($results, fn(array $result): bool => !$result['passed']);

    echo json_encode([
        'database' => $databaseName,
        'passed' => count($results) - count($failed),
        'failed' => count($failed),
        'tests' => $results,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;

    $exitCode = count($failed) === 0 ? 0 : 1;
} finally {
    $server->exec('DROP DATABASE IF EXISTS `' . $databaseName . '`');
}

exit($exitCode ?? 1);
