<?php

function ensure_message_contact_remark_schema(PDO $pdo): void
{
    static $ensured = false;

    if ($ensured) {
        return;
    }

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS message_contact_remarks (
            id INT NOT NULL AUTO_INCREMENT,
            conversation_id INT NOT NULL,
            owner_user_id INT NOT NULL,
            target_user_id INT NOT NULL,
            remark_name VARCHAR(80) DEFAULT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY uniq_message_contact_remark (conversation_id, owner_user_id, target_user_id),
            KEY idx_message_contact_remarks_owner (owner_user_id),
            KEY idx_message_contact_remarks_target (target_user_id),
            CONSTRAINT fk_message_contact_remarks_conversation
                FOREIGN KEY (conversation_id) REFERENCES conversations(id)
                ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT fk_message_contact_remarks_owner
                FOREIGN KEY (owner_user_id) REFERENCES users(id)
                ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT fk_message_contact_remarks_target
                FOREIGN KEY (target_user_id) REFERENCES users(id)
                ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );

    $ensured = true;
}

function normalize_message_contact_remark(?string $remark): ?string
{
    $remark = trim((string) $remark);

    if ($remark === '') {
        return null;
    }

    return mb_substr($remark, 0, 80);
}

function set_message_contact_remark(
    PDO $pdo,
    int $conversation_id,
    int $owner_user_id,
    int $target_user_id,
    ?string $remark
): void {
    ensure_message_contact_remark_schema($pdo);

    if ($owner_user_id === $target_user_id) {
        throw new InvalidArgumentException('You cannot set a remark for yourself.');
    }

    if (!is_conversation_participant($pdo, $conversation_id, $owner_user_id)) {
        throw new RuntimeException('Conversation unavailable.');
    }

    if (!is_conversation_participant($pdo, $conversation_id, $target_user_id)) {
        throw new RuntimeException('This user is not in the conversation.');
    }

    $remark = normalize_message_contact_remark($remark);

    if ($remark === null) {
        $stmt = $pdo->prepare(
            'DELETE FROM message_contact_remarks
             WHERE conversation_id = ?
               AND owner_user_id = ?
               AND target_user_id = ?'
        );
        $stmt->execute([$conversation_id, $owner_user_id, $target_user_id]);

        return;
    }

    $stmt = $pdo->prepare(
        'INSERT INTO message_contact_remarks
            (conversation_id, owner_user_id, target_user_id, remark_name)
         VALUES (?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE
            remark_name = VALUES(remark_name),
            updated_at = CURRENT_TIMESTAMP'
    );
    $stmt->execute([$conversation_id, $owner_user_id, $target_user_id, $remark]);
}
