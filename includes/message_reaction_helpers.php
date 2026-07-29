<?php

const MESSAGE_REACTION_EMOJIS = ['👍', '❤️', '😂', '😢', '🙏', '🔥', '✨'];

const MESSAGE_REACTION_MORE_EMOJIS = [
    '👀', '💬', '❓', '❗', '⚠️', '🚫', '👌', '🤝',
    '👎', '❌', '✅', '💯', '👏', '💪', '💡', '🙋',
    '😮', '😡', '😭', '🤣', '😅', '🎉', '🙌', '🤩',
    '🥺', '😊', '🥰', '💕', '😳', '😏', '😉', '😎',
    '😇', '😬', '🤭', '💭', '😤', '😒', '🙄', '🤔',
    '😵', '😆', '😝', '😈', '🤗', '🌷', '🌸', '🎀',
    '💫', '🌟', '👑', '☀️', '💸', '📌', '💤', '😴',
    '☕', '😌', '💅', '🥱', '😐', '🐺', '🍡', '✌️'
];

function ensure_message_reaction_schema(PDO $pdo): void
{
    static $ensured = false;

    if ($ensured) {
        return;
    }

    $table_stmt = $pdo->prepare(
        'SELECT 1
         FROM information_schema.TABLES
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_NAME = "message_reactions"
         LIMIT 1'
    );
    $table_stmt->execute();

    if (!$table_stmt->fetchColumn()) {
        $pdo->exec(
            'CREATE TABLE message_reactions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                message_id INT NOT NULL,
                user_id INT NOT NULL,
                emoji VARCHAR(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uniq_message_reaction_user (message_id, user_id),
                KEY idx_message_reactions_message (message_id),
                KEY idx_message_reactions_updated (updated_at),
                CONSTRAINT fk_message_reactions_message
                    FOREIGN KEY (message_id) REFERENCES messages (id)
                    ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT fk_message_reactions_user
                    FOREIGN KEY (user_id) REFERENCES users (id)
                    ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    $column_stmt = $pdo->prepare(
        'SELECT CHARACTER_MAXIMUM_LENGTH, COLLATION_NAME
         FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_NAME = "message_reactions"
           AND COLUMN_NAME = "emoji"
         LIMIT 1'
    );
    $column_stmt->execute();
    $emoji_column = $column_stmt->fetch();

    if (
        $emoji_column
        && (
            (int) ($emoji_column['CHARACTER_MAXIMUM_LENGTH'] ?? 0) < 32
            || (string) ($emoji_column['COLLATION_NAME'] ?? '') !== 'utf8mb4_bin'
        )
    ) {
        $pdo->exec(
            'ALTER TABLE message_reactions
             MODIFY emoji VARCHAR(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL'
        );
    }

    $ensured = true;
}

function message_reaction_options(): array
{
    return array_values(array_unique(array_merge(MESSAGE_REACTION_EMOJIS, MESSAGE_REACTION_MORE_EMOJIS)));
}

function normalize_message_reaction_emoji(string $emoji): string
{
    $emoji = trim($emoji);

    if (!in_array($emoji, message_reaction_options(), true)) {
        throw new InvalidArgumentException('Choose one of the available reactions.');
    }

    return $emoji;
}

function get_message_reaction_summaries(
    PDO $pdo,
    int $conversation_id,
    int $user_id,
    ?array $message_ids = null
): array {
    ensure_message_reaction_schema($pdo);
    ensure_message_clear_schema($pdo);

    if (!is_conversation_participant($pdo, $conversation_id, $user_id)) {
        return [];
    }

    $message_ids = $message_ids !== null
        ? array_values(array_unique(array_filter(array_map('intval', $message_ids))))
        : null;

    if ($message_ids !== null && empty($message_ids)) {
        return [];
    }

    $message_id_sql = '';

    if ($message_ids !== null) {
        $message_id_sql = ' AND m.id IN (' . implode(',', array_fill(0, count($message_ids), '?')) . ')';
    }

    $stmt = $pdo->prepare(
        'SELECT
            reaction.message_id,
            reaction.emoji,
            COUNT(*) AS reaction_count,
            MAX(reaction.user_id = ?) AS reacted_by_current_user,
            GROUP_CONCAT(
                COALESCE(student.name, teacher.name, reacting_user.username)
                ORDER BY COALESCE(student.name, teacher.name, reacting_user.username)
                SEPARATOR ", "
            ) AS user_names
         FROM message_reactions reaction
         INNER JOIN messages m
            ON m.id = reaction.message_id
         INNER JOIN conversation_participants current_participant
            ON current_participant.conversation_id = m.conversation_id
           AND current_participant.user_id = ?
           AND current_participant.deleted_at IS NULL
         INNER JOIN conversations c
            ON c.id = m.conversation_id
         INNER JOIN users reacting_user
            ON reacting_user.id = reaction.user_id
         LEFT JOIN students student
            ON student.user_id = reacting_user.id
         LEFT JOIN teachers teacher
            ON teacher.user_id = reacting_user.id
         WHERE m.conversation_id = ?
           AND m.deleted_at IS NULL
           AND m.message_type <> "system"
           AND (
               c.conversation_type <> "group"
               OR m.created_at >= current_participant.joined_at
           )
           AND (
               current_participant.cleared_at IS NULL
               OR m.created_at > current_participant.cleared_at
           )
           ' . $message_id_sql . '
         GROUP BY reaction.message_id, reaction.emoji COLLATE utf8mb4_bin
         ORDER BY MIN(reaction.created_at), reaction.emoji COLLATE utf8mb4_bin'
    );

    $params = [$user_id, $user_id, $conversation_id];

    if ($message_ids !== null) {
        array_push($params, ...$message_ids);
    }

    $stmt->execute($params);
    $summaries = [];

    foreach ($stmt->fetchAll() as $row) {
        $message_id = (int) $row['message_id'];
        $summaries[$message_id][] = [
            'emoji' => (string) $row['emoji'],
            'count' => (int) $row['reaction_count'],
            'reacted' => (bool) $row['reacted_by_current_user'],
            'user_names' => (string) ($row['user_names'] ?? ''),
        ];
    }

    return $summaries;
}

function set_message_reaction(
    PDO $pdo,
    int $message_id,
    int $conversation_id,
    int $user_id,
    string $emoji
): array {
    ensure_message_reaction_schema($pdo);
    ensure_message_clear_schema($pdo);
    $emoji = normalize_message_reaction_emoji($emoji);

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
           AND m.message_type <> "system"
           AND (
               c.conversation_type <> "group"
               OR m.created_at >= current_participant.joined_at
           )
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

    $current_stmt = $pdo->prepare(
        'SELECT emoji
         FROM message_reactions
         WHERE message_id = ?
           AND user_id = ?
         LIMIT 1'
    );
    $current_stmt->execute([$message_id, $user_id]);
    $current_emoji = $current_stmt->fetchColumn();

    if ($current_emoji === $emoji) {
        $delete_stmt = $pdo->prepare(
            'DELETE FROM message_reactions
             WHERE message_id = ?
               AND user_id = ?'
        );
        $delete_stmt->execute([$message_id, $user_id]);
    } else {
        $upsert_stmt = $pdo->prepare(
            'INSERT INTO message_reactions (message_id, user_id, emoji)
             VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE
                emoji = VALUES(emoji),
                updated_at = CURRENT_TIMESTAMP'
        );
        $upsert_stmt->execute([$message_id, $user_id, $emoji]);
    }

    return get_message_reaction_summaries($pdo, $conversation_id, $user_id, [$message_id])[$message_id] ?? [];
}
