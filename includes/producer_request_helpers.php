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
