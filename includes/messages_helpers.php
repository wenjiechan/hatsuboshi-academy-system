<?php
// Separate normal messages from special system messages
const MESSAGE_TYPE_TEXT = 'text';
const MESSAGE_TYPE_BIRTHDAY = 'birthday';
const MESSAGE_TYPE_PRODUCER_ADD_REQUEST = 'producer_add_request';
const MESSAGE_TYPE_PRODUCER_REMOVE_REQUEST = 'producer_remove_request';
const MESSAGE_TYPE_SYSTEM = 'system';

// Send users back to the correct dashboard based on role
function message_dashboard_url(string $role): string
{
    return match ($role) {
        'admin' => '/gakumas-sms/admin/dashboard.php',
        'producer' => '/gakumas-sms/producer/dashboard.php',
        'teacher' => '/gakumas-sms/teacher/dashboard.php',
        default => '/gakumas-sms/student/dashboard.php',
    };
}

// Create a unique key for two users
function message_direct_key(int $first_user_id, int $second_user_id): string
{
    $user_ids = [$first_user_id, $second_user_id];
    sort($user_ids, SORT_NUMERIC);

    return implode(':', $user_ids);
}

// Checks whether a direct conversation already exists between two users
function find_direct_conversation(PDO $pdo, int $first_user_id, int $second_user_id): ?int
{
    if ($first_user_id === $second_user_id) {
        return null;
    }

    $stmt = $pdo->prepare(
        'SELECT id
         FROM conversations
         WHERE conversation_type = "direct"
           AND direct_key = ?
         LIMIT 1'
    );
    $stmt->execute([message_direct_key($first_user_id, $second_user_id)]);
    $conversation_id = $stmt->fetchColumn();

    return $conversation_id === false ? null : (int) $conversation_id;
}

//Create sirect conversation
function create_direct_conversation(PDO $pdo, int $first_user_id, int $second_user_id): int
{
    if ($first_user_id === $second_user_id) {
        throw new InvalidArgumentException('A user cannot start a conversation with themselves.');
    }

    $user_stmt = $pdo->prepare(
        'SELECT COUNT(*)
         FROM users
         WHERE id IN (?, ?)
           AND is_active = 1'
    );
    $user_stmt->execute([$first_user_id, $second_user_id]);

    if ((int) $user_stmt->fetchColumn() !== 2) {
        throw new InvalidArgumentException('Both conversation participants must be active users.');
    }

    $direct_key = message_direct_key($first_user_id, $second_user_id);

    try {
        $pdo->beginTransaction();

        // Insert a new row into conversations
        $conversation_stmt = $pdo->prepare(
            'INSERT INTO conversations (conversation_type, direct_key)
             VALUES ("direct", ?)'
        );
        $conversation_stmt->execute([$direct_key]);
        $conversation_id = (int) $pdo->lastInsertId();

        // Insert two rows into conversation_participants
        $participant_stmt = $pdo->prepare(
            'INSERT INTO conversation_participants (conversation_id, user_id)
             VALUES (?, ?), (?, ?)'
        );
        $participant_stmt->execute([
            $conversation_id,
            $first_user_id,
            $conversation_id,
            $second_user_id,
        ]);

        $pdo->commit();

        return $conversation_id;
    } catch (PDOException $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        // Another request may have created the same direct conversation first.
        if ((string) $exception->getCode() === '23000') {
            $existing_id = find_direct_conversation($pdo, $first_user_id, $second_user_id);

            if ($existing_id !== null) {
                return $existing_id;
            }
        }

        throw $exception;
    }
}

// If conversation already exists, use it, otherwise create a new one
function find_or_create_direct_conversation(PDO $pdo, int $first_user_id, int $second_user_id): int
{
    return find_direct_conversation($pdo, $first_user_id, $second_user_id)
        ?? create_direct_conversation($pdo, $first_user_id, $second_user_id);
}

// Finds a system conversation for a user
function find_system_conversation(PDO $pdo, int $recipient_user_id): ?int
{
    $stmt = $pdo->prepare(
        'SELECT id
         FROM conversations
         WHERE conversation_type = "system"
           AND direct_key = ?
         LIMIT 1'
    );
    $stmt->execute(['system:user:' . $recipient_user_id]);
    $conversation_id = $stmt->fetchColumn();

    return $conversation_id === false ? null : (int) $conversation_id;
}

// Similar to direct conversation, but for system messages
function find_or_create_system_conversation(
    PDO $pdo,
    int $admin_user_id,
    int $recipient_user_id
): int {
    $existing_id = find_system_conversation($pdo, $recipient_user_id);

    if ($existing_id !== null) {
        return $existing_id;
    }

    if ($admin_user_id === $recipient_user_id) {
        throw new InvalidArgumentException('The system recipient cannot be the admin sender.');
    }

    $users_stmt = $pdo->prepare(
        'SELECT
            SUM(id = ? AND role = "admin" AND is_active = 1) AS valid_admin,
            SUM(id = ? AND is_active = 1) AS valid_recipient
         FROM users
         WHERE id IN (?, ?)'
    );
    $users_stmt->execute([
        $admin_user_id,
        $recipient_user_id,
        $admin_user_id,
        $recipient_user_id,
    ]);
    $users = $users_stmt->fetch();

    if (empty($users['valid_admin']) || empty($users['valid_recipient'])) {
        throw new InvalidArgumentException('The system sender or recipient is unavailable.');
    }

    try {
        $pdo->beginTransaction();

        $conversation_stmt = $pdo->prepare(
            'INSERT INTO conversations (conversation_type, direct_key)
             VALUES ("system", ?)'
        );
        $conversation_stmt->execute(['system:user:' . $recipient_user_id]);
        $conversation_id = (int) $pdo->lastInsertId();

        $participant_stmt = $pdo->prepare(
            'INSERT INTO conversation_participants (conversation_id, user_id)
             VALUES (?, ?), (?, ?)'
        );
        $participant_stmt->execute([
            $conversation_id,
            $admin_user_id,
            $conversation_id,
            $recipient_user_id,
        ]);

        $pdo->commit();

        return $conversation_id;
    } catch (PDOException $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        if ((string) $exception->getCode() === '23000') {
            $existing_id = find_system_conversation($pdo, $recipient_user_id);

            if ($existing_id !== null) {
                return $existing_id;
            }
        }

        throw $exception;
    }
}

// Check whether the log in user is part of the conversation
function is_conversation_participant(PDO $pdo, int $conversation_id, int $user_id): bool
{
    $stmt = $pdo->prepare(
        'SELECT 1
         FROM conversation_participants
         WHERE conversation_id = ?
           AND user_id = ?
           AND deleted_at IS NULL
         LIMIT 1'
    );
    $stmt->execute([$conversation_id, $user_id]);

    return (bool) $stmt->fetchColumn();
}

// Get all active users that the current user can message
function get_message_contacts(PDO $pdo, int $current_user_id): array
{
    $stmt = $pdo->prepare(
        'SELECT
            u.id,
            u.username,
            u.role,
            u.avatar,
            COALESCE(s.name, t.name, u.username) AS display_name,
            t.specialty
         FROM users u
         LEFT JOIN students s ON s.user_id = u.id
         LEFT JOIN teachers t ON t.user_id = u.id
         WHERE u.id <> ?
           AND u.is_active = 1
           AND u.role IN ("student", "producer", "teacher")
         ORDER BY
            FIELD(u.role, "student", "producer", "teacher"),
            display_name'
    );
    $stmt->execute([$current_user_id]);

    return $stmt->fetchAll();
}

// Get one active messagable user by ID
function get_message_user(PDO $pdo, int $user_id): ?array
{
    $stmt = $pdo->prepare(
        'SELECT
            u.id,
            u.username,
            u.role,
            u.avatar,
            COALESCE(s.name, t.name, u.username) AS display_name,
            t.specialty
         FROM users u
         LEFT JOIN students s ON s.user_id = u.id
         LEFT JOIN teachers t ON t.user_id = u.id
         WHERE u.id = ?
           AND u.is_active = 1
           AND u.role IN ("student", "producer", "teacher")
         LIMIT 1'
    );
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    return $user ?: null;
}

// Information about a conversation for the current user
function get_conversation_details(PDO $pdo, int $conversation_id, int $user_id): ?array
{
    $stmt = $pdo->prepare(
        'SELECT
            c.id,
            c.conversation_type,
            c.created_at,
            c.updated_at,
            current_participant.is_muted,
            current_participant.is_archived,
            other_user.id AS other_user_id,
            other_user.username AS other_username,
            other_user.role AS other_role,
            other_user.avatar AS other_avatar,
            CASE
                WHEN c.conversation_type = "system" AND other_user.role = "admin"
                THEN "Admin"
                ELSE COALESCE(student.name, teacher.name, other_user.username)
            END AS other_display_name,
            teacher.specialty AS other_specialty
         FROM conversations c
         INNER JOIN conversation_participants current_participant
            ON current_participant.conversation_id = c.id
           AND current_participant.user_id = ?
           AND current_participant.deleted_at IS NULL
         LEFT JOIN conversation_participants other_participant
            ON other_participant.conversation_id = c.id
           AND other_participant.user_id <> current_participant.user_id
           AND other_participant.deleted_at IS NULL
         LEFT JOIN users other_user ON other_user.id = other_participant.user_id
         LEFT JOIN students student ON student.user_id = other_user.id
         LEFT JOIN teachers teacher ON teacher.user_id = other_user.id
         WHERE c.id = ?
         LIMIT 1'
    );
    $stmt->execute([$user_id, $conversation_id]);
    $conversation = $stmt->fetch();

    return $conversation ?: null;
}

// Gets all other users in the conversation except the sender
function get_conversation_recipient_ids(PDO $pdo, int $conversation_id, int $sender_id): array
{
    $stmt = $pdo->prepare(
        'SELECT user_id
         FROM conversation_participants
         WHERE conversation_id = ?
           AND user_id <> ?
           AND deleted_at IS NULL'
    );
    $stmt->execute([$conversation_id, $sender_id]);

    return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
}

// Gets recipients who have not muted this conversation.
function get_conversation_notification_recipient_ids(
    PDO $pdo,
    int $conversation_id,
    int $sender_id
): array {
    $stmt = $pdo->prepare(
        'SELECT user_id
         FROM conversation_participants
         WHERE conversation_id = ?
           AND user_id <> ?
           AND is_muted = 0
           AND deleted_at IS NULL'
    );
    $stmt->execute([$conversation_id, $sender_id]);

    return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
}

// Checks whether the current user has muted a conversation
function is_conversation_muted(PDO $pdo, int $conversation_id, int $user_id): bool
{
    $stmt = $pdo->prepare(
        'SELECT is_muted
         FROM conversation_participants
         WHERE conversation_id = ?
           AND user_id = ?
           AND deleted_at IS NULL
         LIMIT 1'
    );
    $stmt->execute([$conversation_id, $user_id]);

    return (bool) $stmt->fetchColumn();
}

// Archives or unarchives a conversation for one user
function set_conversation_archived(
    PDO $pdo,
    int $conversation_id,
    int $user_id,
    bool $is_archived
): bool {
    $stmt = $pdo->prepare(
        'UPDATE conversation_participants
         SET is_archived = ?
         WHERE conversation_id = ?
           AND user_id = ?
           AND deleted_at IS NULL'
    );
    $stmt->execute([$is_archived ? 1 : 0, $conversation_id, $user_id]);

    return $stmt->rowCount() > 0;
}

// Mutes or unmutes a conversation for one user
function set_conversation_muted(
    PDO $pdo,
    int $conversation_id,
    int $user_id,
    bool $is_muted
): bool {
    $stmt = $pdo->prepare(
        'UPDATE conversation_participants
         SET is_muted = ?
         WHERE conversation_id = ?
           AND user_id = ?
           AND deleted_at IS NULL'
    );
    $stmt->execute([$is_muted ? 1 : 0, $conversation_id, $user_id]);

    return $stmt->rowCount() > 0;
}

// Gets all conversations for the current user
// If the user has not opened the conversation after a new message, it becomes unread
// Newest conversations appear at the top
function get_user_conversations(PDO $pdo, int $user_id, bool $include_archived = false): array
{
    $archive_sql = $include_archived ? '' : 'AND current_participant.is_archived = 0';

    $stmt = $pdo->prepare(
        'SELECT
            c.id,
            c.conversation_type,
            c.updated_at,
            current_participant.is_archived,
            current_participant.is_muted,
            current_participant.last_read_at,
            other_user.id AS other_user_id,
            other_user.username AS other_username,
            other_user.role AS other_role,
            other_user.avatar AS other_avatar,
            student.name AS other_student_name,
            latest_message.id AS latest_message_id,
            latest_message.sender_id AS latest_sender_id,
            latest_message.message_type AS latest_message_type,
            CASE
                WHEN latest_message.deleted_at IS NOT NULL
                THEN "This message was deleted."
                ELSE latest_message.body
            END AS latest_message_body,
            latest_message.created_at AS latest_message_at,
            (
                SELECT COUNT(*)
                FROM messages unread_message
                WHERE unread_message.conversation_id = c.id
                  AND unread_message.deleted_at IS NULL
                  AND (unread_message.sender_id IS NULL OR unread_message.sender_id <> ?)
                  AND (
                      current_participant.last_read_at IS NULL
                      OR unread_message.created_at > current_participant.last_read_at
                  )
            ) AS unread_count
         FROM conversation_participants current_participant
         INNER JOIN conversations c
            ON c.id = current_participant.conversation_id
         LEFT JOIN conversation_participants other_participant
            ON other_participant.conversation_id = c.id
           AND other_participant.user_id <> current_participant.user_id
           AND other_participant.deleted_at IS NULL
         LEFT JOIN users other_user
            ON other_user.id = other_participant.user_id
         LEFT JOIN students student
            ON student.user_id = other_user.id
         LEFT JOIN messages latest_message
            ON latest_message.id = (
                SELECT recent_message.id
                FROM messages recent_message
                WHERE recent_message.conversation_id = c.id
                ORDER BY recent_message.created_at DESC, recent_message.id DESC
                LIMIT 1
            )
         WHERE current_participant.user_id = ?
           AND current_participant.deleted_at IS NULL
           AND latest_message.id IS NOT NULL
           ' . $archive_sql . '
         ORDER BY
            COALESCE(latest_message.created_at, c.updated_at) DESC,
            c.id DESC'
    );
    $stmt->execute([$user_id, $user_id]);

    return $stmt->fetchAll();
}

// Gets messages inside a conversation
// Load messages ordered from oldest to newest
// Calculates whether the message can be edited
function get_conversation_messages(
    PDO $pdo,
    int $conversation_id,
    int $user_id,
    int $limit = 100
): array {
    if (!is_conversation_participant($pdo, $conversation_id, $user_id)) {
        return [];
    }

    $limit = max(1, min($limit, 200));
    $stmt = $pdo->prepare(
        'SELECT
            m.id,
            m.sender_id,
            m.message_type,
            m.body,
            m.related_type,
            m.related_id,
            m.created_at,
            m.edited_at,
            m.deleted_at,
            request.id AS request_id,
            request.request_type AS request_type,
            request.status AS request_status,
            request.producer_id AS request_producer_id,
            request.student_id AS request_student_id,
            CASE
                WHEN m.sender_id = ?
                 AND m.message_type = "text"
                 AND m.deleted_at IS NULL
                 AND m.created_at >= DATE_SUB(NOW(), INTERVAL 15 MINUTE)
                THEN 1
                ELSE 0
            END AS can_edit,
            CASE
                WHEN m.sender_id = ?
                 AND m.message_type = "text"
                 AND m.deleted_at IS NULL
                THEN 1
                ELSE 0
            END AS can_delete,
            u.username AS sender_username,
            u.role AS sender_role,
            u.avatar AS sender_avatar,
            s.name AS sender_student_name
         FROM messages m
         LEFT JOIN users u ON u.id = m.sender_id
         LEFT JOIN students s ON s.user_id = u.id
         LEFT JOIN producer_student_requests request
            ON request.id = m.related_id
           AND m.related_type = "producer_student_request"
         WHERE m.conversation_id = ?
         ORDER BY m.created_at ASC, m.id ASC
         LIMIT ' . $limit
    );
    $stmt->execute([$user_id, $user_id, $conversation_id]);

    return $stmt->fetchAll();
}

// Gets messages deleted after a polling cursor.
function get_deleted_conversation_messages(
    PDO $pdo,
    int $conversation_id,
    int $user_id,
    string $deleted_after,
    int $limit = 100
): array {
    if (!is_conversation_participant($pdo, $conversation_id, $user_id)) {
        return [];
    }

    $limit = max(1, min($limit, 200));
    $stmt = $pdo->prepare(
        'SELECT
            m.id,
            m.sender_id,
            m.message_type,
            m.created_at,
            m.deleted_at
         FROM messages m
         WHERE m.conversation_id = ?
           AND m.deleted_at IS NOT NULL
           AND m.deleted_at >= DATE_SUB(?, INTERVAL 1 SECOND)
         ORDER BY m.deleted_at ASC, m.id ASC
         LIMIT ' . $limit
    );
    $stmt->execute([$conversation_id, $deleted_after]);

    return $stmt->fetchAll();
}

// Gets messages edited after a polling cursor.
// The one-second overlap prevents edits from being missed by DATETIME's second precision.
function get_edited_conversation_messages(
    PDO $pdo,
    int $conversation_id,
    int $user_id,
    string $edited_after,
    int $limit = 100
): array {
    if (!is_conversation_participant($pdo, $conversation_id, $user_id)) {
        return [];
    }

    $limit = max(1, min($limit, 200));
    $stmt = $pdo->prepare(
        'SELECT
            m.id,
            m.body,
            m.edited_at
         FROM messages m
         WHERE m.conversation_id = ?
           AND m.edited_at IS NOT NULL
           AND m.edited_at >= DATE_SUB(?, INTERVAL 1 SECOND)
           AND m.deleted_at IS NULL
         ORDER BY m.edited_at ASC, m.id ASC
         LIMIT ' . $limit
    );
    $stmt->execute([$conversation_id, $edited_after]);

    return $stmt->fetchAll();
}

// Perform actual message edit
function edit_conversation_message(
    PDO $pdo,
    int $message_id,
    int $conversation_id,
    int $sender_id,
    string $body
): int
{
    $body = trim($body);

    // Validation
    if ($body === '') {
        throw new InvalidArgumentException('Message body cannot be empty.');
    }

    if (mb_strlen($body) > 5000) {
        throw new InvalidArgumentException('Message body cannot exceed 5000 characters.');
    }

    $message_stmt = $pdo->prepare(
        'SELECT id, conversation_id, sender_id, message_type, body, created_at, deleted_at
         FROM messages
         WHERE id = ?
           AND conversation_id = ?
         LIMIT 1'
    );
    $message_stmt->execute([$message_id, $conversation_id]);
    $message = $message_stmt->fetch();

    if (!$message || (int) $message['sender_id'] !== $sender_id) {
        throw new RuntimeException('You cannot edit this message.');
    }

    if ($message['message_type'] !== MESSAGE_TYPE_TEXT || !empty($message['deleted_at'])) {
        throw new RuntimeException('Only active text messages can be edited.');
    }

    $eligibility_stmt = $pdo->prepare(
        'SELECT 1
         FROM messages
         WHERE id = ?
           AND conversation_id = ?
           AND created_at >= DATE_SUB(NOW(), INTERVAL 15 MINUTE)'
    );
    $eligibility_stmt->execute([$message_id, $conversation_id]);

    if (!$eligibility_stmt->fetchColumn()) {
        throw new RuntimeException('Messages can only be edited within 15 minutes of sending.');
    }

    if ($body === (string) $message['body']) {
        return (int) $message['conversation_id'];
    }

    // If the message is changed, it runs
    $update_stmt = $pdo->prepare(
        'UPDATE messages
         SET body = ?,
             edited_at = NOW()
         WHERE id = ?
           AND conversation_id = ?
           AND sender_id = ?
           AND message_type = "text"
           AND deleted_at IS NULL
           AND created_at >= DATE_SUB(NOW(), INTERVAL 15 MINUTE)'
    );
    $update_stmt->execute([$body, $message_id, $conversation_id, $sender_id]);

    if ($update_stmt->rowCount() !== 1) {
        throw new RuntimeException('This message can no longer be edited.');
    }

    return (int) $message['conversation_id'];
}

// Soft delete a message
function delete_conversation_message(
    PDO $pdo,
    int $message_id,
    int $conversation_id,
    int $sender_id
): int {
    $message_stmt = $pdo->prepare(
        'SELECT sender_id, message_type, deleted_at
         FROM messages
         WHERE id = ?
           AND conversation_id = ?
         LIMIT 1'
    );
    $message_stmt->execute([$message_id, $conversation_id]);
    $message = $message_stmt->fetch();

    if (!$message || (int) $message['sender_id'] !== $sender_id) {
        throw new RuntimeException('You cannot delete this message.');
    }

    if ($message['message_type'] !== MESSAGE_TYPE_TEXT) {
        throw new RuntimeException('Only text messages can be deleted.');
    }

    if (!empty($message['deleted_at'])) {
        return $conversation_id;
    }

    $delete_stmt = $pdo->prepare(
        'UPDATE messages
         SET deleted_at = NOW()
         WHERE id = ?
           AND conversation_id = ?
           AND sender_id = ?
           AND message_type = "text"
           AND deleted_at IS NULL'
    );
    $delete_stmt->execute([$message_id, $conversation_id, $sender_id]);

    if ($delete_stmt->rowCount() !== 1) {
        throw new RuntimeException('This message could not be deleted.');
    }

    return $conversation_id;
}

// Insert a new message into the database
function send_conversation_message(
    PDO $pdo,
    int $conversation_id,
    int $sender_id,
    string $body,
    string $message_type = MESSAGE_TYPE_TEXT,
    ?string $related_type = null,
    ?int $related_id = null,
    ?string $dedupe_key = null
): int {
    $body = trim($body);

    if ($body === '') {
        throw new InvalidArgumentException('Message body cannot be empty.');
    }

    if (mb_strlen($body) > 5000) {
        throw new InvalidArgumentException('Message body cannot exceed 5000 characters.');
    }

    if (!is_conversation_participant($pdo, $conversation_id, $sender_id)) {
        throw new RuntimeException('The sender does not belong to this conversation.');
    }

    $allowed_types = [
        MESSAGE_TYPE_TEXT,
        MESSAGE_TYPE_BIRTHDAY,
        MESSAGE_TYPE_PRODUCER_ADD_REQUEST,
        MESSAGE_TYPE_PRODUCER_REMOVE_REQUEST,
        MESSAGE_TYPE_SYSTEM,
    ];

    if (!in_array($message_type, $allowed_types, true)) {
        throw new InvalidArgumentException('Unsupported message type.');
    }

    try {
        $pdo->beginTransaction();

        // Starts a transaction and inserts into messages
        $stmt = $pdo->prepare(
            'INSERT INTO messages
                (conversation_id, sender_id, message_type, body, related_type, related_id, dedupe_key)
             VALUES
                (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $conversation_id,
            $sender_id,
            $message_type,
            $body,
            $related_type,
            $related_id,
            $dedupe_key,
        ]);
        $message_id = (int) $pdo->lastInsertId();

        // Update the conversation timestamp
        $update_stmt = $pdo->prepare(
            'UPDATE conversations
             SET updated_at = CURRENT_TIMESTAMP
             WHERE id = ?'
        );
        $update_stmt->execute([$conversation_id]);

        // Restore archived/ deleted participants
        $restore_stmt = $pdo->prepare(
            'UPDATE conversation_participants
             SET is_archived = 0,
                 deleted_at = NULL
             WHERE conversation_id = ?'
        );
        $restore_stmt->execute([$conversation_id]);

        $pdo->commit();

        return $message_id;
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        throw $exception;
    }
}

// Gets the active admin sender used for system-generated messages.
function get_system_admin_user_id(PDO $pdo): ?int
{
    $stmt = $pdo->query(
        'SELECT id
         FROM users
         WHERE role = "admin"
           AND is_active = 1
         ORDER BY id
         LIMIT 1'
    );
    $admin_user_id = (int) $stmt->fetchColumn();

    return $admin_user_id > 0 ? $admin_user_id : null;
}

// Starts a producer request to end a student relationship.
function create_producer_remove_student_request(PDO $pdo, int $producer_id, int $student_id): int
{
    $student_stmt = $pdo->prepare(
        'SELECT
            s.id,
            s.user_id,
            s.name,
            s.producer_id,
            s.producer_status,
            producer.username AS producer_name
         FROM students s
         INNER JOIN users student_user
            ON student_user.id = s.user_id
           AND student_user.is_active = 1
         INNER JOIN users producer
            ON producer.id = s.producer_id
           AND producer.role = "producer"
           AND producer.is_active = 1
         WHERE s.id = ?
           AND s.producer_id = ?
         LIMIT 1'
    );
    $student_stmt->execute([$student_id, $producer_id]);
    $student = $student_stmt->fetch();

    if (!$student) {
        throw new RuntimeException('This student is not assigned to your producer account.');
    }

    $pending_stmt = $pdo->prepare(
        'SELECT id
         FROM producer_student_requests
         WHERE producer_id = ?
           AND student_id = ?
           AND request_type = "remove"
           AND status = "pending"
         LIMIT 1'
    );
    $pending_stmt->execute([$producer_id, $student_id]);
    $existing_request_id = $pending_stmt->fetchColumn();

    if ($existing_request_id !== false) {
        return (int) $existing_request_id;
    }

    $conversation_id = find_or_create_direct_conversation(
        $pdo,
        $producer_id,
        (int) $student['user_id']
    );

    $body = sprintf(
        '%s requested to end your producer relationship. Please choose Accept if you agree to become unassigned, or Reject if you want to keep this producer.',
        $student['producer_name'] ?: 'Your producer'
    );

    $pdo->beginTransaction();

    try {
        $request_stmt = $pdo->prepare(
            'INSERT INTO producer_student_requests
                (producer_id, student_id, request_type, status)
             VALUES
                (?, ?, "remove", "pending")'
        );
        $request_stmt->execute([$producer_id, $student_id]);
        $request_id = (int) $pdo->lastInsertId();

        $status_stmt = $pdo->prepare(
            'UPDATE students
             SET producer_status = "removal_pending"
             WHERE id = ?
               AND producer_id = ?'
        );
        $status_stmt->execute([$student_id, $producer_id]);

        $pdo->commit();
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        throw $exception;
    }

    $message_id = send_conversation_message(
        $pdo,
        $conversation_id,
        $producer_id,
        $body,
        MESSAGE_TYPE_PRODUCER_REMOVE_REQUEST,
        'producer_student_request',
        $request_id,
        'producer_remove_request:' . $request_id
    );

    $update_stmt = $pdo->prepare(
        'UPDATE producer_student_requests
         SET request_message_id = ?
         WHERE id = ?'
    );
    $update_stmt->execute([$message_id, $request_id]);

    if (
        function_exists('create_notification') &&
        defined('NOTIFICATION_TYPE_NEW_MESSAGE') &&
        !is_conversation_muted($pdo, $conversation_id, (int) $student['user_id'])
    ) {
        create_notification(
            $pdo,
            (int) $student['user_id'],
            NOTIFICATION_TYPE_NEW_MESSAGE,
            'Producer release request',
            ($student['producer_name'] ?: 'Your producer') . ' asked to end your relationship.',
            'message',
            $message_id,
            '/gakumas-sms/messages/view.php?id=' . $conversation_id,
            'new_message:' . $message_id . ':' . (int) $student['user_id']
        );
    }

    return $request_id;
}

// Starts a producer request to add an unassigned student.
function create_producer_add_student_request(PDO $pdo, int $producer_id, int $student_id): int
{
    $student_stmt = $pdo->prepare(
        'SELECT
            s.id,
            s.user_id,
            s.name,
            s.producer_id,
            s.producer_status,
            producer.username AS producer_name
         FROM students s
         INNER JOIN users student_user
            ON student_user.id = s.user_id
           AND student_user.is_active = 1
         INNER JOIN users producer
            ON producer.id = ?
           AND producer.role = "producer"
           AND producer.is_active = 1
         WHERE s.id = ?
           AND s.producer_id IS NULL
         LIMIT 1'
    );
    $student_stmt->execute([$producer_id, $student_id]);
    $student = $student_stmt->fetch();

    if (!$student) {
        throw new RuntimeException('This student is no longer unassigned.');
    }

    $pending_stmt = $pdo->prepare(
        'SELECT id, request_message_id
         FROM producer_student_requests
         WHERE producer_id = ?
           AND student_id = ?
           AND request_type = "add"
           AND status = "pending"
         LIMIT 1'
    );
    $pending_stmt->execute([$producer_id, $student_id]);
    $existing_request = $pending_stmt->fetch();

    if ($existing_request && !empty($existing_request['request_message_id'])) {
        return (int) $existing_request['id'];
    }

    if ($existing_request) {
        $cancel_stmt = $pdo->prepare(
            'UPDATE producer_student_requests
             SET status = "cancelled",
                 cancelled_at = NOW()
             WHERE id = ?
               AND status = "pending"'
        );
        $cancel_stmt->execute([(int) $existing_request['id']]);
    }

    $conversation_id = find_or_create_direct_conversation(
        $pdo,
        $producer_id,
        (int) $student['user_id']
    );

    $body = sprintf(
        '%s wants to become your producer. Please choose Accept if you agree, or Reject if you want to stay unassigned.',
        $student['producer_name'] ?: 'A producer'
    );

    $request_stmt = $pdo->prepare(
        'INSERT INTO producer_student_requests
            (producer_id, student_id, request_type, status)
         VALUES
            (?, ?, "add", "pending")'
    );
    $request_stmt->execute([$producer_id, $student_id]);
    $request_id = (int) $pdo->lastInsertId();

    $message_id = send_conversation_message(
        $pdo,
        $conversation_id,
        $producer_id,
        $body,
        MESSAGE_TYPE_PRODUCER_ADD_REQUEST,
        'producer_student_request',
        $request_id,
        'producer_add_request:' . $request_id
    );

    $update_stmt = $pdo->prepare(
        'UPDATE producer_student_requests
         SET request_message_id = ?
         WHERE id = ?'
    );
    $update_stmt->execute([$message_id, $request_id]);

    if (
        function_exists('create_notification') &&
        defined('NOTIFICATION_TYPE_NEW_MESSAGE') &&
        !is_conversation_muted($pdo, $conversation_id, (int) $student['user_id'])
    ) {
        create_notification(
            $pdo,
            (int) $student['user_id'],
            NOTIFICATION_TYPE_NEW_MESSAGE,
            'Producer add request',
            ($student['producer_name'] ?: 'A producer') . ' asked to become your producer.',
            'message',
            $message_id,
            '/gakumas-sms/messages/view.php?id=' . $conversation_id,
            'new_message:' . $message_id . ':' . (int) $student['user_id']
        );
    }

    return $request_id;
}

// Handles a student's accept/reject choice for a producer relationship request.
function respond_to_producer_student_request(
    PDO $pdo,
    int $request_id,
    int $student_user_id,
    string $action
): int {
    if (!in_array($action, ['accept', 'reject'], true)) {
        throw new InvalidArgumentException('Invalid request response.');
    }

    $request_stmt = $pdo->prepare(
        'SELECT
            request.id,
            request.producer_id,
            request.student_id,
            request.request_type,
            request.status,
            s.user_id AS student_user_id,
            s.name AS student_name,
            s.producer_id AS current_producer_id,
            producer.username AS producer_name
         FROM producer_student_requests request
         INNER JOIN students s ON s.id = request.student_id
         INNER JOIN users producer ON producer.id = request.producer_id
         WHERE request.id = ?
         LIMIT 1'
    );
    $request_stmt->execute([$request_id]);
    $request = $request_stmt->fetch();

    if (!$request || (int) $request['student_user_id'] !== $student_user_id) {
        throw new RuntimeException('This request is not available for your account.');
    }

    if (!in_array($request['request_type'], ['add', 'remove'], true)) {
        throw new RuntimeException('This request type is not supported yet.');
    }

    if ($request['status'] !== 'pending') {
        $conversation_id = find_or_create_direct_conversation(
            $pdo,
            (int) $request['producer_id'],
            $student_user_id
        );

        return $conversation_id;
    }

    if (
        $request['request_type'] === 'remove'
        && (int) $request['current_producer_id'] !== (int) $request['producer_id']
    ) {
        $cancel_stmt = $pdo->prepare(
            'UPDATE producer_student_requests
             SET status = "cancelled",
                 cancelled_at = NOW()
             WHERE id = ?
               AND status = "pending"'
        );
        $cancel_stmt->execute([$request_id]);

        throw new RuntimeException('This relationship has already changed.');
    }

    if (
        $request['request_type'] === 'add'
        && $request['current_producer_id'] !== null
    ) {
        $cancel_stmt = $pdo->prepare(
            'UPDATE producer_student_requests
             SET status = "cancelled",
                 cancelled_at = NOW()
             WHERE id = ?
               AND status = "pending"'
        );
        $cancel_stmt->execute([$request_id]);

        throw new RuntimeException('This student already has a producer.');
    }

    $conversation_id = find_or_create_direct_conversation(
        $pdo,
        (int) $request['producer_id'],
        $student_user_id
    );
    $accepted = $action === 'accept';

    if ($request['request_type'] === 'add') {
        $response_body = $accepted
            ? sprintf('%s accepted the producer request. The producer relationship is now active.', $request['student_name'])
            : sprintf('%s rejected the producer request. The student will stay unassigned.', $request['student_name']);
    } else {
        $response_body = $accepted
            ? sprintf('%s accepted the release request. The student is now unassigned.', $request['student_name'])
            : sprintf('%s rejected the release request. The producer relationship will continue.', $request['student_name']);
    }

    $message_id = send_conversation_message(
        $pdo,
        $conversation_id,
        $student_user_id,
        $response_body,
        MESSAGE_TYPE_SYSTEM,
        'producer_student_request',
        $request_id,
        'producer_remove_response:' . $request_id . ':' . ($accepted ? 'accepted' : 'rejected')
    );

    $pdo->beginTransaction();

    try {
        $update_request_stmt = $pdo->prepare(
            'UPDATE producer_student_requests
             SET status = ?,
                 response_message_id = ?,
                 responded_at = NOW()
             WHERE id = ?
               AND status = "pending"'
        );
        $update_request_stmt->execute([
            $accepted ? 'accepted' : 'rejected',
            $message_id,
            $request_id,
        ]);

        if ($request['request_type'] === 'add' && $accepted) {
            $student_stmt = $pdo->prepare(
                'UPDATE students
                 SET producer_id = ?,
                     producer_status = "active"
                 WHERE id = ?
                   AND producer_id IS NULL'
            );
            $student_stmt->execute([
                (int) $request['producer_id'],
                (int) $request['student_id'],
            ]);
        } elseif ($request['request_type'] === 'add') {
            $student_stmt = $pdo->prepare(
                'UPDATE students
                 SET producer_status = "unassigned"
                 WHERE id = ?
                   AND producer_id IS NULL'
            );
            $student_stmt->execute([(int) $request['student_id']]);
        } elseif ($accepted) {
            $student_stmt = $pdo->prepare(
                'UPDATE students
                 SET producer_id = NULL,
                     producer_status = "unassigned"
                 WHERE id = ?
                   AND producer_id = ?'
            );
            $student_stmt->execute([
                (int) $request['student_id'],
                (int) $request['producer_id'],
            ]);
        } else {
            $student_stmt = $pdo->prepare(
                'UPDATE students
                 SET producer_status = "active"
                 WHERE id = ?
                   AND producer_id = ?'
            );
            $student_stmt->execute([
                (int) $request['student_id'],
                (int) $request['producer_id'],
            ]);
        }

        $pdo->commit();
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        throw $exception;
    }

    if (
        function_exists('create_notification') &&
        defined('NOTIFICATION_TYPE_NEW_MESSAGE') &&
        !is_conversation_muted($pdo, $conversation_id, (int) $request['producer_id'])
    ) {
        create_notification(
            $pdo,
            (int) $request['producer_id'],
            NOTIFICATION_TYPE_NEW_MESSAGE,
            $request['request_type'] === 'add'
                ? 'Producer request response'
                : 'Release request response',
            $response_body,
            'message',
            $message_id,
            '/gakumas-sms/messages/view.php?id=' . $conversation_id,
            'new_message:' . $message_id . ':' . (int) $request['producer_id']
        );
    }

    return $conversation_id;
}

// Update the participant's last_read_at
function mark_conversation_read(PDO $pdo, int $conversation_id, int $user_id): bool
{
    $stmt = $pdo->prepare(
        'UPDATE conversation_participants
         SET last_read_at = CURRENT_TIMESTAMP
         WHERE conversation_id = ?
           AND user_id = ?
           AND deleted_at IS NULL'
    );
    $stmt->execute([$conversation_id, $user_id]);

    return $stmt->rowCount() > 0;
}

// Count all the unread message
function get_unread_message_count(PDO $pdo, int $user_id): int
{
    $stmt = $pdo->prepare(
        'SELECT COUNT(*)
         FROM conversation_participants participant
         INNER JOIN messages message
            ON message.conversation_id = participant.conversation_id
         WHERE participant.user_id = ?
           AND participant.deleted_at IS NULL
           AND participant.is_archived = 0
           AND message.deleted_at IS NULL
           AND (message.sender_id IS NULL OR message.sender_id <> participant.user_id)
           AND (
               participant.last_read_at IS NULL
               OR message.created_at > participant.last_read_at
           )'
    );
    $stmt->execute([$user_id]);

    return (int) $stmt->fetchColumn();
}

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

// Decides what name to show in the inbox
function get_message_user_display_name(array $row, string $prefix = 'other_'): string
{
    if (
        ($row['conversation_type'] ?? '') === 'system' &&
        ($row[$prefix . 'role'] ?? '') === 'admin'
    ) {
        return 'Admin';
    }

    $student_name = trim((string) ($row[$prefix . 'student_name'] ?? ''));

    if ($student_name !== '') {
        return $student_name;
    }

    $username = trim((string) ($row[$prefix . 'username'] ?? ''));

    return $username !== '' ? $username : 'System';
}

// Gets the latest few conversation in dashboard
function get_recent_conversation_previews(PDO $pdo, int $user_id, int $limit = 3): array
{
    $limit = max(1, min($limit, 10));
    $conversations = array_filter(
        get_user_conversations($pdo, $user_id),
        static fn(array $conversation): bool => !empty($conversation['latest_message_id'])
    );

    return array_slice(array_values($conversations), 0, $limit);
}

// Shared avatar helper
function message_avatar_path(?string $avatar, ?string $role): string
{
    $avatar = trim((string) $avatar);
    $role = (string) $role;

    if ($avatar !== '') {
        if (preg_match('/^https?:\/\//i', $avatar) || str_starts_with($avatar, '/')) {
            return $avatar;
        }

        return $role === 'student'
            ? '/gakumas-sms/assets/images/avatars/idols/' . rawurlencode($avatar)
            : '/gakumas-sms/assets/images/avatars/' . rawurlencode($avatar);
    }

    return match ($role) {
        'producer' => '/gakumas-sms/assets/images/avatars/default_producer.webp',
        'teacher' => '/gakumas-sms/assets/images/avatars/default_teacher.webp',
        default => '/gakumas-sms/assets/images/avatars/default.webp',
    };
}
