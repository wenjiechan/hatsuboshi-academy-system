<?php

require_once __DIR__ . '/notifications_helpers.php';

// Group conversations use the conversation row as the source of truth for name/avatar/creator.
function group_conversation_name(PDO $pdo, int $conversation_id): string
{
    $stmt = $pdo->prepare(
        'SELECT group_name
         FROM conversations
         WHERE id = ?
           AND conversation_type = "group"
         LIMIT 1'
    );
    $stmt->execute([$conversation_id]);
    $group_name = trim((string) $stmt->fetchColumn());

    return $group_name !== '' ? $group_name : 'Group chat';
}

function uploaded_group_avatar_path(?array $file): ?string
{
    if (!$file || !isset($file['error']) || (int) $file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ((int) $file['error'] !== UPLOAD_ERR_OK) {
        throw new InvalidArgumentException('The group avatar could not be uploaded.');
    }

    if ((int) ($file['size'] ?? 0) > 2 * 1024 * 1024) {
        throw new InvalidArgumentException('Group avatar cannot exceed 2 MB.');
    }

    $tmp_name = (string) ($file['tmp_name'] ?? '');

    if ($tmp_name === '' || !is_uploaded_file($tmp_name)) {
        throw new InvalidArgumentException('Choose a valid group avatar image.');
    }

    $image_info = getimagesize($tmp_name);
    $mime_type = is_array($image_info) ? (string) ($image_info['mime'] ?? '') : '';
    $extensions = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    if (!isset($extensions[$mime_type])) {
        throw new InvalidArgumentException('Group avatar must be a JPG, PNG, or WebP image.');
    }

    $upload_dir = __DIR__ . '/../assets/images/avatars/groups';

    if (!is_dir($upload_dir) && !mkdir($upload_dir, 0775, true) && !is_dir($upload_dir)) {
        throw new RuntimeException('Unable to prepare the group avatar folder.');
    }

    $filename = 'group_' . bin2hex(random_bytes(8)) . '.' . $extensions[$mime_type];
    $target = $upload_dir . DIRECTORY_SEPARATOR . $filename;

    if (!move_uploaded_file($tmp_name, $target)) {
        throw new RuntimeException('Unable to save the group avatar.');
    }

    return '/gakumas-sms/assets/images/avatars/groups/' . $filename;
}

// Only delete avatars uploaded for groups; default/shared avatar assets must never be removed.
function delete_unused_uploaded_group_avatar(PDO $pdo, ?string $avatar_path): void
{
    $avatar_path = trim((string) $avatar_path);
    $prefix = '/gakumas-sms/assets/images/avatars/groups/';

    if ($avatar_path === '' || !str_starts_with($avatar_path, $prefix)) {
        return;
    }

    $usage_stmt = $pdo->prepare(
        'SELECT COUNT(*)
         FROM conversations
         WHERE group_avatar = ?'
    );
    $usage_stmt->execute([$avatar_path]);

    if ((int) $usage_stmt->fetchColumn() > 0) {
        return;
    }

    $base_dir = realpath(__DIR__ . '/../assets/images/avatars/groups');

    if (!$base_dir) {
        return;
    }

    $filename = basename(rawurldecode(substr($avatar_path, strlen($prefix))));
    $candidate = realpath($base_dir . DIRECTORY_SEPARATOR . $filename);

    if (
        $candidate !== false
        && str_starts_with($candidate, $base_dir . DIRECTORY_SEPARATOR)
        && is_file($candidate)
    ) {
        @unlink($candidate);
    }
}

function ensure_message_group_schema(PDO $pdo): void
{
    static $ensured = false;

    if ($ensured) {
        return;
    }

    $column_exists = static function (string $table, string $column) use ($pdo): bool {
        $stmt = $pdo->prepare(
            'SELECT COUNT(*)
             FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = ?
               AND COLUMN_NAME = ?'
        );
        $stmt->execute([$table, $column]);

        return (int) $stmt->fetchColumn() > 0;
    };

    try {
        $pdo->exec(
            "ALTER TABLE conversations
             MODIFY conversation_type enum('direct','system','group') NOT NULL DEFAULT 'direct'"
        );
    } catch (PDOException $exception) {
        // The enum may already include group on newer databases.
    }

    $conversation_columns = [
        'group_name' => 'ALTER TABLE conversations ADD COLUMN group_name VARCHAR(100) NULL AFTER direct_key',
        'group_avatar' => 'ALTER TABLE conversations ADD COLUMN group_avatar VARCHAR(255) NULL AFTER group_name',
        'created_by' => 'ALTER TABLE conversations ADD COLUMN created_by INT NULL AFTER group_avatar',
    ];

    foreach ($conversation_columns as $column => $sql) {
        if (!$column_exists('conversations', $column)) {
            $pdo->exec($sql);
        }
    }

    if (!$column_exists('conversation_participants', 'is_group_admin')) {
        $pdo->exec(
            'ALTER TABLE conversation_participants
             ADD COLUMN is_group_admin TINYINT(1) NOT NULL DEFAULT 0 AFTER is_muted'
        );
    }

    $ensured = true;
}

// Students may create groups, but they cannot add admins.
function get_group_message_contacts(PDO $pdo, int $current_user_id, string $current_role): array
{
    ensure_message_group_schema($pdo);

    $role_sql = $current_role === 'student'
        ? 'AND u.role IN ("student", "producer", "teacher")'
        : 'AND u.role IN ("admin", "student", "producer", "teacher")';

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
           ' . $role_sql . '
         ORDER BY
            FIELD(u.role, "admin", "student", "producer", "teacher"),
            display_name'
    );
    $stmt->execute([$current_user_id]);

    return $stmt->fetchAll();
}

function get_group_candidate_users(PDO $pdo, array $user_ids, int $current_user_id, string $current_role): array
{
    $user_ids = array_values(array_unique(array_filter(array_map('intval', $user_ids))));

    if (empty($user_ids)) {
        return [];
    }

    $placeholders = implode(',', array_fill(0, count($user_ids), '?'));
    $role_sql = $current_role === 'student'
        ? 'AND role IN ("student", "producer", "teacher")'
        : 'AND role IN ("admin", "student", "producer", "teacher")';

    $stmt = $pdo->prepare(
        'SELECT id, username, role
         FROM users
         WHERE id IN (' . $placeholders . ')
           AND id <> ?
           AND is_active = 1
           ' . $role_sql
    );
    $stmt->execute([...$user_ids, $current_user_id]);

    return $stmt->fetchAll();
}

function create_group_conversation(
    PDO $pdo,
    int $creator_id,
    string $creator_role,
    string $group_name,
    array $member_ids,
    ?array $group_avatar_file = null
): int {
    ensure_message_group_schema($pdo);

    $group_name = trim($group_name);

    if ($group_name === '') {
        throw new InvalidArgumentException('Enter a group name.');
    }

    if (mb_strlen($group_name) > 100) {
        throw new InvalidArgumentException('Group name cannot exceed 100 characters.');
    }

    $members = get_group_candidate_users($pdo, $member_ids, $creator_id, $creator_role);
    $valid_member_ids = array_map(static fn(array $member): int => (int) $member['id'], $members);

    if (count($valid_member_ids) < 2) {
        throw new InvalidArgumentException('Choose at least two group members.');
    }

    $group_avatar = uploaded_group_avatar_path($group_avatar_file);

    try {
        $pdo->beginTransaction();

        $conversation_stmt = $pdo->prepare(
            'INSERT INTO conversations (conversation_type, group_name, group_avatar, created_by)
             VALUES ("group", ?, ?, ?)'
        );
        $conversation_stmt->execute([$group_name, $group_avatar, $creator_id]);
        $conversation_id = (int) $pdo->lastInsertId();

        $participant_stmt = $pdo->prepare(
            'INSERT INTO conversation_participants (conversation_id, user_id, is_group_admin)
             VALUES (?, ?, ?)'
        );

        $participant_stmt->execute([$conversation_id, $creator_id, 1]);

        foreach ($valid_member_ids as $member_id) {
            $participant_stmt->execute([$conversation_id, $member_id, 0]);
        }

        $pdo->commit();
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        throw $exception;
    }

    send_group_system_message(
        $pdo,
        $conversation_id,
        $creator_id,
        message_user_display_name($pdo, $creator_id) . ' created the group.'
    );

    return $conversation_id;
}

// The creator is the only group admin for now.
function update_group_conversation_details(
    PDO $pdo,
    int $conversation_id,
    int $actor_id,
    string $group_name,
    ?array $group_avatar_file
): void {
    ensure_message_group_schema($pdo);

    if (!is_group_conversation_admin($pdo, $conversation_id, $actor_id)) {
        throw new RuntimeException('Only the group admin can update group details.');
    }

    $group_name = trim($group_name);

    if ($group_name === '') {
        throw new InvalidArgumentException('Enter a group name.');
    }

    if (mb_strlen($group_name) > 100) {
        throw new InvalidArgumentException('Group name cannot exceed 100 characters.');
    }

    $stmt = $pdo->prepare(
        'SELECT group_name, group_avatar
         FROM conversations
         WHERE id = ?
           AND conversation_type = "group"
         LIMIT 1'
    );
    $stmt->execute([$conversation_id]);
    $current = $stmt->fetch();

    if (!$current) {
        throw new RuntimeException('Group conversation unavailable.');
    }

    $old_name = trim((string) ($current['group_name'] ?? ''));
    $old_avatar = trim((string) ($current['group_avatar'] ?? ''));
    $uploaded_avatar = uploaded_group_avatar_path($group_avatar_file);
    $group_avatar = $uploaded_avatar ?? ($old_avatar !== '' ? $old_avatar : null);
    $new_avatar = trim((string) $group_avatar);

    if ($old_name === $group_name && $old_avatar === $new_avatar) {
        return;
    }

    $update_stmt = $pdo->prepare(
        'UPDATE conversations
         SET group_name = ?,
             group_avatar = ?
         WHERE id = ?
           AND conversation_type = "group"'
    );
    $update_stmt->execute([$group_name, $group_avatar, $conversation_id]);

    $actor_name = message_user_display_name($pdo, $actor_id);

    if ($uploaded_avatar !== null && $old_avatar !== $new_avatar) {
        delete_unused_uploaded_group_avatar($pdo, $old_avatar);
    }

    if ($old_name !== $group_name) {
        send_group_system_message(
            $pdo,
            $conversation_id,
            $actor_id,
            $actor_name . ' renamed the group to ' . $group_name . '.'
        );
    }

    if ($old_avatar !== $new_avatar) {
        send_group_system_message(
            $pdo,
            $conversation_id,
            $actor_id,
            $actor_name . ' updated the group avatar.'
        );
    }
}

function get_group_members(PDO $pdo, int $conversation_id, int $user_id): array
{
    ensure_message_group_schema($pdo);
    ensure_message_contact_remark_schema($pdo);

    if (!is_conversation_participant($pdo, $conversation_id, $user_id)) {
        return [];
    }

    $stmt = $pdo->prepare(
        'SELECT
            participant.user_id,
            CASE
                WHEN c.created_by = participant.user_id THEN 1
                ELSE 0
            END AS is_group_admin,
            participant.joined_at,
            u.username,
            u.role,
            u.avatar,
            remark.remark_name,
            COALESCE(s.name, t.name, u.username) AS real_display_name,
            COALESCE(NULLIF(remark.remark_name, ""), s.name, t.name, u.username) AS display_name,
            t.specialty
         FROM conversation_participants participant
         INNER JOIN conversations c
            ON c.id = participant.conversation_id
           AND c.conversation_type = "group"
         INNER JOIN users u
            ON u.id = participant.user_id
           AND u.is_active = 1
         LEFT JOIN students s ON s.user_id = u.id
         LEFT JOIN teachers t ON t.user_id = u.id
         LEFT JOIN message_contact_remarks remark
            ON remark.conversation_id = participant.conversation_id
           AND remark.owner_user_id = ?
           AND remark.target_user_id = participant.user_id
         WHERE participant.conversation_id = ?
           AND participant.deleted_at IS NULL
         ORDER BY is_group_admin DESC, display_name'
    );
    $stmt->execute([$user_id, $conversation_id]);

    return $stmt->fetchAll();
}

function is_group_conversation_admin(PDO $pdo, int $conversation_id, int $user_id): bool
{
    ensure_message_group_schema($pdo);

    $stmt = $pdo->prepare(
        'SELECT 1
         FROM conversation_participants participant
         INNER JOIN conversations c
            ON c.id = participant.conversation_id
           AND c.conversation_type = "group"
           AND c.created_by = participant.user_id
         WHERE participant.conversation_id = ?
           AND participant.user_id = ?
           AND participant.deleted_at IS NULL
         LIMIT 1'
    );
    $stmt->execute([$conversation_id, $user_id]);

    return (bool) $stmt->fetchColumn();
}

function is_group_conversation_participant(PDO $pdo, int $conversation_id, int $user_id): bool
{
    ensure_message_group_schema($pdo);

    $stmt = $pdo->prepare(
        'SELECT 1
         FROM conversation_participants participant
         INNER JOIN conversations c
            ON c.id = participant.conversation_id
           AND c.conversation_type = "group"
         WHERE participant.conversation_id = ?
           AND participant.user_id = ?
           AND participant.deleted_at IS NULL
         LIMIT 1'
    );
    $stmt->execute([$conversation_id, $user_id]);

    return (bool) $stmt->fetchColumn();
}

function group_active_admin_count(PDO $pdo, int $conversation_id): int
{
    $stmt = $pdo->prepare(
        'SELECT COUNT(*)
         FROM conversation_participants participant
         INNER JOIN conversations c
            ON c.id = participant.conversation_id
           AND c.conversation_type = "group"
           AND c.created_by = participant.user_id
         WHERE participant.conversation_id = ?
           AND participant.deleted_at IS NULL'
    );
    $stmt->execute([$conversation_id]);

    return (int) $stmt->fetchColumn();
}

function message_user_display_name(PDO $pdo, int $user_id): string
{
    $stmt = $pdo->prepare(
        'SELECT COALESCE(s.name, t.name, u.username) AS display_name
         FROM users u
         LEFT JOIN students s ON s.user_id = u.id
         LEFT JOIN teachers t ON t.user_id = u.id
         WHERE u.id = ?
         LIMIT 1'
    );
    $stmt->execute([$user_id]);
    $display_name = $stmt->fetchColumn();

    return $display_name ? (string) $display_name : 'Someone';
}

function send_group_system_message(PDO $pdo, int $conversation_id, int $actor_id, string $body): int
{
    return send_conversation_message(
        $pdo,
        $conversation_id,
        $actor_id,
        $body,
        MESSAGE_TYPE_SYSTEM
    );
}

function add_group_members(
    PDO $pdo,
    int $conversation_id,
    int $actor_id,
    string $actor_role,
    array $member_ids
): int {
    ensure_message_group_schema($pdo);

    if (!is_group_conversation_admin($pdo, $conversation_id, $actor_id)) {
        throw new RuntimeException('Only a group admin can add members.');
    }

    $members = get_group_candidate_users($pdo, $member_ids, $actor_id, $actor_role);

    if (empty($members)) {
        throw new InvalidArgumentException('Choose at least one available member.');
    }

    $inserted = 0;
    $actor_name = message_user_display_name($pdo, $actor_id);
    $group_name = group_conversation_name($pdo, $conversation_id);
    $previous_participant_stmt = $pdo->prepare(
        'SELECT deleted_at
         FROM conversation_participants
         WHERE conversation_id = ?
           AND user_id = ?
         LIMIT 1'
    );
    // Re-added users start a fresh membership window and do not regain old group history.
    $participant_stmt = $pdo->prepare(
        'INSERT INTO conversation_participants (conversation_id, user_id, is_group_admin)
         VALUES (?, ?, 0)
         ON DUPLICATE KEY UPDATE
            deleted_at = NULL,
            is_archived = 0,
            is_group_admin = 0,
            joined_at = CURRENT_TIMESTAMP,
            last_read_at = NULL'
    );

    foreach ($members as $member) {
        $member_id = (int) $member['id'];

        if (is_conversation_participant($pdo, $conversation_id, $member_id)) {
            continue;
        }

        $previous_participant_stmt->execute([$conversation_id, $member_id]);
        $was_member_before = $previous_participant_stmt->fetchColumn() !== false;
        $member_name = message_user_display_name($pdo, $member_id);

        $participant_stmt->execute([$conversation_id, $member_id]);
        $inserted++;
        $system_message_id = send_group_system_message(
            $pdo,
            $conversation_id,
            $actor_id,
            $was_member_before
                ? $actor_name . ' added ' . $member_name . ' back to the group.'
                : $actor_name . ' added ' . $member_name . ' to the group.'
        );

        create_notification(
            $pdo,
            $member_id,
            NOTIFICATION_TYPE_NEW_MESSAGE,
            $was_member_before ? 'Added back to group' : 'Added to group',
            $was_member_before
                ? $actor_name . ' added you back to ' . $group_name . '.'
                : $actor_name . ' added you to ' . $group_name . '.',
            'message',
            $system_message_id,
            '/gakumas-sms/messages/view.php?id=' . $conversation_id,
            'group_add:' . $conversation_id . ':' . $member_id . ':' . $system_message_id
        );
    }

    return $inserted;
}

function remove_group_member(PDO $pdo, int $conversation_id, int $actor_id, int $member_id): void
{
    ensure_message_group_schema($pdo);
    $actor_name = message_user_display_name($pdo, $actor_id);
    $member_name = message_user_display_name($pdo, $member_id);
    $group_name = group_conversation_name($pdo, $conversation_id);

    if (!is_group_conversation_admin($pdo, $conversation_id, $actor_id)) {
        throw new RuntimeException('Only a group admin can remove members.');
    }

    if ($actor_id === $member_id) {
        throw new InvalidArgumentException('Use leave group to remove yourself.');
    }

    if (!is_conversation_participant($pdo, $conversation_id, $member_id)) {
        throw new RuntimeException('This member is not in the group.');
    }

    if (is_group_conversation_admin($pdo, $conversation_id, $member_id) && group_active_admin_count($pdo, $conversation_id) <= 1) {
        throw new RuntimeException('The last group admin cannot be removed.');
    }

    $stmt = $pdo->prepare(
        'UPDATE conversation_participants
         SET deleted_at = NOW(),
             is_archived = 1
         WHERE conversation_id = ?
           AND user_id = ?
           AND deleted_at IS NULL'
    );
    $stmt->execute([$conversation_id, $member_id]);

    $system_message_id = send_group_system_message(
        $pdo,
        $conversation_id,
        $actor_id,
        $actor_name . ' removed ' . $member_name . ' from the group.'
    );

    create_notification(
        $pdo,
        $member_id,
        NOTIFICATION_TYPE_NEW_MESSAGE,
        'Removed from group',
        $actor_name . ' removed you from ' . $group_name . '.',
        'message',
        $system_message_id,
        '/gakumas-sms/messages/inbox.php',
        'group_remove:' . $conversation_id . ':' . $member_id . ':' . $system_message_id
    );
}

function leave_group_conversation(PDO $pdo, int $conversation_id, int $user_id): void
{
    ensure_message_group_schema($pdo);

    if (!is_group_conversation_participant($pdo, $conversation_id, $user_id)) {
        throw new RuntimeException('You are not a member of this group.');
    }

    if (is_group_conversation_admin($pdo, $conversation_id, $user_id) && group_active_admin_count($pdo, $conversation_id) <= 1) {
        throw new RuntimeException('You cannot leave while you are the last group admin.');
    }

    send_group_system_message(
        $pdo,
        $conversation_id,
        $user_id,
        message_user_display_name($pdo, $user_id) . ' left the group.'
    );

    $stmt = $pdo->prepare(
        'UPDATE conversation_participants
         SET deleted_at = NOW(),
             is_archived = 1
         WHERE conversation_id = ?
           AND user_id = ?
           AND deleted_at IS NULL'
    );
    $stmt->execute([$conversation_id, $user_id]);
}

// Mentions target active members only; removed/left users should not be notified.
function get_group_message_mention_targets(PDO $pdo, int $conversation_id, int $sender_id, string $body): array
{
    if (!is_group_conversation_participant($pdo, $conversation_id, $sender_id)) {
        return [
            'everyone' => false,
            'user_ids' => [],
        ];
    }

    $mentions_everyone = preg_match('/(^|[\s(])@everyone(?=$|[\s.,!?;:)\]])/i', $body) === 1;
    $stmt = $pdo->prepare(
        'SELECT
            participant.user_id,
            COALESCE(s.name, t.name, u.username) AS display_name
         FROM conversation_participants participant
         INNER JOIN users u
            ON u.id = participant.user_id
           AND u.is_active = 1
         LEFT JOIN students s ON s.user_id = u.id
         LEFT JOIN teachers t ON t.user_id = u.id
         WHERE participant.conversation_id = ?
           AND participant.user_id <> ?
           AND participant.deleted_at IS NULL'
    );
    $stmt->execute([$conversation_id, $sender_id]);
    $mentioned_user_ids = [];

    foreach ($stmt->fetchAll() as $member) {
        $display_name = trim((string) $member['display_name']);

        if ($display_name === '') {
            continue;
        }

        $pattern = '/(^|[\s(])@' . preg_quote($display_name, '/') . '(?=$|[\s.,!?;:)\]])/iu';

        if (preg_match($pattern, $body) === 1) {
            $mentioned_user_ids[] = (int) $member['user_id'];
        }
    }

    return [
        'everyone' => $mentions_everyone,
        'user_ids' => array_values(array_unique($mentioned_user_ids)),
    ];
}
