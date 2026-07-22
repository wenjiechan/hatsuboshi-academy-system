<?php

require_once __DIR__ . '/student_edit_validation.php';
require_once __DIR__ . '/notifications_helpers.php';

function format_request_song_duration(?string $duration): string
{
    if (!$duration) {
        return '';
    }

    $parts = explode(':', $duration);

    if (count($parts) !== 3) {
        return $duration;
    }

    return sprintf('%02d:%02d', (int) $parts[1], (int) $parts[2]);
}

function load_student_request_context(PDO $pdo, int $user_id): array
{
    $student_stmt = $pdo->prepare(
        'SELECT
            s.*,
            u.avatar,
            u.theme_primary_color,
            u.theme_secondary_color,
            producer.username AS producer_name
         FROM students s
         INNER JOIN users u ON u.id = s.user_id
         LEFT JOIN users producer ON producer.id = s.producer_id
         WHERE s.user_id = ?
         LIMIT 1'
    );

    $student_stmt->execute([$user_id]);
    $student = $student_stmt->fetch();

    if (!$student) {
        return [
            'student' => null,
            'admins' => [],
            'current_songs' => [],
            'available_songs' => [],
            'producer_is_available' => false,
            'recipient_summary' => '',
        ];
    }

    $admin_stmt = $pdo->query(
        'SELECT id, username
         FROM users
         WHERE role = "admin"
           AND is_active = 1
         ORDER BY username ASC'
    );

    $current_song_stmt = $pdo->prepare(
        'SELECT
            so.id,
            so.title,
            so.title_jp,
            so.artist,
            so.duration,
            so.release_date,
            so.song_type,
            so.notes
         FROM student_songs ss
         INNER JOIN songs so ON so.id = ss.song_id
         WHERE ss.student_id = ?
         ORDER BY FIELD(so.song_type, "Solo", "Group", "Remix", "Cover"), so.title ASC'
    );
    $current_song_stmt->execute([(int) $student['id']]);

    $library_song_stmt = $pdo->prepare(
        'SELECT
            so.id,
            so.title,
            so.title_jp,
            so.artist,
            so.duration,
            so.release_date,
            so.song_type
         FROM songs so
         WHERE NOT EXISTS (
            SELECT 1
            FROM student_songs ss
            WHERE ss.song_id = so.id
              AND ss.student_id = ?
         )
         ORDER BY so.title ASC'
    );
    $library_song_stmt->execute([(int) $student['id']]);

    $producer_is_available = !empty($student['producer_id'])
        && in_array($student['producer_status'] ?? 'unassigned', ['active', 'removal_pending'], true);

    return [
        'student' => $student,
        'admins' => $admin_stmt->fetchAll(),
        'current_songs' => $current_song_stmt->fetchAll(),
        'available_songs' => $library_song_stmt->fetchAll(),
        'producer_is_available' => $producer_is_available,
        'recipient_summary' => $producer_is_available
            ? 'You can send this request to your producer or admin.'
            : 'You are unassigned, so requests go to admin only.',
    ];
}

function apply_student_request_session_theme(array $student): void
{
    $_SESSION['student_name'] = $student['name'];
    $_SESSION['avatar'] = $student['avatar'] ?? '';
    $_SESSION['theme_primary_color'] = $student['theme_primary_color'] ?: DEFAULT_THEME_PRIMARY;
    $_SESSION['theme_secondary_color'] = $student['theme_secondary_color'] ?: DEFAULT_THEME_SECONDARY;
}

function student_request_birthday_view_data(array $student): array
{
    $birthday_timestamp = !empty($student['birthday']) ? strtotime((string) $student['birthday']) : false;

    return [
        'display' => $birthday_timestamp ? date('F d', $birthday_timestamp) : '',
        'month' => $birthday_timestamp ? (int) date('n', $birthday_timestamp) : 1,
        'day' => $birthday_timestamp ? (int) date('j', $birthday_timestamp) : 1,
        'months' => student_edit_month_options(),
    ];
}

function student_request_json(array $data): string
{
    return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
}

function student_request_find_by_id(array $rows, int $id): ?array
{
    foreach ($rows as $row) {
        if ((int) ($row['id'] ?? 0) === $id) {
            return $row;
        }
    }

    return null;
}

function student_request_normalized_value(mixed $value): string
{
    return trim((string) $value);
}

function student_request_changed_values(array $current, array $requested): array
{
    $changes = [];

    // Store only real changes so reviewers see a focused request and empty submissions are rejected.
    foreach ($requested as $key => $value) {
        if (!array_key_exists($key, $current) || student_request_normalized_value($value) !== student_request_normalized_value($current[$key])) {
            $changes[$key] = $value;
        }
    }

    return $changes;
}

function student_request_only_keys(array $data, array $keys): array
{
    return array_intersect_key($data, array_flip($keys));
}

function student_request_recipient_id(array $post, array $student, array $admins, bool $producer_is_available, string $request_type): int
{
    if ($request_type === 'song_edit') {
        return (int) ($admins[0]['id'] ?? 0);
    }

    $recipient_type = (string) ($post['recipient_type'] ?? 'admin');

    if ($recipient_type === 'producer' && $producer_is_available) {
        return (int) ($student['producer_id'] ?? 0);
    }

    return (int) ($admins[0]['id'] ?? 0);
}

function student_request_profile_payload(array $post, array $student): array
{
    $requested = [
        'name' => trim((string) ($post['name'] ?? '')),
        'name_jp' => trim((string) ($post['name_jp'] ?? '')),
        'birthday' => trim((string) ($post['birthday'] ?? '')),
        'zodiac' => trim((string) ($post['zodiac'] ?? '')),
        'blood_type' => trim((string) ($post['blood_type'] ?? '')),
        'height' => trim((string) ($post['height'] ?? '')),
        'weight' => trim((string) ($post['weight'] ?? '')),
        'three_size' => trim((string) ($post['three_size'] ?? '')),
        'school_year' => trim((string) ($post['school_year'] ?? '')),
    ];

    $current_birthday = !empty($student['birthday']) ? date('F d', strtotime((string) $student['birthday'])) : '';
    $current = [
        'name' => $student['name'] ?? '',
        'name_jp' => $student['name_jp'] ?? '',
        'birthday' => $current_birthday,
        'zodiac' => $student['zodiac'] ?? '',
        'blood_type' => $student['blood_type'] ?? '',
        'height' => $student['height'] ?? '',
        'weight' => $student['weight'] ?? '',
        'three_size' => $student['three_size'] ?? '',
        'school_year' => $student['school_year'] ?? '',
    ];

    $changes = student_request_changed_values($current, $requested);
    $changed_keys = array_keys($changes);

    // Keep current_data aligned with requested_data so request panels compare only changed fields.
    return [
        'error' => $requested['name'] === ''
            ? 'Name is required for a profile update request.'
            : (empty($changes) ? 'Change at least one profile field before sending a request.' : ''),
        'db_type' => 'profile_update',
        'song_id' => null,
        'subject' => 'Profile Update Request',
        'current_data' => student_request_only_keys($current, $changed_keys),
        'requested_data' => $changes,
    ];
}

function student_request_song_add_payload(array $post, array $available_songs): array
{
    $mode = (string) ($post['song_add_mode'] ?? 'existing');

    if ($mode === 'new') {
        $title = trim((string) ($post['new_song_title'] ?? ''));

        return [
            'error' => $title === '' ? 'Song title is required for a new song request.' : '',
            'db_type' => 'song_add',
            'song_id' => null,
            'subject' => 'Add New Song Request',
            'current_data' => [
                'current_student_song_status' => 'Not assigned to this student yet',
            ],
            'requested_data' => [
                'mode' => 'new_song',
                'title' => $title,
                'title_jp' => trim((string) ($post['new_song_title_jp'] ?? '')),
                'artist' => trim((string) ($post['new_song_artist'] ?? '')),
                'song_type' => trim((string) ($post['new_song_type'] ?? 'Group')),
                'duration' => trim((string) ($post['new_song_duration'] ?? '')),
                'release_date' => trim((string) ($post['new_song_release_date'] ?? '')),
                'notes' => trim((string) ($post['new_song_notes'] ?? '')),
            ],
        ];
    }

    $song_id = (int) ($post['existing_song_id'] ?? 0);
    $song = student_request_find_by_id($available_songs, $song_id);

    return [
        'error' => !$song ? 'Choose an available song from the library.' : '',
        'db_type' => 'song_add',
        'song_id' => $song ? (int) $song['id'] : null,
        'subject' => 'Add Existing Song Request',
        'current_data' => [
            'current_student_song_status' => 'Not assigned to this student yet',
        ],
        'requested_data' => $song ? [
            'mode' => 'existing_song',
            'song_id' => (int) $song['id'],
            'title' => $song['title'] ?? '',
            'title_jp' => $song['title_jp'] ?? '',
            'artist' => $song['artist'] ?? '',
            'song_type' => $song['song_type'] ?? '',
            'duration' => format_request_song_duration($song['duration'] ?? ''),
        ] : [],
    ];
}

function student_request_song_edit_payload(array $post, array $current_songs): array
{
    $song_id = (int) ($post['edit_song_id'] ?? 0);
    $song = student_request_find_by_id($current_songs, $song_id);

    $current = $song ? [
        'title' => $song['title'] ?? '',
        'title_jp' => $song['title_jp'] ?? '',
        'artist' => $song['artist'] ?? '',
        'duration' => format_request_song_duration($song['duration'] ?? ''),
        'song_type' => $song['song_type'] ?? '',
    ] : [];
    $requested = [
        'title' => trim((string) ($post['edit_song_title'] ?? '')),
        'title_jp' => trim((string) ($post['edit_song_title_jp'] ?? '')),
        'artist' => trim((string) ($post['edit_song_artist'] ?? '')),
        'duration' => trim((string) ($post['edit_song_duration'] ?? '')),
        'song_type' => trim((string) ($post['edit_song_type'] ?? '')),
    ];
    $changes = $song ? student_request_changed_values($current, $requested) : [];
    $changed_keys = array_keys($changes);
    $reason = trim((string) ($post['edit_song_reason'] ?? ''));

    // The reason explains the request, but it does not count as a song data change by itself.
    if ($reason !== '') {
        $changes['reason'] = $reason;
    }

    $error = '';

    if (!$song) {
        $error = 'Choose one of your current songs to edit.';
    } elseif ($requested['title'] === '') {
        $error = 'Song title is required for a song correction request.';
    } elseif (empty($changed_keys)) {
        $error = 'Change at least one song field before sending a request.';
    }

    return [
        'error' => $error,
        'db_type' => 'song_correction',
        'song_id' => $song ? (int) $song['id'] : null,
        'subject' => 'Song Correction Request',
        'current_data' => student_request_only_keys($current, $changed_keys),
        'requested_data' => $changes,
    ];
}

function student_request_song_delete_payload(array $post, array $current_songs): array
{
    $song_id = (int) ($post['delete_song_id'] ?? 0);
    $song = student_request_find_by_id($current_songs, $song_id);

    return [
        'error' => !$song ? 'Choose one of your current songs to remove.' : '',
        'db_type' => 'song_remove',
        'song_id' => $song ? (int) $song['id'] : null,
        'subject' => 'Delete Song Request',
        'current_data' => $song ? [
            'current_student_song_status' => 'Assigned to this student',
            'title' => $song['title'] ?? '',
            'artist' => $song['artist'] ?? '',
        ] : [],
        'requested_data' => [
            'action' => 'Remove song from student song list',
            'reason' => trim((string) ($post['delete_song_reason'] ?? '')),
        ],
    ];
}

function student_request_recipient_role(PDO $pdo, int $recipient_id): ?string
{
    $stmt = $pdo->prepare(
        'SELECT role
         FROM users
         WHERE id = ?
           AND is_active = 1
         LIMIT 1'
    );
    $stmt->execute([$recipient_id]);
    $role = $stmt->fetchColumn();

    return $role !== false ? (string) $role : null;
}

function notify_recipient_about_student_request(
    PDO $pdo,
    array $student,
    int $recipient_id,
    string $recipient_role,
    int $request_id,
    string $subject
): bool
{
    $student_name = trim((string) ($student['name'] ?? 'Student'));
    $student_class = trim((string) ($student['school_year'] ?? ''));
    $student_rank = trim((string) ($student['rank'] ?? ''));

    $summary_parts = array_filter([$student_class, $student_rank]);
    $body = $student_name . ' submitted ' . strtolower($subject) . '.';

    if (!empty($summary_parts)) {
        $body .= ' ' . implode(' - ', $summary_parts) . '.';
    }

    $action_url = $recipient_role === 'admin'
        ? '/gakumas-sms/admin/request_view.php?id=' . $request_id
        : '/gakumas-sms/producer/request_view.php?id=' . $request_id;

    return create_notification(
        $pdo,
        $recipient_id,
        NOTIFICATION_TYPE_STUDENT_REQUEST,
        'New student request',
        $body,
        'student_update_request',
        $request_id,
        $action_url,
        'student_request:' . $recipient_role . ':' . $request_id
    );
}

function create_student_update_request(PDO $pdo, array $student, int $recipient_id, array $payload, string $message): int
{
    $stmt = $pdo->prepare(
        'INSERT INTO student_update_requests
            (student_id, recipient_id, request_type, song_id, subject, details, current_data, requested_data, status, created_at)
         VALUES
            (?, ?, ?, ?, ?, ?, ?, ?, "pending", NOW())'
    );

    $stmt->execute([
        (int) $student['id'],
        $recipient_id,
        $payload['db_type'],
        $payload['song_id'],
        $payload['subject'],
        $message,
        student_request_json($payload['current_data']),
        student_request_json($payload['requested_data']),
    ]);

    $request_id = (int) $pdo->lastInsertId();

    $recipient_role = student_request_recipient_role($pdo, $recipient_id);

    if (in_array($recipient_role, ['admin', 'producer'], true)) {
        notify_recipient_about_student_request(
            $pdo,
            $student,
            $recipient_id,
            $recipient_role,
            $request_id,
            (string) $payload['subject']
        );
    }

    return $request_id;
}
