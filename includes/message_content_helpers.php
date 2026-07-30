<?php
// Get messages inside a conversation from oldest to newest.
// Also calculates whether each message can be edited or deleted by the current user.
function get_conversation_messages(
    PDO $pdo,
    int $conversation_id,
    int $user_id,
    int $limit = 100
): array {
    ensure_message_pin_schema($pdo);
    ensure_message_reply_schema($pdo);
    ensure_message_clear_schema($pdo);
    ensure_message_attachment_schema($pdo);
    ensure_message_sticker_schema($pdo);
    ensure_message_contact_remark_schema($pdo);

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
            m.sticker_key,
            m.created_at,
            m.edited_at,
            m.deleted_at,
            m.pinned_at,
            m.pinned_by,
            m.reply_to_message_id,
            m.forwarded_from_label,
            m.forwarded_from_message_id,
            reply.body AS reply_body,
            reply.message_type AS reply_message_type,
            reply.sticker_key AS reply_sticker_key,
            reply.deleted_at AS reply_deleted_at,
            (
                SELECT reply_attachment.original_name
                FROM message_attachments reply_attachment
                WHERE reply_attachment.message_id = reply.id
                ORDER BY reply_attachment.id ASC
                LIMIT 1
            ) AS reply_attachment_name,
            COALESCE(NULLIF(reply_remark.remark_name, ""), reply_student.name, reply_teacher.name, reply_user.username) AS reply_sender_display_name,
            request.id AS request_id,
            request.request_type AS request_type,
            request.status AS request_status,
            request.producer_id AS request_producer_id,
            request.student_id AS request_student_id,
            CASE
                WHEN m.sender_id = ?
                 AND m.message_type = "text"
                 AND m.deleted_at IS NULL
                 AND TRIM(m.body) <> ""
                 AND m.created_at >= DATE_SUB(NOW(), INTERVAL 15 MINUTE)
                THEN 1
                ELSE 0
            END AS can_edit,
            CASE
                WHEN m.sender_id = ?
                 AND m.message_type IN ("text", "sticker")
                 AND m.deleted_at IS NULL
                THEN 1
                ELSE 0
            END AS can_delete,
            CASE
                WHEN m.deleted_at IS NULL THEN 1
                ELSE 0
            END AS can_pin,
            u.username AS sender_username,
            u.role AS sender_role,
            u.avatar AS sender_avatar,
            COALESCE(NULLIF(sender_remark.remark_name, ""), s.name, t.name, u.username) AS sender_display_name,
            s.name AS sender_student_name,
            t.name AS sender_teacher_name
         FROM messages m
         INNER JOIN conversation_participants current_participant
            ON current_participant.conversation_id = m.conversation_id
           AND current_participant.user_id = ?
           AND current_participant.deleted_at IS NULL
         INNER JOIN conversations c
            ON c.id = m.conversation_id
         LEFT JOIN users u ON u.id = m.sender_id
         LEFT JOIN students s ON s.user_id = u.id
         LEFT JOIN teachers t ON t.user_id = u.id
         LEFT JOIN message_contact_remarks sender_remark
            ON sender_remark.conversation_id = m.conversation_id
           AND sender_remark.owner_user_id = current_participant.user_id
           AND sender_remark.target_user_id = u.id
         LEFT JOIN messages reply
            ON reply.id = m.reply_to_message_id
           AND reply.conversation_id = m.conversation_id
         LEFT JOIN users reply_user ON reply_user.id = reply.sender_id
         LEFT JOIN students reply_student ON reply_student.user_id = reply_user.id
         LEFT JOIN teachers reply_teacher ON reply_teacher.user_id = reply_user.id
         LEFT JOIN message_contact_remarks reply_remark
            ON reply_remark.conversation_id = m.conversation_id
           AND reply_remark.owner_user_id = current_participant.user_id
           AND reply_remark.target_user_id = reply_user.id
         LEFT JOIN producer_student_requests request
            ON request.id = m.related_id
           AND m.related_type = "producer_student_request"
         WHERE m.conversation_id = ?
           AND (
               c.conversation_type <> "group"
               OR m.created_at >= current_participant.joined_at
           )
           -- Clearing a chat hides older messages only for the current participant.
           AND (
               current_participant.cleared_at IS NULL
               OR m.created_at > current_participant.cleared_at
           )
         ORDER BY m.created_at ASC, m.id ASC
         LIMIT ' . $limit
    );
    $stmt->execute([$user_id, $user_id, $user_id, $conversation_id]);

    return hydrate_message_attachments($pdo, $stmt->fetchAll());
}

// Gets messages deleted after a polling cursor.
function get_deleted_conversation_messages(
    PDO $pdo,
    int $conversation_id,
    int $user_id,
    string $deleted_after,
    int $limit = 100
): array {
    ensure_message_clear_schema($pdo);

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
         INNER JOIN conversation_participants current_participant
            ON current_participant.conversation_id = m.conversation_id
           AND current_participant.user_id = ?
           AND current_participant.deleted_at IS NULL
         INNER JOIN conversations c
            ON c.id = m.conversation_id
         WHERE m.conversation_id = ?
           AND m.deleted_at IS NOT NULL
           AND m.deleted_at >= DATE_SUB(?, INTERVAL 1 SECOND)
           AND (
               c.conversation_type <> "group"
               OR m.created_at >= current_participant.joined_at
           )
           -- Ignore deleted-message updates for rows hidden by clear chat.
           AND (
               current_participant.cleared_at IS NULL
               OR m.created_at > current_participant.cleared_at
           )
         ORDER BY m.deleted_at ASC, m.id ASC
         LIMIT ' . $limit
    );
    $stmt->execute([$user_id, $conversation_id, $deleted_after]);

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
    ensure_message_clear_schema($pdo);
    ensure_message_attachment_schema($pdo);

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
         INNER JOIN conversation_participants current_participant
            ON current_participant.conversation_id = m.conversation_id
           AND current_participant.user_id = ?
           AND current_participant.deleted_at IS NULL
         INNER JOIN conversations c
            ON c.id = m.conversation_id
         WHERE m.conversation_id = ?
           AND m.edited_at IS NOT NULL
           AND m.edited_at >= DATE_SUB(?, INTERVAL 1 SECOND)
           AND m.deleted_at IS NULL
           AND (
               c.conversation_type <> "group"
               OR m.created_at >= current_participant.joined_at
           )
           -- Ignore edited-message updates for rows hidden by clear chat.
           AND (
               current_participant.cleared_at IS NULL
               OR m.created_at > current_participant.cleared_at
           )
         ORDER BY m.edited_at ASC, m.id ASC
         LIMIT ' . $limit
    );
    $stmt->execute([$user_id, $conversation_id, $edited_after]);

    return hydrate_message_attachments($pdo, $stmt->fetchAll());
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

    if (!in_array($message['message_type'], [MESSAGE_TYPE_TEXT, MESSAGE_TYPE_STICKER], true)) {
        throw new RuntimeException('Only text messages and stickers can be deleted.');
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
           AND message_type IN ("text", "sticker")
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
    ?string $dedupe_key = null,
    ?int $reply_to_message_id = null,
    ?string $forwarded_from_label = null,
    ?int $forwarded_from_message_id = null,
    array $attachments = [],
    ?string $sticker_key = null
): int {
    ensure_message_reply_schema($pdo);
    ensure_message_clear_schema($pdo);
    ensure_message_attachment_schema($pdo);
    ensure_message_sticker_schema($pdo);

    $body = trim($body);

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
        MESSAGE_TYPE_STICKER,
    ];

    if (!in_array($message_type, $allowed_types, true)) {
        throw new InvalidArgumentException('Unsupported message type.');
    }

    $sticker = null;

    if ($message_type === MESSAGE_TYPE_STICKER) {
        // Sticker keys are validated against the local built-in pack catalog.
        $sticker = get_message_sticker($sticker_key);

        if (!$sticker) {
            throw new InvalidArgumentException('Choose a valid sticker.');
        }

        if ($body !== '' || $attachments !== []) {
            throw new InvalidArgumentException('Send stickers by themselves.');
        }

        $sticker_key = $sticker['key'];
    } else {
        $sticker_key = null;
    }

    if ($body === '' && $attachments === [] && $sticker === null) {
        throw new InvalidArgumentException('Write a message, add an attachment, or choose a sticker.');
    }

    if ($reply_to_message_id !== null && $reply_to_message_id <= 0) {
        $reply_to_message_id = null;
    }

    if ($reply_to_message_id !== null) {
        $reply_stmt = $pdo->prepare(
            'SELECT reply.id
             FROM messages reply
             INNER JOIN conversation_participants current_participant
                ON current_participant.conversation_id = reply.conversation_id
               AND current_participant.user_id = ?
               AND current_participant.deleted_at IS NULL
             INNER JOIN conversations c
                ON c.id = reply.conversation_id
             WHERE reply.id = ?
               AND reply.conversation_id = ?
               AND reply.deleted_at IS NULL
               AND reply.message_type <> "system"
               AND (
                   c.conversation_type <> "group"
                   OR reply.created_at >= current_participant.joined_at
               )
               -- A cleared old message cannot be selected as a reply target.
               AND (
                   current_participant.cleared_at IS NULL
                   OR reply.created_at > current_participant.cleared_at
               )
             LIMIT 1'
        );
        $reply_stmt->execute([$sender_id, $reply_to_message_id, $conversation_id]);

        if (!$reply_stmt->fetchColumn()) {
            throw new InvalidArgumentException('The message you are replying to is unavailable.');
        }
    }

    try {
        $pdo->beginTransaction();

        // Starts a transaction and inserts into messages
        $stmt = $pdo->prepare(
            'INSERT INTO messages
                (
                    conversation_id,
                    sender_id,
                    message_type,
                    body,
                    related_type,
                    related_id,
                    sticker_key,
                    dedupe_key,
                    reply_to_message_id,
                    forwarded_from_label,
                    forwarded_from_message_id
                )
             VALUES
                (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $conversation_id,
            $sender_id,
            $message_type,
            $body,
            $related_type,
            $related_id,
            $sticker_key,
            $dedupe_key,
            $reply_to_message_id,
            $forwarded_from_label !== null ? trim($forwarded_from_label) : null,
            $forwarded_from_message_id,
        ]);
        $message_id = (int) $pdo->lastInsertId();
        insert_message_attachments($pdo, $message_id, $attachments);

        // Update the conversation timestamp
        $update_stmt = $pdo->prepare(
            'UPDATE conversations
             SET updated_at = CURRENT_TIMESTAMP
             WHERE id = ?'
        );
        $update_stmt->execute([$conversation_id]);

        // Bring active members back to the inbox without reviving users who left a group.
        $restore_stmt = $pdo->prepare(
            'UPDATE conversation_participants
             SET is_archived = 0
             WHERE conversation_id = ?
               AND deleted_at IS NULL'
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

function ensure_message_reply_schema(PDO $pdo): void
{
    static $ensured = false;

    if ($ensured) {
        return;
    }

    $column_stmt = $pdo->prepare(
        'SELECT 1
         FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_NAME = "messages"
           AND COLUMN_NAME = "reply_to_message_id"
         LIMIT 1'
    );
    $column_stmt->execute();

    if (!$column_stmt->fetch()) {
        $pdo->exec('ALTER TABLE messages ADD COLUMN reply_to_message_id INT NULL AFTER dedupe_key');
    }

    $forward_label_stmt = $pdo->prepare(
        'SELECT 1
         FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_NAME = "messages"
           AND COLUMN_NAME = "forwarded_from_label"
         LIMIT 1'
    );
    $forward_label_stmt->execute();

    if (!$forward_label_stmt->fetchColumn()) {
        $pdo->exec('ALTER TABLE messages ADD COLUMN forwarded_from_label VARCHAR(160) NULL AFTER reply_to_message_id');
    }

    $forward_message_stmt = $pdo->prepare(
        'SELECT 1
         FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_NAME = "messages"
           AND COLUMN_NAME = "forwarded_from_message_id"
         LIMIT 1'
    );
    $forward_message_stmt->execute();

    if (!$forward_message_stmt->fetchColumn()) {
        $pdo->exec('ALTER TABLE messages ADD COLUMN forwarded_from_message_id INT NULL AFTER forwarded_from_label');
    }

    $index_stmt = $pdo->prepare(
        'SELECT 1
         FROM information_schema.STATISTICS
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_NAME = "messages"
           AND INDEX_NAME = "idx_messages_reply_to"
         LIMIT 1'
    );
    $index_stmt->execute();

    if (!$index_stmt->fetchColumn()) {
        $pdo->exec('CREATE INDEX idx_messages_reply_to ON messages (reply_to_message_id)');
    }

    $forward_index_stmt = $pdo->prepare(
        'SELECT 1
         FROM information_schema.STATISTICS
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_NAME = "messages"
           AND INDEX_NAME = "idx_messages_forwarded_from"
         LIMIT 1'
    );
    $forward_index_stmt->execute();

    if (!$forward_index_stmt->fetchColumn()) {
        $pdo->exec('CREATE INDEX idx_messages_forwarded_from ON messages (forwarded_from_message_id)');
    }

    $ensured = true;
}

function get_forwardable_message(PDO $pdo, int $conversation_id, int $message_id, int $user_id): ?array
{
    ensure_message_clear_schema($pdo);
    ensure_message_sticker_schema($pdo);

    if (!is_conversation_participant($pdo, $conversation_id, $user_id)) {
        return null;
    }

    $stmt = $pdo->prepare(
        'SELECT
            m.id,
            m.conversation_id,
            m.sender_id,
            m.message_type,
            m.body,
            m.sticker_key,
            m.created_at,
            CASE
                WHEN c.conversation_type = "group" THEN c.group_name
                ELSE COALESCE(source_student.name, source_teacher.name, source_user.username, "Someone")
            END AS forward_source_label
         FROM messages m
         INNER JOIN conversation_participants current_participant
            ON current_participant.conversation_id = m.conversation_id
           AND current_participant.user_id = ?
           AND current_participant.deleted_at IS NULL
         INNER JOIN conversations c
            ON c.id = m.conversation_id
         LEFT JOIN users source_user ON source_user.id = m.sender_id
         LEFT JOIN students source_student ON source_student.user_id = source_user.id
         LEFT JOIN teachers source_teacher ON source_teacher.user_id = source_user.id
         WHERE m.id = ?
           AND m.conversation_id = ?
           AND m.deleted_at IS NULL
           AND m.message_type <> "system"
           AND (
               c.conversation_type <> "group"
               OR m.created_at >= current_participant.joined_at
           )
           -- A cleared old message cannot be forwarded from the hidden history.
           AND (
               current_participant.cleared_at IS NULL
               OR m.created_at > current_participant.cleared_at
           )
         LIMIT 1'
    );
    $stmt->execute([$user_id, $message_id, $conversation_id]);
    $message = $stmt->fetch();

    return $message ?: null;
}

function forward_conversation_message(
    PDO $pdo,
    int $source_conversation_id,
    int $source_message_id,
    int $target_conversation_id,
    int $sender_id
): int {
    $source_message = get_forwardable_message($pdo, $source_conversation_id, $source_message_id, $sender_id);

    if (!$source_message) {
        throw new RuntimeException('This message cannot be forwarded.');
    }

    $target_conversation = get_conversation_details($pdo, $target_conversation_id, $sender_id);

    if (!$target_conversation || ($target_conversation['conversation_type'] ?? '') === 'system') {
        throw new RuntimeException('Choose a valid conversation to forward this message.');
    }

    // Forward a sticker by its stable catalog key instead of copying its image file.
    $is_sticker = $source_message['message_type'] === MESSAGE_TYPE_STICKER;
    $attachments = $is_sticker ? [] : get_message_attachment_records($pdo, (int) $source_message['id']);

    return send_conversation_message(
        $pdo,
        $target_conversation_id,
        $sender_id,
        (string) $source_message['body'],
        $is_sticker ? MESSAGE_TYPE_STICKER : MESSAGE_TYPE_TEXT,
        null,
        null,
        null,
        null,
        (string) ($source_message['forward_source_label'] ?? 'Someone'),
        (int) $source_message['id'],
        $attachments,
        $is_sticker ? (string) ($source_message['sticker_key'] ?? '') : null
    );
}
