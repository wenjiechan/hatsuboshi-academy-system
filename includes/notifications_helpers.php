<?php
// Defines all notification types
const NOTIFICATION_TYPE_SCHEDULE_START = 'schedule_start';
const NOTIFICATION_TYPE_LESSON_START = 'lesson_start';
const NOTIFICATION_TYPE_BIRTHDAY_UPCOMING = 'birthday_upcoming';
const NOTIFICATION_TYPE_BIRTHDAY_TODAY = 'birthday_today';
const NOTIFICATION_TYPE_SCHEDULE_UPDATED = 'schedule_updated';
const NOTIFICATION_TYPE_SCHEDULE_CANCELLED = 'schedule_cancelled';
const NOTIFICATION_TYPE_SCHEDULE_CREATED = 'schedule_created';
const NOTIFICATION_TYPE_LESSON_UPDATED = 'lesson_updated';
const NOTIFICATION_TYPE_NEW_MESSAGE = 'new_message';
const NOTIFICATION_TYPE_STUDENT_REQUEST = 'student_request';

const NOTIFICATION_CATEGORY_MESSAGES = 'messages';
const NOTIFICATION_CATEGORY_SCHEDULES = 'schedules';
const NOTIFICATION_CATEGORY_LESSONS = 'lessons';
const NOTIFICATION_CATEGORY_BIRTHDAYS = 'birthdays';
const NOTIFICATION_CATEGORY_REQUESTS = 'requests';
const NOTIFICATION_CATEGORY_SYSTEM = 'system';

// Check the notifications table has all required columns
function ensure_notifications_table_columns(PDO $pdo): void
{
    static $ensured = false;

    if ($ensured) {
        return;
    }

    $columns = [];
    foreach ($pdo->query('SHOW COLUMNS FROM notifications')->fetchAll() as $column) {
        $columns[$column['Field']] = true;
    }

    $column_sql = [
        'related_id' => 'ALTER TABLE notifications ADD COLUMN related_id INT NULL AFTER body',
        'related_type' => 'ALTER TABLE notifications ADD COLUMN related_type VARCHAR(50) NULL AFTER related_id',
        'action_url' => 'ALTER TABLE notifications ADD COLUMN action_url VARCHAR(255) NULL AFTER related_type',
        'dedupe_key' => 'ALTER TABLE notifications ADD COLUMN dedupe_key VARCHAR(190) NULL AFTER action_url',
        'read_at' => 'ALTER TABLE notifications ADD COLUMN read_at DATETIME NULL AFTER is_read',
        'responded_at' => 'ALTER TABLE notifications ADD COLUMN responded_at DATETIME NULL AFTER read_at',
    ];

    foreach ($column_sql as $column => $sql) {
        if (empty($columns[$column])) {
            $pdo->exec($sql);
        }
    }

    $indexes = [];
    foreach ($pdo->query('SHOW INDEX FROM notifications')->fetchAll() as $index) {
        $indexes[$index['Key_name']] = true;
    }

    $index_sql = [
        'idx_notifications_related' => 'ALTER TABLE notifications ADD KEY idx_notifications_related (related_type, related_id)',
        'idx_notifications_dedupe_key' => 'ALTER TABLE notifications ADD KEY idx_notifications_dedupe_key (dedupe_key)',
        'idx_notifications_read_at' => 'ALTER TABLE notifications ADD KEY idx_notifications_read_at (read_at)',
    ];

    foreach ($index_sql as $index => $sql) {
        if (empty($indexes[$index])) {
            $pdo->exec($sql);
        }
    }

    $ensured = true;
}

function ensure_notification_settings_schema(PDO $pdo): void
{
    static $ensured = false;

    if ($ensured) {
        return;
    }

    // Settings are opt-out by default so existing users keep receiving notifications.
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS user_notification_settings (
            user_id INT NOT NULL,
            messages_enabled TINYINT(1) NOT NULL DEFAULT 1,
            schedules_enabled TINYINT(1) NOT NULL DEFAULT 1,
            lessons_enabled TINYINT(1) NOT NULL DEFAULT 1,
            birthdays_enabled TINYINT(1) NOT NULL DEFAULT 1,
            requests_enabled TINYINT(1) NOT NULL DEFAULT 1,
            system_enabled TINYINT(1) NOT NULL DEFAULT 1,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (user_id),
            CONSTRAINT fk_user_notification_settings_user
                FOREIGN KEY (user_id) REFERENCES users(id)
                ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );

    $ensured = true;
}

function notification_category_options(): array
{
    return [
        NOTIFICATION_CATEGORY_MESSAGES => 'Messages',
        NOTIFICATION_CATEGORY_SCHEDULES => 'Schedules',
        NOTIFICATION_CATEGORY_LESSONS => 'Lessons',
        NOTIFICATION_CATEGORY_BIRTHDAYS => 'Birthdays',
        NOTIFICATION_CATEGORY_REQUESTS => 'Requests',
        NOTIFICATION_CATEGORY_SYSTEM => 'System notices',
    ];
}

function notification_category_for_type(string $type): string
{
    // Map internal notification types to the user-facing setting categories.
    return match ($type) {
        NOTIFICATION_TYPE_NEW_MESSAGE => NOTIFICATION_CATEGORY_MESSAGES,
        NOTIFICATION_TYPE_SCHEDULE_START,
        NOTIFICATION_TYPE_SCHEDULE_CREATED,
        NOTIFICATION_TYPE_SCHEDULE_UPDATED,
        NOTIFICATION_TYPE_SCHEDULE_CANCELLED => NOTIFICATION_CATEGORY_SCHEDULES,
        NOTIFICATION_TYPE_LESSON_START,
        NOTIFICATION_TYPE_LESSON_UPDATED => NOTIFICATION_CATEGORY_LESSONS,
        NOTIFICATION_TYPE_BIRTHDAY_UPCOMING,
        NOTIFICATION_TYPE_BIRTHDAY_TODAY => NOTIFICATION_CATEGORY_BIRTHDAYS,
        NOTIFICATION_TYPE_STUDENT_REQUEST => NOTIFICATION_CATEGORY_REQUESTS,
        default => NOTIFICATION_CATEGORY_SYSTEM,
    };
}

function load_user_notification_settings(PDO $pdo, int $user_id): array
{
    ensure_notification_settings_schema($pdo);

    $defaults = array_fill_keys(array_keys(notification_category_options()), true);
    $stmt = $pdo->prepare(
        'SELECT messages_enabled, schedules_enabled, lessons_enabled, birthdays_enabled, requests_enabled, system_enabled
         FROM user_notification_settings
         WHERE user_id = ?
         LIMIT 1'
    );
    $stmt->execute([$user_id]);
    $row = $stmt->fetch();

    if (!$row) {
        return $defaults;
    }

    return [
        NOTIFICATION_CATEGORY_MESSAGES => !empty($row['messages_enabled']),
        NOTIFICATION_CATEGORY_SCHEDULES => !empty($row['schedules_enabled']),
        NOTIFICATION_CATEGORY_LESSONS => !empty($row['lessons_enabled']),
        NOTIFICATION_CATEGORY_BIRTHDAYS => !empty($row['birthdays_enabled']),
        NOTIFICATION_CATEGORY_REQUESTS => !empty($row['requests_enabled']),
        NOTIFICATION_CATEGORY_SYSTEM => !empty($row['system_enabled']),
    ];
}

function save_user_notification_settings(PDO $pdo, int $user_id, array $enabled_categories): void
{
    ensure_notification_settings_schema($pdo);

    $settings = [];
    foreach (notification_category_options() as $category => $_label) {
        $settings[$category] = !empty($enabled_categories[$category]) ? 1 : 0;
    }

    $stmt = $pdo->prepare(
        'INSERT INTO user_notification_settings
            (user_id, messages_enabled, schedules_enabled, lessons_enabled, birthdays_enabled, requests_enabled, system_enabled)
         VALUES (?, ?, ?, ?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE
            messages_enabled = VALUES(messages_enabled),
            schedules_enabled = VALUES(schedules_enabled),
            lessons_enabled = VALUES(lessons_enabled),
            birthdays_enabled = VALUES(birthdays_enabled),
            requests_enabled = VALUES(requests_enabled),
            system_enabled = VALUES(system_enabled),
            updated_at = CURRENT_TIMESTAMP'
    );
    $stmt->execute([
        $user_id,
        $settings[NOTIFICATION_CATEGORY_MESSAGES],
        $settings[NOTIFICATION_CATEGORY_SCHEDULES],
        $settings[NOTIFICATION_CATEGORY_LESSONS],
        $settings[NOTIFICATION_CATEGORY_BIRTHDAYS],
        $settings[NOTIFICATION_CATEGORY_REQUESTS],
        $settings[NOTIFICATION_CATEGORY_SYSTEM],
    ]);
}

function user_allows_notification_type(PDO $pdo, int $user_id, string $type): bool
{
    $settings = load_user_notification_settings($pdo, $user_id);
    $category = notification_category_for_type($type);

    return $settings[$category] ?? true;
}

// Inserts a new notification in to the database
function create_notification(
    PDO $pdo,
    int $user_id,
    string $type,
    string $title,
    ?string $body = null,
    ?string $related_type = null,
    ?int $related_id = null,
    ?string $action_url = null,
    ?string $dedupe_key = null
): bool {
    ensure_notifications_table_columns($pdo);

    // Respect the recipient's notification settings before doing dedupe work.
    if (!user_allows_notification_type($pdo, $user_id, $type)) {
        return false;
    }

    // Check the duplication notifications
    if ($dedupe_key !== null) {
        $duplicate_stmt = $pdo->prepare(
            'SELECT id
             FROM notifications
             WHERE user_id = ?
               AND dedupe_key = ?
             LIMIT 1'
        );
        $duplicate_stmt->execute([$user_id, $dedupe_key]);

        if ($duplicate_stmt->fetch()) {
            return false;
        }
    }

    $stmt = $pdo->prepare(
        'INSERT INTO notifications
            (user_id, type, title, body, related_type, related_id, action_url, dedupe_key)
         VALUES
            (?, ?, ?, ?, ?, ?, ?, ?)'
    );

    return $stmt->execute([
        $user_id,
        $type,
        $title,
        $body,
        $related_type,
        $related_id,
        $action_url,
        $dedupe_key,
    ]);
}

// Get all notifications for one user
function get_user_notifications(PDO $pdo, int $user_id): array
{
    ensure_notifications_table_columns($pdo);

    $stmt = $pdo->prepare(
        'SELECT id, type, title, body, related_type, related_id, action_url, is_read, created_at, read_at, responded_at
         FROM notifications
         WHERE user_id = ?
         ORDER BY created_at DESC'
    );
    $stmt->execute([$user_id]);

    return $stmt->fetchAll();
}

function notification_group_key(array $notification): string
{
    $type = (string) ($notification['type'] ?? '');
    $action_url = (string) ($notification['action_url'] ?? '');

    // Message notifications should group by conversation, not by individual message row.
    if ($type === NOTIFICATION_TYPE_NEW_MESSAGE && preg_match('/[?&]id=(\d+)/', $action_url, $matches)) {
        return 'message:conversation:' . $matches[1];
    }

    $related_type = (string) ($notification['related_type'] ?? '');
    $related_id = (int) ($notification['related_id'] ?? 0);

    if ($related_type !== '' && $related_id > 0) {
        return $type . ':' . $related_type . ':' . $related_id;
    }

    // Fallback keeps similar system-style notifications together without requiring schema changes.
    return $type . ':' . md5((string) ($notification['title'] ?? '') . '|' . $action_url);
}

function group_user_notifications(array $notifications): array
{
    $groups = [];

    foreach ($notifications as $notification) {
        $key = notification_group_key($notification);

        if (!isset($groups[$key])) {
            $groups[$key] = [
                'group_key' => $key,
                'latest' => $notification,
                'items' => [],
                'ids' => [],
                'count' => 0,
                'unread_count' => 0,
            ];
        }

        $groups[$key]['items'][] = $notification;
        $groups[$key]['ids'][] = (int) $notification['id'];
        $groups[$key]['count']++;

        if (empty($notification['is_read'])) {
            $groups[$key]['unread_count']++;
        }
    }

    return array_values($groups);
}

function mark_notifications_read(PDO $pdo, int $user_id, array $notification_ids): bool
{
    ensure_notifications_table_columns($pdo);

    // Selected grouped cards can submit comma-separated IDs, so normalize before querying.
    $ids = normalize_notification_id_list($notification_ids);

    if (empty($ids)) {
        return false;
    }

    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare(
        'UPDATE notifications
         SET is_read = 1,
             read_at = COALESCE(read_at, NOW())
         WHERE user_id = ?
           AND id IN (' . $placeholders . ')'
    );

    return $stmt->execute(array_merge([$user_id], $ids));
}

function delete_notifications(PDO $pdo, int $user_id, array $notification_ids): bool
{
    ensure_notifications_table_columns($pdo);

    // Reuse the same normalization as mark-read so grouped bulk actions behave consistently.
    $ids = normalize_notification_id_list($notification_ids);

    if (empty($ids)) {
        return false;
    }

    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare(
        'DELETE FROM notifications
         WHERE user_id = ?
           AND id IN (' . $placeholders . ')'
    );

    return $stmt->execute(array_merge([$user_id], $ids));
}

function normalize_notification_id_list(array $notification_ids): array
{
    $ids = [];

    foreach ($notification_ids as $notification_id) {
        foreach (explode(',', (string) $notification_id) as $id) {
            $id = (int) trim($id);

            if ($id > 0) {
                $ids[] = $id;
            }
        }
    }

    return array_values(array_unique($ids));
}

//Marks one notification as read
function mark_notification_read(PDO $pdo, int $notification_id, int $user_id): bool
{
    ensure_notifications_table_columns($pdo);

    $stmt = $pdo->prepare(
        'UPDATE notifications
         SET is_read = 1,
             read_at = COALESCE(read_at, NOW())
         WHERE id = ?
           AND user_id = ?'
    );

    return $stmt->execute([$notification_id, $user_id]);
}

// Marks all unread notifications for a user as read
function mark_all_notifications_read(PDO $pdo, int $user_id): bool
{
    ensure_notifications_table_columns($pdo);

    $stmt = $pdo->prepare(
        'UPDATE notifications
         SET is_read = 1,
             read_at = COALESCE(read_at, NOW())
         WHERE user_id = ?
           AND is_read = 0'
    );

    return $stmt->execute([$user_id]);
}

// Count unread notifications
function get_unread_notification_count(PDO $pdo, int $user_id): int
{
    $stmt = $pdo->prepare(
        'SELECT COUNT(*)
         FROM notifications
         WHERE user_id = ?
           AND is_read = 0'
    );
    $stmt->execute([$user_id]);

    return (int) $stmt->fetchColumn();
}

// Create notifications when a schedule is created, updated, or cancelled
function notify_schedule_created(PDO $pdo, int $schedule_id, string $schedule_kind = 'schedule'): bool
{
    return notify_schedule_change($pdo, $schedule_id, $schedule_kind, NOTIFICATION_TYPE_SCHEDULE_CREATED);
}

function notify_schedule_updated(PDO $pdo, int $schedule_id, string $schedule_kind = 'schedule'): bool
{
    return notify_schedule_change($pdo, $schedule_id, $schedule_kind, NOTIFICATION_TYPE_SCHEDULE_UPDATED);
}

function notify_schedule_cancelled(PDO $pdo, int $schedule_id, string $schedule_kind = 'schedule'): bool
{
    return notify_schedule_change($pdo, $schedule_id, $schedule_kind, NOTIFICATION_TYPE_SCHEDULE_CANCELLED);
}

function notify_lesson_updated(PDO $pdo, int $lesson_id): int
{
    $lesson_stmt = $pdo->prepare(
        'SELECT id, name, type
         FROM lessons
         WHERE id = ?
         LIMIT 1'
    );
    $lesson_stmt->execute([$lesson_id]);
    $lesson = $lesson_stmt->fetch();

    if (!$lesson) {
        return 0;
    }

    $recipients_stmt = $pdo->query(
        'SELECT id
         FROM users
         WHERE role IN ("student", "producer", "teacher")
           AND is_active = 1'
    );

    $created = 0;
    foreach ($recipients_stmt->fetchAll() as $recipient) {
        $created += create_notification(
            $pdo,
            (int) $recipient['id'],
            NOTIFICATION_TYPE_LESSON_UPDATED,
            'Lesson updated',
            sprintf('%s lesson details have been updated.', $lesson['name']),
            'lesson',
            (int) $lesson['id'],
            '/gakumas-sms/student/lessons.php',
            'lesson_updated:' . (int) $lesson['id'] . ':' . date('Y-m-d H:i:s')
        ) ? 1 : 0;
    }

    return $created;
}

function generate_automatic_notifications(PDO $pdo, ?DateTimeImmutable $now = null): array
{
    ensure_notifications_table_columns($pdo);

    $now = $now ?: new DateTimeImmutable('now', new DateTimeZone('Asia/Kuala_Lumpur'));

    return [
        'schedule_start' => notify_due_schedule_starts($pdo, $now),
        'birthday_upcoming' => notify_upcoming_birthdays($pdo, $now),
        'birthday_today' => notify_birthdays_for_date($pdo, $now, NOTIFICATION_TYPE_BIRTHDAY_TODAY),
    ];
}

// Checks schedules that are starting soon
function notify_due_schedule_starts(PDO $pdo, DateTimeImmutable $now): int
{
    $created = 0;
    $today = $now->format('Y-m-d');
    $current_time = $now->format('H:i:s');
    $window_start = $now->modify('-15 minutes')->format('H:i:s');
    $weekday = (int) $now->format('N');

    $normal_stmt = $pdo->prepare(
        'SELECT
            s.id,
            s.student_id,
            s.activity_type,
            s.title,
            s.date,
            s.start_time,
            s.location,
            st.user_id,
            st.name AS student_name
         FROM schedules s
         INNER JOIN students st ON st.id = s.student_id
         WHERE s.date = ?
           AND s.status = "scheduled"
           AND s.start_time BETWEEN ? AND ?'
    );
    $normal_stmt->execute([$today, $window_start, $current_time]);

    foreach ($normal_stmt->fetchAll() as $schedule) {
        $created += notify_schedule_start_row($pdo, $schedule, 'schedule', $today);
    }

    $recurring_stmt = $pdo->prepare(
        'SELECT
            rs.id,
            rs.student_id,
            rs.activity_type,
            rs.title,
            rs.weekday,
            rs.start_time,
            rs.location,
            st.user_id,
            st.name AS student_name
         FROM recurring_schedules rs
         INNER JOIN students st ON st.id = rs.student_id
         WHERE rs.weekday = ?
           AND rs.is_active = 1
           AND rs.start_time BETWEEN ? AND ?'
    );
    $recurring_stmt->execute([$weekday, $window_start, $current_time]);

    foreach ($recurring_stmt->fetchAll() as $schedule) {
        $created += notify_schedule_start_row($pdo, $schedule, 'recurring_schedule', $today);
    }

    return $created;
}

// Create one upcoming notification for the early window, then one more for tomorrow.
function notify_upcoming_birthdays(PDO $pdo, DateTimeImmutable $now): int
{
    $created = 0;

    for ($days_until = 2; $days_until <= 7; $days_until++) {
        $created += notify_birthdays_for_date(
            $pdo,
            $now->modify('+' . $days_until . ' days'),
            NOTIFICATION_TYPE_BIRTHDAY_UPCOMING,
            $days_until,
            'early'
        );
    }

    $created += notify_birthdays_for_date(
        $pdo,
        $now->modify('+1 day'),
        NOTIFICATION_TYPE_BIRTHDAY_UPCOMING,
        1,
        'tomorrow'
    );

    return $created;
}

// Check students whose birthday month and day match the target day.
// The stage becomes part of the dedupe key, so early, tomorrow, and today stay separate.
function notify_birthdays_for_date(
    PDO $pdo,
    DateTimeImmutable $date,
    string $type,
    ?int $days_until = null,
    ?string $stage = null
): int
{
    $month_day = $date->format('m-d');
    $date_key = $date->format('Y-m-d');
    $created = 0;
    $stage = $stage ?: ($type === NOTIFICATION_TYPE_BIRTHDAY_TODAY ? 'today' : 'early');
    $upcoming_text = $days_until === 1
        ? 'tomorrow'
        : 'in ' . (int) $days_until . ' days';

    $stmt = $pdo->prepare(
        'SELECT
            s.id,
            s.name,
            s.user_id,
            s.producer_id,
            s.producer_status
         FROM students s
         INNER JOIN users student_user
            ON student_user.id = s.user_id
           AND student_user.is_active = 1
         WHERE s.birthday IS NOT NULL
           AND DATE_FORMAT(s.birthday, "%m-%d") = ?'
    );
    $stmt->execute([$month_day]);

    foreach ($stmt->fetchAll() as $student) {
        $student_user_id = (int) $student['user_id'];
        if ($type === NOTIFICATION_TYPE_BIRTHDAY_TODAY) {
            $student_title = 'Happy birthday!';
            $student_body = 'Today is your birthday. Happy birthday!';
        } elseif ($stage === 'tomorrow') {
            $student_title = 'Your birthday is tomorrow';
            $student_body = 'Your birthday is tomorrow.';
        } else {
            $student_title = 'Your birthday is coming up';
            $student_body = 'Your birthday is ' . $upcoming_text . '.';
        }

        $created += create_notification(
            $pdo,
            $student_user_id,
            $type,
            $student_title,
            $student_body,
            'student',
            (int) $student['id'],
            '/gakumas-sms/student/profile.php',
            $type . ':' . $stage . ':' . (int) $student['id'] . ':' . $date_key . ':' . $student_user_id
        ) ? 1 : 0;

        // Related users receive student-facing reminders, while the birthday student gets self-facing copy.
        foreach (get_birthday_related_notification_recipients($pdo, $student) as $recipient) {
            $recipient_user_id = (int) $recipient['id'];
            if ($type === NOTIFICATION_TYPE_BIRTHDAY_TODAY) {
                $recipient_title = 'Student birthday today';
                $recipient_body = sprintf(
                    'Today is %s\'s birthday. Click here to send a birthday message!',
                    $student['name']
                );
                $recipient_action_url = '/gakumas-sms/messages/send_birthday.php?student_id=' . (int) $student['id'];
            } elseif ($stage === 'tomorrow') {
                $recipient_title = 'Student birthday tomorrow';
                $recipient_body = sprintf('%s\'s birthday is tomorrow.', $student['name']);
                $recipient_action_url = null;
            } else {
                $recipient_title = 'Student birthday coming up';
                $recipient_body = sprintf('%s\'s birthday is %s.', $student['name'], $upcoming_text);
                $recipient_action_url = null;
            }

            $created += create_notification(
                $pdo,
                $recipient_user_id,
                $type,
                $recipient_title,
                $recipient_body,
                'student',
                (int) $student['id'],
                $recipient_action_url,
                $type . ':' . $stage . ':' . (int) $student['id'] . ':' . $date_key . ':' . $recipient_user_id
            ) ? 1 : 0;
        }
    }

    return $created;
}

// Birthday-related users are all students, all teachers, all admins, and the active assigned producer.
function get_birthday_related_notification_recipients(PDO $pdo, array $student): array
{
    $stmt = $pdo->prepare(
        'SELECT DISTINCT u.id, u.role
         FROM users u
         WHERE u.is_active = 1
           AND u.id <> ?
           AND (
                u.role IN ("student", "teacher", "admin")
                OR (
                    u.role = "producer"
                    AND u.id = ?
                    AND ? = "active"
                )
           )
         ORDER BY FIELD(u.role, "student", "producer", "teacher", "admin"), u.id'
    );
    $stmt->execute([
        (int) $student['user_id'],
        (int) ($student['producer_id'] ?? 0),
        (string) ($student['producer_status'] ?? ''),
    ]);

    return $stmt->fetchAll();
}

function notify_schedule_change(PDO $pdo, int $schedule_id, string $schedule_kind, string $type): bool
{
    $schedule = get_notification_schedule($pdo, $schedule_id, $schedule_kind);

    if (!$schedule) {
        return false;
    }

    $labels = [
        NOTIFICATION_TYPE_SCHEDULE_CREATED => ['Schedule created', 'A new schedule has been added: %s.'],
        NOTIFICATION_TYPE_SCHEDULE_UPDATED => ['Schedule updated', 'Your schedule has been updated: %s.'],
        NOTIFICATION_TYPE_SCHEDULE_CANCELLED => ['Schedule cancelled', 'Your schedule has been cancelled: %s.'],
    ];

    [$title, $body_template] = $labels[$type] ?? ['Schedule update', 'Schedule update: %s.'];

    return create_notification(
        $pdo,
        (int) $schedule['user_id'],
        $type,
        $title,
        sprintf($body_template, format_notification_schedule_summary($schedule, $schedule_kind)),
        $schedule_kind,
        (int) $schedule['id'],
        '/gakumas-sms/student/schedule.php',
        $type . ':' . $schedule_kind . ':' . (int) $schedule['id'] . ':' . date('Y-m-d H:i:s')
    );
}

//Gets schedule data from normal or recurring data
function get_notification_schedule(PDO $pdo, int $schedule_id, string $schedule_kind): ?array
{
    if ($schedule_kind === 'recurring_schedule') {
        $stmt = $pdo->prepare(
            'SELECT
                rs.*,
                st.user_id,
                st.name AS student_name
             FROM recurring_schedules rs
             INNER JOIN students st ON st.id = rs.student_id
             WHERE rs.id = ?
             LIMIT 1'
        );
    } else {
        $stmt = $pdo->prepare(
            'SELECT
                s.*,
                st.user_id,
                st.name AS student_name
             FROM schedules s
             INNER JOIN students st ON st.id = s.student_id
             WHERE s.id = ?
             LIMIT 1'
        );
    }

    $stmt->execute([$schedule_id]);
    $schedule = $stmt->fetch();

    return $schedule ?: null;
}

//Creates the actual starting soon notifications
function notify_schedule_start_row(PDO $pdo, array $schedule, string $schedule_kind, string $date_key): int
{
    $is_lesson = is_lesson_activity((string) $schedule['activity_type']);
    $type = $is_lesson ? NOTIFICATION_TYPE_LESSON_START : NOTIFICATION_TYPE_SCHEDULE_START;
    $title = $is_lesson ? 'Lesson starting' : 'Schedule starting';
    $body = sprintf('%s starts at %s.', $schedule['title'], substr((string) $schedule['start_time'], 0, 5));

    if (!empty($schedule['location'])) {
        $body .= ' Location: ' . $schedule['location'] . '.';
    }

    return create_notification(
        $pdo,
        (int) $schedule['user_id'],
        $type,
        $title,
        $body,
        $schedule_kind,
        (int) $schedule['id'],
        '/gakumas-sms/student/schedule.php',
        $type . ':' . $schedule_kind . ':' . (int) $schedule['id'] . ':' . $date_key
    ) ? 1 : 0;
}

// Decide the activity is considered a lessons
function is_lesson_activity(string $activity_type): bool
{
    return in_array(strtolower($activity_type), ['lesson', 'vocal', 'dance', 'visual'], true);
}

// Create readable schedule text
function format_notification_schedule_summary(array $schedule, string $schedule_kind): string
{
    $time = substr((string) $schedule['start_time'], 0, 5);

    if ($schedule_kind === 'recurring_schedule') {
        $weekday_names = [
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
            7 => 'Sunday',
        ];
        $day = $weekday_names[(int) $schedule['weekday']] ?? 'weekly';

        return sprintf('%s every %s at %s', $schedule['title'], $day, $time);
    }

    return sprintf('%s on %s at %s', $schedule['title'], $schedule['date'], $time);
}
