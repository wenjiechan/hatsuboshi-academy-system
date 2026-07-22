<?php

function producer_request_type_label(?string $type): string
{
    return match ($type) {
        'profile_update' => 'Profile Update',
        'song_add' => 'Add Song',
        'song_remove' => 'Delete Song',
        'song_delete' => 'Delete Song',
        'song_correction' => 'Edit Song',
        'song_edit' => 'Edit Song',
        default => 'Request',
    };
}

function producer_request_status_label(?string $status): string
{
    return ucwords(str_replace('_', ' ', (string) ($status ?: 'pending')));
}

function producer_request_time_label(?string $datetime): string
{
    if (!$datetime) {
        return 'Not dated';
    }

    $timestamp = strtotime($datetime);

    if (!$timestamp) {
        return $datetime;
    }

    $diff = time() - $timestamp;

    if ($diff < 60) {
        return 'Just now';
    }

    if ($diff < 3600) {
        $minutes = (int) floor($diff / 60);
        return $minutes . ' min ago';
    }

    if ($diff < 86400) {
        $hours = (int) floor($diff / 3600);
        return $hours . ' hr ago';
    }

    if ($diff < 604800) {
        $days = (int) floor($diff / 86400);
        return $days . ' day' . ($days === 1 ? '' : 's') . ' ago';
    }

    return date('M j, Y', $timestamp);
}

function producer_request_preview(array $request): string
{
    $details = trim((string) ($request['details'] ?? ''));

    if ($details !== '') {
        return $details;
    }

    $subject = trim((string) ($request['subject'] ?? ''));

    if ($subject !== '') {
        return $subject;
    }

    return 'Open this request to review the submitted details.';
}

function producer_request_decode_json(?string $json): array
{
    if (!$json) {
        return [];
    }

    $decoded = json_decode($json, true);

    return is_array($decoded) ? $decoded : [];
}

function producer_request_json(array $data): string
{
    return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
}

function producer_request_normalize_duration(?string $duration): ?string
{
    $duration = trim((string) $duration);

    if ($duration === '') {
        return null;
    }

    if (preg_match('/^\d{1,2}:\d{2}$/', $duration)) {
        return '00:' . $duration;
    }

    if (preg_match('/^\d{1,2}:\d{2}:\d{2}$/', $duration)) {
        return $duration;
    }

    return null;
}

function producer_request_birthday_date(?string $month_day, ?string $current_birthday): ?string
{
    $month_day = trim((string) $month_day);
    $current_birthday = trim((string) $current_birthday);

    if ($month_day === '') {
        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $current_birthday) ? $current_birthday : null;
    }

    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $month_day)) {
        return $month_day;
    }

    $year = '2000';

    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $current_birthday)) {
        $year = date('Y', strtotime($current_birthday));
    }

    foreach (['!F d Y', '!M d Y'] as $format) {
        $date = DateTimeImmutable::createFromFormat($format, $month_day . ' ' . $year);
        $errors = DateTimeImmutable::getLastErrors();

        if ($date && ($errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0))) {
            return $date->format('Y-m-d');
        }
    }

    return preg_match('/^\d{4}-\d{2}-\d{2}$/', $current_birthday) ? $current_birthday : null;
}

function producer_request_first_admin_id(PDO $pdo): ?int
{
    $stmt = $pdo->query(
        'SELECT id
         FROM users
         WHERE role = "admin"
           AND is_active = 1
         ORDER BY id
         LIMIT 1'
    );
    $admin_id = (int) $stmt->fetchColumn();

    return $admin_id > 0 ? $admin_id : null;
}

function producer_request_notify_admin_forward(PDO $pdo, array $request, int $admin_id, int $producer_id): void
{
    require_once __DIR__ . '/notifications_helpers.php';

    $student_name = trim((string) ($request['student_name'] ?? 'Student'));
    $type_label = producer_request_type_label($request['request_type'] ?? '');

    create_notification(
        $pdo,
        $admin_id,
        NOTIFICATION_TYPE_STUDENT_REQUEST,
        'Student request forwarded',
        $student_name . ' has a ' . strtolower($type_label) . ' forwarded by producer.',
        'student_update_request',
        (int) $request['id'],
        '/gakumas-sms/admin/request_view.php?id=' . (int) $request['id'],
        'student_request:admin_forward:' . (int) $request['id'] . ':' . $producer_id
    );
}

function producer_request_notify_student_result(PDO $pdo, array $request, string $result): void
{
    require_once __DIR__ . '/notifications_helpers.php';

    $student_id = (int) ($request['student_id'] ?? 0);

    if ($student_id <= 0) {
        return;
    }

    $stmt = $pdo->prepare(
        'SELECT user_id, name
         FROM students
         WHERE id = ?
         LIMIT 1'
    );
    $stmt->execute([$student_id]);
    $student = $stmt->fetch();

    if (!$student || empty($student['user_id'])) {
        return;
    }

    $type_label = producer_request_type_label($request['request_type'] ?? '');
    $approved = $result === 'approved';
    $title = $approved ? 'Request completed' : 'Request rejected';
    $body = $approved
        ? 'Your ' . strtolower($type_label) . ' request has been completed.'
        : 'Your ' . strtolower($type_label) . ' request was rejected.';

    create_notification(
        $pdo,
        (int) $student['user_id'],
        NOTIFICATION_TYPE_STUDENT_REQUEST,
        $title,
        $body,
        'student_update_request',
        (int) $request['id'],
        '/gakumas-sms/student/request.php',
        'student_request_result:' . $result . ':' . (int) $request['id']
    );
}

function producer_request_apply(PDO $pdo, array $request, int $actor_id): void
{
    $request_type = (string) ($request['request_type'] ?? '');
    $student_id = (int) ($request['student_id'] ?? 0);
    $song_id = !empty($request['song_id']) ? (int) $request['song_id'] : null;
    $requested = producer_request_decode_json($request['requested_data'] ?? null);

    if ($student_id <= 0) {
        throw new RuntimeException('Request is missing student details.');
    }

    if ($request_type === 'profile_update') {
        $birthday = array_key_exists('birthday', $requested)
            ? producer_request_birthday_date($requested['birthday'] ?? '', $request['birthday'] ?? null)
            : ($request['birthday'] ?? null);
        $stmt = $pdo->prepare(
            'UPDATE students
             SET name = ?,
                 name_jp = ?,
                 birthday = ?,
                 zodiac = ?,
                 blood_type = ?,
                 height = ?,
                 weight = ?,
                 three_size = ?,
                 school_year = ?
             WHERE id = ?'
        );
        $stmt->execute([
            trim((string) ($requested['name'] ?? $request['student_name'] ?? '')),
            trim((string) ($requested['name_jp'] ?? $request['student_name_jp'] ?? '')),
            $birthday,
            trim((string) ($requested['zodiac'] ?? $request['zodiac'] ?? '')),
            trim((string) ($requested['blood_type'] ?? $request['blood_type'] ?? '')),
            ($requested['height'] ?? $request['height'] ?? '') === '' ? null : (int) ($requested['height'] ?? $request['height']),
            ($requested['weight'] ?? $request['weight'] ?? '') === '' ? null : (int) ($requested['weight'] ?? $request['weight']),
            trim((string) ($requested['three_size'] ?? $request['three_size'] ?? '')),
            trim((string) ($requested['school_year'] ?? $request['school_year'] ?? '')),
            $student_id,
        ]);

        return;
    }

    if ($request_type === 'song_add') {
        if (($requested['mode'] ?? '') === 'new_song') {
            $duration = producer_request_normalize_duration($requested['duration'] ?? '');
            $stmt = $pdo->prepare(
                'INSERT INTO songs
                    (title, title_jp, artist, duration, release_date, song_type, notes, created_by)
                 VALUES
                    (?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                trim((string) ($requested['title'] ?? '')),
                trim((string) ($requested['title_jp'] ?? '')),
                trim((string) ($requested['artist'] ?? '')),
                $duration,
                trim((string) ($requested['release_date'] ?? '')) ?: null,
                in_array(($requested['song_type'] ?? ''), ['Solo', 'Group', 'Remix', 'Cover'], true) ? $requested['song_type'] : 'Group',
                trim((string) ($requested['notes'] ?? '')),
                $actor_id,
            ]);
            $song_id = (int) $pdo->lastInsertId();
        } else {
            $song_id = $song_id ?: (int) ($requested['song_id'] ?? 0);
        }

        if ($song_id <= 0) {
            throw new RuntimeException('Request is missing song details.');
        }

        $stmt = $pdo->prepare(
            'INSERT IGNORE INTO student_songs (student_id, song_id, added_by)
             VALUES (?, ?, ?)'
        );
        $stmt->execute([$student_id, $song_id, $actor_id]);

        return;
    }

    if (in_array($request_type, ['song_remove', 'song_delete'], true)) {
        $song_id = $song_id ?: (int) ($requested['song_id'] ?? 0);

        if ($song_id <= 0) {
            throw new RuntimeException('Request is missing song details.');
        }

        $stmt = $pdo->prepare(
            'DELETE FROM student_songs
             WHERE student_id = ?
               AND song_id = ?'
        );
        $stmt->execute([$student_id, $song_id]);

        return;
    }

    if (in_array($request_type, ['song_correction', 'song_edit'], true)) {
        if ($song_id <= 0) {
            throw new RuntimeException('Request is missing song details.');
        }

        // Merge partial correction payloads with the live song row so unchanged columns are preserved.
        $song_stmt = $pdo->prepare(
            'SELECT title, title_jp, artist, duration, song_type
             FROM songs
             WHERE id = ?
             LIMIT 1'
        );
        $song_stmt->execute([$song_id]);
        $current = $song_stmt->fetch() ?: [];
        $duration_value = $requested['duration'] ?? $current['duration'] ?? '';
        $duration = producer_request_normalize_duration($duration_value);
        $stmt = $pdo->prepare(
            'UPDATE songs
             SET title = ?,
                 title_jp = ?,
                 artist = ?,
                 duration = ?,
                 song_type = ?
             WHERE id = ?'
        );
        $stmt->execute([
            trim((string) ($requested['title'] ?? $current['title'] ?? '')),
            trim((string) ($requested['title_jp'] ?? $current['title_jp'] ?? '')),
            trim((string) ($requested['artist'] ?? $current['artist'] ?? '')),
            $duration,
            in_array(($requested['song_type'] ?? $current['song_type'] ?? ''), ['Solo', 'Group', 'Remix', 'Cover'], true) ? ($requested['song_type'] ?? $current['song_type']) : 'Group',
            $song_id,
        ]);

        return;
    }

    throw new RuntimeException('This request type cannot be applied automatically yet.');
}

function producer_request_handle_action(PDO $pdo, array $request, int $actor_id, string $actor_role, string $action): string
{
    $request_id = (int) ($request['id'] ?? 0);
    $status = (string) ($request['status'] ?? 'pending');

    if ($request_id <= 0) {
        throw new RuntimeException('Request is missing.');
    }

    if (in_array($status, ['approved', 'rejected', 'cancelled'], true)) {
        throw new RuntimeException('This request is already closed.');
    }

    if ($action === 'auto_edit') {
        $pdo->beginTransaction();

        try {
            producer_request_apply($pdo, $request, $actor_id);
            $stmt = $pdo->prepare(
                'UPDATE student_update_requests
                 SET status = "approved",
                     apply_mode = "automatic",
                     handled_by = ?,
                     resolved_at = NOW(),
                     applied_at = NOW()
                 WHERE id = ?'
            );
            $stmt->execute([$actor_id, $request_id]);
            producer_request_notify_student_result($pdo, $request, 'approved');
            $pdo->commit();
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $exception;
        }

        return 'Request applied automatically.';
    }

    if ($action === 'manual_edit') {
        $stmt = $pdo->prepare(
            'UPDATE student_update_requests
             SET status = "in_progress",
                 apply_mode = "manual",
                 handled_by = ?
             WHERE id = ?'
        );
        $stmt->execute([$actor_id, $request_id]);

        return 'Request marked for manual editing.';
    }

    if ($action === 'completed') {
        $stmt = $pdo->prepare(
            'UPDATE student_update_requests
             SET status = "approved",
                 handled_by = ?,
                 resolved_at = NOW(),
                 applied_at = COALESCE(applied_at, NOW())
             WHERE id = ?'
        );
        $stmt->execute([$actor_id, $request_id]);
        producer_request_notify_student_result($pdo, $request, 'approved');

        return 'Request marked as completed.';
    }

    if ($action === 'reject') {
        $stmt = $pdo->prepare(
            'UPDATE student_update_requests
             SET status = "rejected",
                 handled_by = ?,
                 resolved_at = NOW()
             WHERE id = ?'
        );
        $stmt->execute([$actor_id, $request_id]);
        producer_request_notify_student_result($pdo, $request, 'rejected');

        return 'Request rejected.';
    }

    if ($action === 'send_admin') {
        if ($actor_role !== 'producer') {
            throw new RuntimeException('Only producers can forward requests to admin.');
        }

        $admin_id = producer_request_first_admin_id($pdo);

        if (!$admin_id) {
            throw new RuntimeException('No active admin account is available.');
        }

        $stmt = $pdo->prepare(
            'UPDATE student_update_requests
             SET recipient_id = ?,
                 status = "pending",
                 apply_mode = NULL,
                 handled_by = NULL,
                 forwarded_by = ?,
                 forwarded_at = NOW()
             WHERE id = ?'
        );
        $stmt->execute([$admin_id, $actor_id, $request_id]);
        producer_request_notify_admin_forward($pdo, $request, $admin_id, $actor_id);

        return 'Request sent to admin.';
    }

    throw new InvalidArgumentException('Choose a valid request action.');
}

function producer_request_manual_edit_url(array $request, string $actor_role): string
{
    $request_id = (int) ($request['id'] ?? 0);
    $student_id = (int) ($request['student_id'] ?? 0);
    $song_id = !empty($request['song_id']) ? (int) $request['song_id'] : 0;
    $request_type = (string) ($request['request_type'] ?? '');

    if ($actor_role === 'admin') {
        if ($request_type === 'profile_update') {
            return '/gakumas-sms/admin/students.php?edit=' . $student_id . '&request_id=' . $request_id . '#edit-student';
        }

        if (in_array($request_type, ['song_correction', 'song_edit'], true) && $song_id > 0) {
            return '/gakumas-sms/admin/songs.php?edit=' . $song_id . '&request_id=' . $request_id . '#song-form';
        }

        return '/gakumas-sms/admin/student_songs.php?manage_student_id=' . $student_id . '&request_id=' . $request_id . '#adminStudentSongs';
    }

    if ($request_type === 'profile_update') {
        return '/gakumas-sms/producer/student_edit.php?id=' . $student_id . '&request_id=' . $request_id;
    }

    return '/gakumas-sms/producer/songs.php?student_id=' . $student_id . '&request_id=' . $request_id . '#studentSongs';
}

function producer_request_context_requested_data(?array $request): array
{
    if (!$request) {
        return [];
    }

    return producer_request_decode_json($request['requested_data'] ?? null);
}

function producer_request_context_current_data(?array $request): array
{
    if (!$request) {
        return [];
    }

    return producer_request_decode_json($request['current_data'] ?? null);
}

function producer_request_context_changed_data(?array $request): array
{
    $requested = producer_request_visible_data(producer_request_context_requested_data($request));
    $current = producer_request_visible_data(producer_request_context_current_data($request));

    // New requests already store changed fields only; this fallback keeps older full snapshots tidy.
    if (!$request || ($request['request_type'] ?? '') !== 'profile_update') {
        return $requested;
    }

    return array_filter(
        $requested,
        static function (mixed $value, string $key) use ($current): bool {
            if (!array_key_exists($key, $current)) {
                return true;
            }

            return trim((string) $value) !== trim((string) $current[$key]);
        },
        ARRAY_FILTER_USE_BOTH
    );
}

function producer_request_detail_url(array $request, string $actor_role): string
{
    $request_id = (int) ($request['id'] ?? 0);

    return $actor_role === 'admin'
        ? '/gakumas-sms/admin/request_view.php?id=' . $request_id
        : '/gakumas-sms/producer/request_view.php?id=' . $request_id;
}

function producer_request_format_value(mixed $value): string
{
    if (is_array($value)) {
        return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) ?: '';
    }

    if ($value === null || $value === '') {
        return 'Not listed';
    }

    return (string) $value;
}

function producer_request_field_label(string $key): string
{
    return match ($key) {
        'mode' => 'Request Kind',
        'title_jp' => 'Japanese Title',
        'song_type' => 'Song Type',
        'song_id' => 'Song ID',
        'current_student_song_status' => 'Current Student Song Status',
        default => ucwords(str_replace('_', ' ', $key)),
    };
}

function producer_request_display_value(string $key, mixed $value): string
{
    if ($key === 'mode') {
        return match ((string) $value) {
            'existing_song' => 'Add existing song from library',
            'new_song' => 'Create new song and assign it',
            default => producer_request_format_value($value),
        };
    }

    if (in_array($key, ['birthday', 'release_date'], true) && is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
        return date($key === 'birthday' ? 'F d' : 'M j, Y', strtotime($value));
    }

    if ($key === 'duration' && is_string($value)) {
        $parts = explode(':', $value);

        if (count($parts) === 3) {
            return sprintf('%02d:%02d', (int) $parts[1], (int) $parts[2]);
        }

        return $value;
    }

    if ($key === 'height' && is_numeric($value)) {
        return (string) $value . ' cm';
    }

    if ($key === 'weight' && is_numeric($value)) {
        return (string) $value . ' kg';
    }

    return producer_request_format_value($value);
}

function producer_request_avatar_path(?string $avatar, ?string $student_name): string
{
    $default_path = '/gakumas-sms/assets/images/avatars/default.webp';
    $avatar = trim((string) $avatar);

    if ($avatar !== '') {
        $avatar_path = str_replace('\\', '/', $avatar);

        if (str_starts_with($avatar_path, '/') || preg_match('/^https?:\/\//i', $avatar_path)) {
            return $avatar_path;
        }

        return '/gakumas-sms/assets/images/avatars/idols/' . rawurlencode($avatar_path);
    }

    $student_name = trim((string) $student_name);

    if ($student_name !== '') {
        foreach ([$student_name . '.png', $student_name . '1.png'] as $filename) {
            $local_path = dirname(__DIR__) . '/assets/images/avatars/idols/' . $filename;

            if (is_file($local_path)) {
                return '/gakumas-sms/assets/images/avatars/idols/' . rawurlencode($filename);
            }
        }
    }

    return $default_path;
}

function producer_request_user_avatar_path(?string $avatar, ?string $role): string
{
    $role = (string) ($role ?: 'student');
    $default_path = match ($role) {
        'producer' => '/gakumas-sms/assets/images/avatars/default_producer.webp',
        'teacher' => '/gakumas-sms/assets/images/avatars/default_teacher.webp',
        default => '/gakumas-sms/assets/images/avatars/default.webp',
    };

    $avatar = trim((string) $avatar);

    if ($avatar === '') {
        return $default_path;
    }

    $avatar_path = str_replace('\\', '/', $avatar);

    if (str_starts_with($avatar_path, '/') || preg_match('/^https?:\/\//i', $avatar_path)) {
        return $avatar_path;
    }

    return '/gakumas-sms/assets/images/avatars/' . rawurlencode($avatar_path);
}

function producer_request_visible_data(array $data): array
{
    $hidden_keys = [
        'student',
        'student_name',
        'class',
        'school_year',
        'rank',
        'vocal',
        'dance',
        'visual',
        'note',
    ];

    return array_filter(
        $data,
        static fn(string $key): bool => !in_array($key, $hidden_keys, true),
        ARRAY_FILTER_USE_KEY
    );
}

function load_producer_request_account(PDO $pdo, int $user_id): ?array
{
    $stmt = $pdo->prepare(
        'SELECT id, username, theme_primary_color, theme_secondary_color
         FROM users
         WHERE id = ?
           AND role = "producer"
         LIMIT 1'
    );
    $stmt->execute([$user_id]);

    return $stmt->fetch() ?: null;
}

function apply_producer_request_theme(array $producer): void
{
    $_SESSION['theme_primary_color'] = $producer['theme_primary_color'] ?: ($_SESSION['theme_primary_color'] ?? DEFAULT_THEME_PRIMARY);
    $_SESSION['theme_secondary_color'] = $producer['theme_secondary_color'] ?: ($_SESSION['theme_secondary_color'] ?? DEFAULT_THEME_SECONDARY);
}

function load_producer_request_inbox(PDO $pdo, int $producer_id): array
{
    $stmt = $pdo->prepare(
        'SELECT
            r.*,
            s.name AS student_name,
            s.name_jp AS student_name_jp,
            s.school_year,
            s.rank,
            u.avatar AS student_avatar
         FROM student_update_requests r
         INNER JOIN students s ON s.id = r.student_id
         INNER JOIN users u ON u.id = s.user_id
         WHERE r.recipient_id = ?
         ORDER BY FIELD(r.status, "pending", "in_progress", "approved", "rejected", "cancelled"),
                  r.created_at DESC'
    );
    $stmt->execute([$producer_id]);

    return $stmt->fetchAll();
}

function load_producer_request_detail(PDO $pdo, int $producer_id, int $request_id): ?array
{
    $stmt = $pdo->prepare(
        'SELECT
            r.*,
            s.name AS student_name,
            s.name_jp AS student_name_jp,
            s.school_year,
            s.rank,
            s.birthday,
            s.zodiac,
            s.blood_type,
            s.height,
            s.weight,
            s.three_size,
            s.hometown,
            s.hobbies,
            s.special_skill,
            s.bio,
            s.vocal,
            s.dance,
            s.visual,
            u.avatar AS student_avatar,
            handler.username AS handler_name
         FROM student_update_requests r
         INNER JOIN students s ON s.id = r.student_id
         INNER JOIN users u ON u.id = s.user_id
         LEFT JOIN users handler ON handler.id = r.handled_by
         WHERE r.id = ?
           AND r.recipient_id = ?
         LIMIT 1'
    );
    $stmt->execute([$request_id, $producer_id]);

    return $stmt->fetch() ?: null;
}
