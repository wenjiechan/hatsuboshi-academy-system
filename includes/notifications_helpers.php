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
        'birthday_upcoming' => notify_birthdays_for_date($pdo, $now->modify('+7 days'), NOTIFICATION_TYPE_BIRTHDAY_UPCOMING),
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

// Check students whose birthday month and day match the target day
function notify_birthdays_for_date(PDO $pdo, DateTimeImmutable $date, string $type): int
{
    $month_day = $date->format('m-d');
    $date_key = $date->format('Y-m-d');
    $created = 0;

    $stmt = $pdo->prepare(
        'SELECT
            s.id,
            s.name,
            s.user_id,
            s.producer_id,
            s.producer_status
         FROM students s
         WHERE s.birthday IS NOT NULL
           AND DATE_FORMAT(s.birthday, "%m-%d") = ?'
    );
    $stmt->execute([$month_day]);

    foreach ($stmt->fetchAll() as $student) {
        $student_user_id = (int) $student['user_id'];
        $student_title = $type === NOTIFICATION_TYPE_BIRTHDAY_TODAY
            ? 'Happy birthday!'
            : 'Your birthday is coming up';
        $student_body = $type === NOTIFICATION_TYPE_BIRTHDAY_TODAY
            ? 'Today is your birthday. Happy birthday!'
            : 'Your birthday is in one week.';

        $created += create_notification(
            $pdo,
            $student_user_id,
            $type,
            $student_title,
            $student_body,
            'student',
            (int) $student['id'],
            '/gakumas-sms/student/profile.php',
            $type . ':' . (int) $student['id'] . ':' . $date_key . ':' . $student_user_id
        ) ? 1 : 0;

        if (!empty($student['producer_id']) && $student['producer_status'] === 'active') {
            $producer_user_id = (int) $student['producer_id'];
            $producer_title = $type === NOTIFICATION_TYPE_BIRTHDAY_TODAY
                ? 'Student birthday today'
                : 'Student birthday coming up';
            $producer_body = $type === NOTIFICATION_TYPE_BIRTHDAY_TODAY
                ? sprintf('Today is %s\'s birthday.', $student['name'])
                : sprintf('%s\'s birthday is in one week.', $student['name']);

            $created += create_notification(
                $pdo,
                $producer_user_id,
                $type,
                $producer_title,
                $producer_body,
                'student',
                (int) $student['id'],
                '/gakumas-sms/producer/students.php',
                $type . ':' . (int) $student['id'] . ':' . $date_key . ':' . $producer_user_id
            ) ? 1 : 0;
        }
    }

    return $created;
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
