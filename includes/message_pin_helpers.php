<?php

function ensure_message_pin_schema(PDO $pdo): void
{
    static $ensured = false;

    if ($ensured) {
        return;
    }

    $column_exists = static function (string $column) use ($pdo): bool {
        $stmt = $pdo->prepare(
            'SELECT COUNT(*)
             FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = "messages"
               AND COLUMN_NAME = ?'
        );
        $stmt->execute([$column]);

        return (int) $stmt->fetchColumn() > 0;
    };

    if (!$column_exists('pinned_at')) {
        $pdo->exec('ALTER TABLE messages ADD COLUMN pinned_at DATETIME NULL AFTER deleted_at');
    }

    if (!$column_exists('pinned_by')) {
        $pdo->exec('ALTER TABLE messages ADD COLUMN pinned_by INT NULL AFTER pinned_at');
    }

    $ensured = true;
}

function get_pinned_conversation_messages(PDO $pdo, int $conversation_id, int $user_id): array
{
    ensure_message_pin_schema($pdo);
    ensure_message_clear_schema($pdo);
    ensure_message_sticker_schema($pdo);

    if (!is_conversation_participant($pdo, $conversation_id, $user_id)) {
        return [];
    }

    // Include the key so pinned sticker previews can resolve their label.
    $stmt = $pdo->prepare(
        'SELECT
            m.id,
            m.body,
            m.message_type,
            m.sticker_key,
            m.sender_id,
            m.created_at,
            m.pinned_at,
            COALESCE(s.name, t.name, u.username, "Someone") AS sender_display_name
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
         WHERE m.conversation_id = ?
           AND m.pinned_at IS NOT NULL
           AND m.deleted_at IS NULL
           AND (
               c.conversation_type <> "group"
               OR m.created_at >= current_participant.joined_at
           )
           -- Hide pinned messages that are older than this participant clear point.
           AND (
               current_participant.cleared_at IS NULL
               OR m.created_at > current_participant.cleared_at
           )
         ORDER BY m.pinned_at DESC, m.id DESC
         LIMIT 5'
    );
    $stmt->execute([$user_id, $conversation_id]);

    return hydrate_message_attachments($pdo, $stmt->fetchAll());
}

function set_conversation_message_pinned(
    PDO $pdo,
    int $message_id,
    int $conversation_id,
    int $user_id,
    bool $is_pinned
): int {
    ensure_message_pin_schema($pdo);
    ensure_message_clear_schema($pdo);

    if (!is_conversation_participant($pdo, $conversation_id, $user_id)) {
        throw new RuntimeException('You cannot update this message.');
    }

    $message_stmt = $pdo->prepare(
        'SELECT m.id
         FROM messages m
         INNER JOIN conversation_participants current_participant
            ON current_participant.conversation_id = m.conversation_id
           AND current_participant.user_id = ?
           AND current_participant.deleted_at IS NULL
         INNER JOIN conversations c
            ON c.id = m.conversation_id
         WHERE m.id = ?
           AND m.conversation_id = ?
           AND m.deleted_at IS NULL
           AND (
               c.conversation_type <> "group"
               OR m.created_at >= current_participant.joined_at
           )
           -- Prevent pin changes for messages this participant already cleared.
           AND (
               current_participant.cleared_at IS NULL
               OR m.created_at > current_participant.cleared_at
           )
         LIMIT 1'
    );
    $message_stmt->execute([$user_id, $message_id, $conversation_id]);

    if (!$message_stmt->fetchColumn()) {
        throw new RuntimeException('This message is not available.');
    }

    $stmt = $pdo->prepare(
        'UPDATE messages
         SET pinned_at = ?,
             pinned_by = ?
         WHERE id = ?
           AND conversation_id = ?'
    );
    $stmt->execute([
        $is_pinned ? date('Y-m-d H:i:s') : null,
        $is_pinned ? $user_id : null,
        $message_id,
        $conversation_id,
    ]);

    return $conversation_id;
}
