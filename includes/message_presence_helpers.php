<?php

// Typing status is intentionally short-lived; stale rows are ignored by timestamp.
function ensure_message_presence_schema(PDO $pdo): void
{
    static $ensured = false;

    if ($ensured) {
        return;
    }

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS message_typing_status (
            conversation_id INT NOT NULL,
            user_id INT NOT NULL,
            is_typing TINYINT(1) NOT NULL DEFAULT 0,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (conversation_id, user_id),
            INDEX idx_message_typing_active (conversation_id, is_typing, updated_at),
            CONSTRAINT fk_message_typing_conversation
                FOREIGN KEY (conversation_id) REFERENCES conversations(id)
                ON DELETE CASCADE,
            CONSTRAINT fk_message_typing_user
                FOREIGN KEY (user_id) REFERENCES users(id)
                ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
    );

    $ensured = true;
}

function set_conversation_typing_status(PDO $pdo, int $conversation_id, int $user_id, bool $is_typing): void
{
    ensure_message_presence_schema($pdo);

    if (!is_conversation_participant($pdo, $conversation_id, $user_id)) {
        throw new RuntimeException('Conversation unavailable.');
    }

    $stmt = $pdo->prepare(
        'INSERT INTO message_typing_status (conversation_id, user_id, is_typing, updated_at)
         VALUES (?, ?, ?, CURRENT_TIMESTAMP)
         ON DUPLICATE KEY UPDATE
            is_typing = VALUES(is_typing),
            updated_at = CURRENT_TIMESTAMP'
    );
    $stmt->execute([$conversation_id, $user_id, $is_typing ? 1 : 0]);
}

function get_conversation_typing_users(PDO $pdo, int $conversation_id, int $user_id): array
{
    ensure_message_presence_schema($pdo);

    if (!is_conversation_participant($pdo, $conversation_id, $user_id)) {
        return [];
    }

    $stmt = $pdo->prepare(
        'SELECT
            status.user_id,
            COALESCE(s.name, t.name, u.username) AS display_name
         FROM message_typing_status status
         INNER JOIN conversation_participants participant
            ON participant.conversation_id = status.conversation_id
           AND participant.user_id = status.user_id
           AND participant.deleted_at IS NULL
         INNER JOIN users u
            ON u.id = status.user_id
           AND u.is_active = 1
         LEFT JOIN students s ON s.user_id = u.id
         LEFT JOIN teachers t ON t.user_id = u.id
         WHERE status.conversation_id = ?
           AND status.user_id <> ?
           AND status.is_typing = 1
           AND status.updated_at >= DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 6 SECOND)
         ORDER BY display_name
         LIMIT 4'
    );
    $stmt->execute([$conversation_id, $user_id]);

    return $stmt->fetchAll();
}

// Group read receipts are derived from each member's last_read_at; no per-message read table is needed.
function get_group_message_read_receipts(PDO $pdo, int $conversation_id, int $user_id): array
{
    if (!is_conversation_participant($pdo, $conversation_id, $user_id)) {
        return [];
    }

    $stmt = $pdo->prepare(
        'SELECT
            m.id AS message_id,
            COUNT(reader.user_id) AS read_count,
            GROUP_CONCAT(
                COALESCE(s.name, t.name, u.username)
                ORDER BY COALESCE(s.name, t.name, u.username)
                SEPARATOR ", "
            ) AS read_names,
            GROUP_CONCAT(
                CASE
                    WHEN reader.user_id IS NULL THEN NULL
                    ELSE CONCAT_WS(
                        "|",
                        reader.user_id,
                        COALESCE(s.name, t.name, u.username),
                        COALESCE(u.role, ""),
                        COALESCE(u.avatar, ""),
                        COALESCE(t.specialty, ""),
                        DATE_FORMAT(reader.last_read_at, "%b %e, %Y at %l:%i %p")
                    )
                END
                ORDER BY COALESCE(s.name, t.name, u.username)
                SEPARATOR "\n"
            ) AS read_user_rows
         FROM messages m
         INNER JOIN conversations c
            ON c.id = m.conversation_id
           AND c.conversation_type = "group"
         LEFT JOIN conversation_participants reader
            ON reader.conversation_id = m.conversation_id
           AND reader.user_id <> m.sender_id
           AND reader.deleted_at IS NULL
           AND reader.joined_at <= m.created_at
           AND reader.last_read_at IS NOT NULL
           AND reader.last_read_at >= m.created_at
         LEFT JOIN users u ON u.id = reader.user_id
         LEFT JOIN students s ON s.user_id = u.id
         LEFT JOIN teachers t ON t.user_id = u.id
         WHERE m.conversation_id = ?
           AND m.sender_id = ?
           AND m.deleted_at IS NULL
         GROUP BY m.id
         ORDER BY m.created_at ASC, m.id ASC
         LIMIT 200'
    );
    $stmt->execute([$conversation_id, $user_id]);

    $receipts = [];

    foreach ($stmt->fetchAll() as $row) {
        $read_users = [];

        foreach (explode("\n", (string) ($row['read_user_rows'] ?? '')) as $reader_row) {
            if (trim($reader_row) === '') {
                continue;
            }

            [$reader_id, $display_name, $role, $avatar, $specialty, $read_at] = array_pad(explode('|', $reader_row, 6), 6, '');
            $role_detail = $role === 'teacher' && $specialty !== ''
                ? ucfirst((string) $specialty) . ' teacher'
                : ucfirst((string) $role);
            $read_users[] = [
                'user_id' => (int) $reader_id,
                'display_name' => (string) $display_name,
                'role' => (string) $role,
                'role_detail' => $role_detail !== '' ? $role_detail : 'Member',
                'avatar' => message_avatar_path((string) $avatar, (string) $role),
                'read_at' => (string) $read_at,
            ];
        }

        $receipts[(int) $row['message_id']] = [
            'message_id' => (int) $row['message_id'],
            'read_count' => (int) $row['read_count'],
            'read_names' => trim((string) ($row['read_names'] ?? '')),
            'read_users' => $read_users,
        ];
    }

    return $receipts;
}

// Direct read ticks use the other participant's conversation-level read cursor.
function get_direct_message_read_receipts(PDO $pdo, int $conversation_id, int $user_id): array
{
    if (!is_conversation_participant($pdo, $conversation_id, $user_id)) {
        return [];
    }

    $stmt = $pdo->prepare(
        'SELECT
            m.id AS message_id,
            CASE
                WHEN reader.last_read_at IS NOT NULL
                 AND reader.last_read_at >= m.created_at
                THEN 1
                ELSE 0
            END AS is_read,
            DATE_FORMAT(reader.last_read_at, "%b %e, %Y at %l:%i %p") AS read_at
         FROM messages m
         INNER JOIN conversations c
            ON c.id = m.conversation_id
           AND c.conversation_type = "direct"
         LEFT JOIN conversation_participants reader
            ON reader.conversation_id = m.conversation_id
           AND reader.user_id <> m.sender_id
           AND reader.deleted_at IS NULL
         WHERE m.conversation_id = ?
           AND m.sender_id = ?
           AND m.deleted_at IS NULL
         ORDER BY m.created_at ASC, m.id ASC
         LIMIT 200'
    );
    $stmt->execute([$conversation_id, $user_id]);

    $receipts = [];

    foreach ($stmt->fetchAll() as $row) {
        $receipts[(int) $row['message_id']] = [
            'message_id' => (int) $row['message_id'],
            'is_read' => (bool) $row['is_read'],
            'read_at' => (string) ($row['read_at'] ?? ''),
        ];
    }

    return $receipts;
}

function group_message_read_receipt_for_message(PDO $pdo, int $message_id, int $conversation_id, int $user_id): array
{
    $receipts = get_group_message_read_receipts($pdo, $conversation_id, $user_id);

    return $receipts[$message_id] ?? [
        'message_id' => $message_id,
        'read_count' => 0,
        'read_names' => '',
        'read_users' => [],
    ];
}

function direct_message_read_receipt_for_message(PDO $pdo, int $message_id, int $conversation_id, int $user_id): array
{
    $receipts = get_direct_message_read_receipts($pdo, $conversation_id, $user_id);

    return $receipts[$message_id] ?? [
        'message_id' => $message_id,
        'is_read' => false,
        'read_at' => '',
    ];
}
