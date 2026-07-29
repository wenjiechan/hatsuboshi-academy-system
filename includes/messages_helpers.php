<?php
// Separate normal messages from special system messages
const MESSAGE_TYPE_TEXT = 'text';
const MESSAGE_TYPE_BIRTHDAY = 'birthday';
const MESSAGE_TYPE_PRODUCER_ADD_REQUEST = 'producer_add_request';
const MESSAGE_TYPE_PRODUCER_REMOVE_REQUEST = 'producer_remove_request';
const MESSAGE_TYPE_SYSTEM = 'system';
const MESSAGE_TYPE_STICKER = 'sticker';

require_once __DIR__ . '/message_group_helpers.php';

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
// Generate the same direct conversation key no matter which user starts the chat.
function message_direct_key(int $first_user_id, int $second_user_id): string
{
    $user_ids = [$first_user_id, $second_user_id];
    sort($user_ids, SORT_NUMERIC);

    return implode(':', $user_ids);
}

// Checks whether a direct conversation already exists between two users
function find_direct_conversation(PDO $pdo, int $first_user_id, int $second_user_id): ?int
{
    ensure_message_group_schema($pdo);

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

// Create a new direct conversation between two active users.
// Use a transaction so the conversation and both participant rows are created together.
function create_direct_conversation(PDO $pdo, int $first_user_id, int $second_user_id): int
{
    ensure_message_group_schema($pdo);

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
    ensure_message_group_schema($pdo);

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
    ensure_message_group_schema($pdo);

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

// Check whether the logged-in user is still an active participant in the conversation.
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

// Adds the timestamp used to hide old messages for one participant only.
function ensure_message_clear_schema(PDO $pdo): void
{
    static $ensured = false;

    if ($ensured) {
        return;
    }

    $column_stmt = $pdo->prepare(
        'SELECT 1
         FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_NAME = "conversation_participants"
           AND COLUMN_NAME = "cleared_at"
         LIMIT 1'
    );
    $column_stmt->execute();

    if (!$column_stmt->fetchColumn()) {
        $pdo->exec('ALTER TABLE conversation_participants ADD COLUMN cleared_at DATETIME NULL AFTER last_read_at');
    }

    $ensured = true;
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

 //Get one active user who can receive messages.
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
    ensure_message_group_schema($pdo);

    $stmt = $pdo->prepare(
        'SELECT
            c.id,
            c.conversation_type,
            c.group_name,
            c.group_avatar,
            c.created_by,
            c.created_at,
            c.updated_at,
            (
                SELECT COUNT(*)
                FROM conversation_participants member_count
                WHERE member_count.conversation_id = c.id
                  AND member_count.deleted_at IS NULL
            ) AS member_count,
            current_participant.is_muted,
            current_participant.is_archived,
            CASE
                WHEN c.conversation_type = "group"
                 AND c.created_by = current_participant.user_id
                THEN 1
                ELSE current_participant.is_group_admin
            END AS is_group_admin,
            other_user.id AS other_user_id,
            other_user.username AS other_username,
            CASE
                WHEN c.conversation_type = "group" THEN "group"
                ELSE other_user.role
            END AS other_role,
            CASE
                WHEN c.conversation_type = "group" THEN c.group_avatar
                ELSE other_user.avatar
            END AS other_avatar,
            CASE
                WHEN c.conversation_type = "group" THEN c.group_name
                WHEN c.conversation_type = "system" AND other_user.role = "admin"
                THEN "Admin"
                ELSE COALESCE(student.name, teacher.name, other_user.username)
            END AS other_display_name,
            CASE
                WHEN c.conversation_type = "group" THEN NULL
                ELSE teacher.specialty
            END AS other_specialty
         FROM conversations c
         INNER JOIN conversation_participants current_participant
            ON current_participant.conversation_id = c.id
           AND current_participant.user_id = ?
           AND current_participant.deleted_at IS NULL
         LEFT JOIN conversation_participants other_participant
            ON other_participant.conversation_id = c.id
           AND other_participant.user_id <> current_participant.user_id
           AND other_participant.deleted_at IS NULL
           AND c.conversation_type <> "group"
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
// Return recipients who should receive a notification for a new message.
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

// Get the current user's conversations, including unread counts and latest message preview.
// Archived conversations are excluded unless $include_archived is true.
// Newest conversations appear first.
function get_user_conversations(PDO $pdo, int $user_id, bool $include_archived = false): array
{
    ensure_message_group_schema($pdo);
    ensure_message_clear_schema($pdo);
    ensure_message_attachment_schema($pdo);
    ensure_message_sticker_schema($pdo);

    $archive_sql = $include_archived ? '' : 'AND current_participant.is_archived = 0';

    $stmt = $pdo->prepare(
        'SELECT
            c.id,
            c.conversation_type,
            c.group_name,
            c.group_avatar,
            c.updated_at,
            current_participant.is_archived,
            current_participant.is_muted,
            current_participant.last_read_at,
            (
                SELECT COUNT(*)
                FROM conversation_participants group_member_count
                WHERE group_member_count.conversation_id = c.id
                  AND group_member_count.deleted_at IS NULL
            ) AS member_count,
            other_user.id AS other_user_id,
            other_user.username AS other_username,
            CASE
                WHEN c.conversation_type = "group" THEN "group"
                ELSE other_user.role
            END AS other_role,
            CASE
                WHEN c.conversation_type = "group" THEN c.group_avatar
                ELSE other_user.avatar
            END AS other_avatar,
            student.name AS other_student_name,
            latest_message.id AS latest_message_id,
            latest_message.sender_id AS latest_sender_id,
            latest_message.message_type AS latest_message_type,
            latest_message.sticker_key AS latest_sticker_key,
            CASE
                WHEN latest_message.deleted_at IS NOT NULL
                THEN "This message was deleted."
                WHEN TRIM(latest_message.body) = ""
                 AND latest_attachment.id IS NOT NULL
                THEN CONCAT(
                    CASE
                        WHEN latest_attachment.attachment_type = "image" THEN "Photo: "
                        ELSE "File: "
                    END,
                    latest_attachment.original_name
                )
                ELSE latest_message.body
            END AS latest_message_body,
            latest_message.created_at AS latest_message_at,
            (
                SELECT COUNT(*)
                FROM messages unread_message
                WHERE unread_message.conversation_id = c.id
                  AND unread_message.deleted_at IS NULL
                  AND (
                      c.conversation_type <> "group"
                      OR unread_message.created_at >= current_participant.joined_at
                  )
                  -- Do not count messages cleared from this participant chat view.
                  AND (
                      current_participant.cleared_at IS NULL
                      OR unread_message.created_at > current_participant.cleared_at
                  )
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
           AND c.conversation_type <> "group"
         LEFT JOIN users other_user
            ON other_user.id = other_participant.user_id
         LEFT JOIN students student
            ON student.user_id = other_user.id
         LEFT JOIN messages latest_message
            ON latest_message.id = (
                SELECT recent_message.id
                FROM messages recent_message
                WHERE recent_message.conversation_id = c.id
                  AND (
                      c.conversation_type <> "group"
                      OR recent_message.created_at >= current_participant.joined_at
                  )
                  -- Use only messages still visible after this participant clear point.
                  AND (
                      current_participant.cleared_at IS NULL
                      OR recent_message.created_at > current_participant.cleared_at
                  )
                ORDER BY recent_message.created_at DESC, recent_message.id DESC
                LIMIT 1
            )
         LEFT JOIN message_attachments latest_attachment
            ON latest_attachment.id = (
                SELECT MIN(first_attachment.id)
                FROM message_attachments first_attachment
                WHERE first_attachment.message_id = latest_message.id
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

    $conversations = $stmt->fetchAll();

    foreach ($conversations as &$conversation) {
        if (($conversation['latest_message_type'] ?? '') === MESSAGE_TYPE_STICKER) {
            // Inbox rows use a readable label instead of an empty sticker-message body.
            $conversation['latest_message_body'] = message_sticker_preview_label(
                (string) ($conversation['latest_sticker_key'] ?? '')
            );
        }
    }
    unset($conversation);

    return $conversations;
}

require_once __DIR__ . '/message_attachment_helpers.php';
require_once __DIR__ . '/message_sticker_helpers.php';
require_once __DIR__ . '/message_content_helpers.php';

require_once __DIR__ . '/message_request_helpers.php';
require_once __DIR__ . '/message_pin_helpers.php';
require_once __DIR__ . '/message_presence_helpers.php';
require_once __DIR__ . '/message_reaction_helpers.php';

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

// Hide all existing messages in a conversation for this user only.
function clear_conversation_for_user(PDO $pdo, int $conversation_id, int $user_id): bool
{
    ensure_message_clear_schema($pdo);

    if (!is_conversation_participant($pdo, $conversation_id, $user_id)) {
        throw new RuntimeException('You cannot clear this conversation.');
    }

    $stmt = $pdo->prepare(
        'UPDATE conversation_participants
         SET cleared_at = CURRENT_TIMESTAMP,
             last_read_at = CURRENT_TIMESTAMP
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
    ensure_message_clear_schema($pdo);

    $stmt = $pdo->prepare(
        'SELECT COUNT(*)
         FROM conversation_participants participant
         INNER JOIN messages message
            ON message.conversation_id = participant.conversation_id
         WHERE participant.user_id = ?
           AND participant.deleted_at IS NULL
           AND participant.is_archived = 0
           AND message.deleted_at IS NULL
           -- Cleared messages should not keep the global unread badge alive.
           AND (
               participant.cleared_at IS NULL
               OR message.created_at > participant.cleared_at
           )
           AND (message.sender_id IS NULL OR message.sender_id <> participant.user_id)
           AND (
               participant.last_read_at IS NULL
               OR message.created_at > participant.last_read_at
           )'
    );
    $stmt->execute([$user_id]);

    return (int) $stmt->fetchColumn();
}

require_once __DIR__ . '/message_birthday_helpers.php';

// Decides what name to show in the inbox
function get_message_user_display_name(array $row, string $prefix = 'other_'): string
{
    if (($row['conversation_type'] ?? '') === 'group') {
        return trim((string) ($row['group_name'] ?? '')) ?: 'Group chat';
    }

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
    $default_avatar = match ($role) {
        'group' => '/gakumas-sms/assets/images/avatars/default.webp',
        'producer' => '/gakumas-sms/assets/images/avatars/default_producer.webp',
        'teacher' => '/gakumas-sms/assets/images/avatars/default_teacher.webp',
        default => '/gakumas-sms/assets/images/avatars/default.webp',
    };

    $local_avatar_exists = static function (string $web_path): bool {
        $path = (string) parse_url($web_path, PHP_URL_PATH);

        if (!str_starts_with($path, '/gakumas-sms/')) {
            return true;
        }

        $base_dir = realpath(__DIR__ . '/..');

        if (!$base_dir) {
            return false;
        }

        $relative = rawurldecode(substr($path, strlen('/gakumas-sms/')));
        $candidate = $base_dir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
        $real_candidate = realpath($candidate);

        return $real_candidate !== false
            && str_starts_with($real_candidate, $base_dir . DIRECTORY_SEPARATOR)
            && is_file($real_candidate);
    };

    if ($avatar !== '') {
        if (preg_match('/^https?:\/\//i', $avatar)) {
            return $avatar;
        }

        if (str_starts_with($avatar, '/')) {
            return $local_avatar_exists($avatar) ? $avatar : $default_avatar;
        }

        $avatar_path = $role === 'student'
            ? '/gakumas-sms/assets/images/avatars/idols/' . rawurlencode($avatar)
            : '/gakumas-sms/assets/images/avatars/' . rawurlencode($avatar);

        return $local_avatar_exists($avatar_path) ? $avatar_path : $default_avatar;
    }

    return $default_avatar;
}
