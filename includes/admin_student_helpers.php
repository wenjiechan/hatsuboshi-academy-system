<?php

require_once __DIR__ . '/student_edit_validation.php';
require_once __DIR__ . '/theme_settings_helpers.php';
require_once __DIR__ . '/messages_helpers.php';
require_once __DIR__ . '/notifications_helpers.php';

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function admin_student_date_to_birthday_input(?string $birthday): string
{
    return $birthday ? date('F d', strtotime($birthday)) : '';
}

function admin_student_sort_text(?string $value): string
{
    $value = trim((string) $value);

    return function_exists('mb_strtolower') ? mb_strtolower($value, 'UTF-8') : strtolower($value);
}

function admin_student_avatar_path(?string $avatar): string
{
    $avatar = trim((string) $avatar);

    if ($avatar === '') {
        return '/gakumas-sms/assets/images/avatars/default.webp';
    }

    $avatar_path = str_replace('\\', '/', $avatar);

    if (!str_starts_with($avatar_path, '/') && !preg_match('/^https?:\/\//i', $avatar_path)) {
        return '/gakumas-sms/assets/images/avatars/idols/' . rawurlencode($avatar_path);
    }

    return $avatar_path;
}

// Gets all active users whose role is producer
function admin_student_load_producers(PDO $pdo): array
{
    $stmt = $pdo->query(
        "SELECT id, username
         FROM users
         WHERE role = 'producer'
           AND is_active = 1
         ORDER BY username"
    );

    return $stmt->fetchAll();
}

// Load student data from students
function admin_student_load_students(PDO $pdo): array
{
    $stmt = $pdo->query(
        "SELECT
            s.*,
            u.username,
            u.avatar,
            u.theme_primary_color,
            u.theme_secondary_color,
            p.username AS producer_name
         FROM students s
         INNER JOIN users u ON u.id = s.user_id
         LEFT JOIN users p ON p.id = s.producer_id
         ORDER BY s.name"
    );

    return $stmt->fetchAll();
}

// Group students by class
function admin_student_group_students_by_class(array $students): array
{
    $grouped_students = [];

    foreach ($students as $student_row) {
        $student_class = $student_row['school_year'] ?: 'Unassigned Class';
        $grouped_students[$student_class][] = $student_row;
    }

    uksort($grouped_students, static function (string $first_class, string $second_class): int {
        if ($first_class === 'Unassigned Class') {
            return 1;
        }

        if ($second_class === 'Unassigned Class') {
            return -1;
        }

        return strnatcasecmp($first_class, $second_class);
    });

    return $grouped_students;
}

function admin_student_find(array $students, int $student_id): ?array
{
    foreach ($students as $student_row) {
        if ((int) $student_row['id'] === $student_id) {
            return $student_row;
        }
    }

    return null;
}

function admin_student_load_relationship(PDO $pdo, int $student_id): ?array
{
    $stmt = $pdo->prepare(
        'SELECT
            s.id AS student_id,
            s.name AS student_name,
            s.user_id AS student_user_id,
            s.producer_id,
            producer.username AS producer_name
         FROM students s
         LEFT JOIN users producer
            ON producer.id = s.producer_id
           AND producer.role = "producer"
         WHERE s.id = ?
         LIMIT 1'
    );
    $stmt->execute([$student_id]);
    $relationship = $stmt->fetch();

    if (!$relationship || empty($relationship['producer_id'])) {
        return null;
    }

    return $relationship;
}

function admin_student_system_admin_id(PDO $pdo): int
{
    $admin_stmt = $pdo->query(
        'SELECT id
         FROM users
         WHERE role = "admin"
           AND is_active = 1
         ORDER BY id
         LIMIT 1'
    );

    return (int) $admin_stmt->fetchColumn();
}

function admin_student_notify_relationship_ended(PDO $pdo, array $relationship, int $admin_user_id): void
{
    $student_id = (int) $relationship['student_id'];
    $student_user_id = (int) $relationship['student_user_id'];
    $producer_user_id = (int) $relationship['producer_id'];
    $student_name = (string) ($relationship['student_name'] ?? 'the student');
    $producer_name = (string) ($relationship['producer_name'] ?? 'the producer');

    $messages = [
        $student_user_id => sprintf(
            'Your producer relationship with %s has ended. You are currently unassigned until an administrator assigns a new producer.',
            $producer_name
        ),
        $producer_user_id => sprintf(
            'Your producer relationship with %s has ended. This student has been removed from your roster.',
            $student_name
        ),
    ];

    foreach ($messages as $recipient_user_id => $body) {
        $conversation_id = find_or_create_system_conversation($pdo, $admin_user_id, (int) $recipient_user_id);
        $message_id = send_conversation_message(
            $pdo,
            $conversation_id,
            $admin_user_id,
            $body,
            MESSAGE_TYPE_SYSTEM,
            'student',
            $student_id
        );

        if (!is_conversation_muted($pdo, $conversation_id, (int) $recipient_user_id)) {
            create_notification(
                $pdo,
                (int) $recipient_user_id,
                NOTIFICATION_TYPE_NEW_MESSAGE,
                'Relationship ended',
                'Admin sent you a system message.',
                'message',
                $message_id,
                '/gakumas-sms/messages/view.php?id=' . $conversation_id,
                'new_message:' . $message_id . ':' . (int) $recipient_user_id
            );
        }
    }
}

function admin_student_validate_producer(PDO $pdo, ?int $producer_id): ?string
{
    if ($producer_id === null) {
        return null;
    }

    $stmt = $pdo->prepare(
        "SELECT id
         FROM users
         WHERE id = ?
           AND role = 'producer'
           AND is_active = 1
         LIMIT 1"
    );
    $stmt->execute([$producer_id]);

    return $stmt->fetch() ? null : 'Please choose a valid active producer.';
}

function admin_student_validate_avatar_upload(string $field): string
{
    if (empty($_FILES[$field]['name'])) {
        return '';
    }

    if (($_FILES[$field]['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return 'Avatar upload failed. Please choose another image.';
    }

    $allowed_avatar_types = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];
    $avatar_mime = mime_content_type($_FILES[$field]['tmp_name']);

    if (!isset($allowed_avatar_types[$avatar_mime])) {
        return 'Avatar must be a JPG, PNG, or WEBP image.';
    }

    if (($_FILES[$field]['size'] ?? 0) > 2 * 1024 * 1024) {
        return 'Avatar must be smaller than 2 MB.';
    }

    return '';
}

function admin_student_validate_required_profile(array $data): string
{
    foreach ([
        'name_jp' => 'Japanese name is required.',
        'birthday' => 'Birthday is required.',
        'blood_type' => 'Blood type is required.',
        'height' => 'Height is required.',
        'weight' => 'Weight is required.',
        'three_size' => 'Three size is required.',
        'school_year' => 'Class is required.',
        'rank' => 'Rank is required.',
    ] as $key => $message) {
        if (($data[$key] ?? '') === '') {
            return $message;
        }
    }

    return '';
}

function admin_student_assignment_producer_id(array $post): ?int
{
    $assignment_type = $post['assignment_type'] ?? 'unassigned';

    if ($assignment_type !== 'producer') {
        return null;
    }

    return ($post['producer_id'] ?? '') !== '' ? (int) $post['producer_id'] : 0;
}

function admin_student_save_avatar_upload(string $field, int $user_id): ?string
{
    if (empty($_FILES[$field]['name'])) {
        return null;
    }

    $allowed_avatar_types = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];
    $avatar_mime = mime_content_type($_FILES[$field]['tmp_name']);
    $avatar_extension = $allowed_avatar_types[$avatar_mime] ?? null;

    if ($avatar_extension === null) {
        return null;
    }

    $avatar_directory = dirname(__DIR__) . '/uploads/profiles';

    if (!is_dir($avatar_directory)) {
        mkdir($avatar_directory, 0775, true);
    }

    $avatar_filename = 'student_' . $user_id . '_' . bin2hex(random_bytes(8)) . '.' . $avatar_extension;
    $avatar_target = $avatar_directory . '/' . $avatar_filename;

    if (!move_uploaded_file($_FILES[$field]['tmp_name'], $avatar_target)) {
        return null;
    }

    return '/gakumas-sms/uploads/profiles/' . $avatar_filename;
}

function admin_student_post_profile_defaults(): array
{
    $today_month = (int) date('n');
    $today_day = (int) date('j');

    return [
        'birthday' => sprintf('2000-%02d-%02d', $today_month, $today_day),
        'zodiac' => student_edit_zodiac_from_month_day($today_month, $today_day),
    ];
}

function admin_student_profile_values_from_post(array $post, array $existing_student = []): array
{
    $validation = validate_student_edit_profile($post, $existing_student ?: admin_student_post_profile_defaults());

    $data = $validation['data'];
    $data['hometown'] = trim($post['hometown'] ?? '');
    $data['hobbies'] = trim($post['hobbies'] ?? '');
    $data['special_skill'] = trim($post['special_skill'] ?? '');
    $data['bio'] = trim($post['bio'] ?? '');

    return [
        'error' => $validation['error'],
        'data' => $data,
    ];
}

function admin_student_flash_success(): string
{
    if (empty($_SESSION['admin_students_success'])) {
        return '';
    }

    $success = (string) $_SESSION['admin_students_success'];
    unset($_SESSION['admin_students_success']);

    return $success;
}

function admin_student_redirect_with_success(string $message): void
{
    $_SESSION['admin_students_success'] = $message;
    header('Location: /gakumas-sms/admin/students.php');
    exit;
}

// Main form router
function admin_student_handle_post(PDO $pdo, bool &$show_create_form): string
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return '';
    }

    verify_csrf($_POST['csrf_token'] ?? '');

    return match ($_POST['action'] ?? '') {
        'create_student' => admin_student_create($pdo, $show_create_form),
        'update_student' => admin_student_update($pdo),
        'remove_producer' => admin_student_remove_producer($pdo),
        default => '',
    };
}

// Create student
function admin_student_create(PDO $pdo, bool &$show_create_form): string
{
    $show_create_form = true;
    $username = trim($_POST['username'] ?? '');
    $temporary_password = (string) ($_POST['temporary_password'] ?? '');
    $producer_id = admin_student_assignment_producer_id($_POST);
    $primary_color = normalize_theme_color($_POST['theme_primary_color'] ?? '', DEFAULT_THEME_PRIMARY);
    $secondary_color = normalize_theme_color($_POST['theme_secondary_color'] ?? '', DEFAULT_THEME_SECONDARY);

    $profile_validation = admin_student_profile_values_from_post($_POST);
    $student_values = $profile_validation['data'];
    $required_profile_error = admin_student_validate_required_profile($student_values);
    $producer_error = $producer_id === 0
        ? 'Please choose which producer will manage this student.'
        : admin_student_validate_producer($pdo, $producer_id);
    $avatar_error = admin_student_validate_avatar_upload('avatar');

    if ($username === '') {
        return 'Username is required.';
    }

    if (student_edit_text_length($username) > 50) {
        return 'Username must be 50 characters or less.';
    }

    if ($temporary_password === '') {
        return 'Temporary password is required.';
    }

    if (strlen($temporary_password) < 6) {
        return 'Temporary password must be at least 6 characters.';
    }

    if ($profile_validation['error'] !== '') {
        return $profile_validation['error'];
    }

    if ($required_profile_error !== '') {
        return $required_profile_error;
    }

    if ($producer_error !== null) {
        return $producer_error;
    }

    if ($avatar_error !== '') {
        return $avatar_error;
    }

    $duplicate_stmt = $pdo->prepare('SELECT id FROM users WHERE username = ? LIMIT 1');
    $duplicate_stmt->execute([$username]);

    if ($duplicate_stmt->fetch()) {
        return 'This username is already used.';
    }

    // Creates the login account
    try {
        $pdo->beginTransaction();

        $user_stmt = $pdo->prepare(
            "INSERT INTO users
                (username, password, role, is_active, theme_primary_color, theme_secondary_color)
             VALUES
                (?, ?, 'student', 1, ?, ?)"
        );
        $user_stmt->execute([
            $username,
            password_hash($temporary_password, PASSWORD_DEFAULT),
            $primary_color,
            $secondary_color,
        ]);
        $user_id = (int) $pdo->lastInsertId();

        // Create the student profile
        $student_stmt = $pdo->prepare(
            'INSERT INTO students
                (user_id, name, name_jp, birthday, zodiac, blood_type, height, weight, three_size,
                 hometown, hobbies, special_skill, school_year, rank, vocal, dance, visual, bio,
                 producer_id, producer_status)
             VALUES
                (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $student_stmt->execute([
            $user_id,
            $student_values['name'],
            $student_values['name_jp'] !== '' ? $student_values['name_jp'] : null,
            $student_values['birthday'] !== '' ? $student_values['birthday'] : null,
            $student_values['zodiac'] !== '' ? $student_values['zodiac'] : null,
            $student_values['blood_type'] !== '' ? $student_values['blood_type'] : null,
            $student_values['height'] !== '' ? (int) $student_values['height'] : null,
            $student_values['weight'] !== '' ? (int) $student_values['weight'] : null,
            $student_values['three_size'] !== '' ? $student_values['three_size'] : null,
            $student_values['hometown'] !== '' ? $student_values['hometown'] : null,
            $student_values['hobbies'] !== '' ? $student_values['hobbies'] : null,
            $student_values['special_skill'] !== '' ? $student_values['special_skill'] : null,
            $student_values['school_year'] !== '' ? $student_values['school_year'] : null,
            $student_values['rank'] !== '' ? $student_values['rank'] : 'Debut',
            $student_values['vocal'],
            $student_values['dance'],
            $student_values['visual'],
            $student_values['bio'] !== '' ? $student_values['bio'] : null,
            // Controls producer status
            $producer_id,
            $producer_id !== null ? 'active' : 'unassigned',
        ]);

        $avatar_path = admin_student_save_avatar_upload('avatar', $user_id);

        if ($avatar_path !== null) {
            $avatar_stmt = $pdo->prepare('UPDATE users SET avatar = ? WHERE id = ?');
            $avatar_stmt->execute([$avatar_path, $user_id]);
        }

        $pdo->commit();
        admin_student_redirect_with_success('Student account and profile created successfully.');
        return '';
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        return 'Student could not be created. Please check the details and try again.';
    }
}

function admin_student_update(PDO $pdo): string
{
    $student_id = (int) ($_POST['student_id'] ?? 0);
    $producer_id = admin_student_assignment_producer_id($_POST);
    $primary_color = normalize_theme_color($_POST['theme_primary_color'] ?? '', DEFAULT_THEME_PRIMARY);
    $secondary_color = normalize_theme_color($_POST['theme_secondary_color'] ?? '', DEFAULT_THEME_SECONDARY);

    // Getting the student to update
    $student_stmt = $pdo->prepare(
        'SELECT s.*, u.id AS account_user_id
         FROM students s
         INNER JOIN users u ON u.id = s.user_id
         WHERE s.id = ?
         LIMIT 1'
    );
    $student_stmt->execute([$student_id]);
    $student = $student_stmt->fetch();

    if (!$student) {
        return 'Student profile was not found.';
    }

    $profile_validation = admin_student_profile_values_from_post($_POST, $student);
    $student_values = $profile_validation['data'];
    $required_profile_error = admin_student_validate_required_profile($student_values);
    $producer_error = $producer_id === 0
        ? 'Please choose which producer will manage this student.'
        : admin_student_validate_producer($pdo, $producer_id);
    $avatar_error = admin_student_validate_avatar_upload('avatar');

    if ($profile_validation['error'] !== '') {
        return $profile_validation['error'];
    }

    if ($required_profile_error !== '') {
        return $required_profile_error;
    }

    if ($producer_error !== null) {
        return $producer_error;
    }

    if ($avatar_error !== '') {
        return $avatar_error;
    }

    try {
        $pdo->beginTransaction();

        // Updates the students table
        $update_student_stmt = $pdo->prepare(
            'UPDATE students
             SET name = ?,
                 name_jp = ?,
                 birthday = ?,
                 zodiac = ?,
                 blood_type = ?,
                 height = ?,
                 weight = ?,
                 three_size = ?,
                 hometown = ?,
                 hobbies = ?,
                 special_skill = ?,
                 school_year = ?,
                 rank = ?,
                 vocal = ?,
                 dance = ?,
                 visual = ?,
                 bio = ?,
                 producer_id = ?,
                 producer_status = ?
             WHERE id = ?'
        );
        $update_student_stmt->execute([
            $student_values['name'],
            $student_values['name_jp'] !== '' ? $student_values['name_jp'] : null,
            $student_values['birthday'] !== '' ? $student_values['birthday'] : null,
            $student_values['zodiac'] !== '' ? $student_values['zodiac'] : null,
            $student_values['blood_type'] !== '' ? $student_values['blood_type'] : null,
            $student_values['height'] !== '' ? (int) $student_values['height'] : null,
            $student_values['weight'] !== '' ? (int) $student_values['weight'] : null,
            $student_values['three_size'] !== '' ? $student_values['three_size'] : null,
            $student_values['hometown'] !== '' ? $student_values['hometown'] : null,
            $student_values['hobbies'] !== '' ? $student_values['hobbies'] : null,
            $student_values['special_skill'] !== '' ? $student_values['special_skill'] : null,
            $student_values['school_year'] !== '' ? $student_values['school_year'] : null,
            $student_values['rank'] !== '' ? $student_values['rank'] : 'Debut',
            $student_values['vocal'],
            $student_values['dance'],
            $student_values['visual'],
            $student_values['bio'] !== '' ? $student_values['bio'] : null,
            $producer_id,
            $producer_id !== null ? 'active' : 'unassigned',
            $student_id,
        ]);

        // Updates theme colors in the user table
        $update_user_stmt = $pdo->prepare(
            'UPDATE users
             SET theme_primary_color = ?,
                 theme_secondary_color = ?
             WHERE id = ?'
        );
        $update_user_stmt->execute([
            $primary_color,
            $secondary_color,
            (int) $student['account_user_id'],
        ]);

        $avatar_path = admin_student_save_avatar_upload('avatar', (int) $student['account_user_id']);

        if ($avatar_path !== null) {
            $avatar_stmt = $pdo->prepare('UPDATE users SET avatar = ? WHERE id = ?');
            $avatar_stmt->execute([$avatar_path, (int) $student['account_user_id']]);
        }

        $pdo->commit();
        admin_student_redirect_with_success('Student profile updated successfully.');
        return '';
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        return 'Student profile could not be updated. Please try again.';
    }
}

// Remove the producer relationship
function admin_student_remove_producer(PDO $pdo): string
{
    $student_id = (int) ($_POST['student_id'] ?? 0);
    $relationship = admin_student_load_relationship($pdo, $student_id);
    $system_admin_id = admin_student_system_admin_id($pdo);

    if ($relationship === null) {
        return 'This student does not currently have a producer assigned.';
    }

    if ($system_admin_id <= 0) {
        return 'No active admin account is available to send the system message.';
    }

    admin_student_notify_relationship_ended($pdo, $relationship, $system_admin_id);

    $stmt = $pdo->prepare(
        "UPDATE students
         SET producer_id = NULL,
             producer_status = 'unassigned'
         WHERE id = ?"
    );
    $stmt->execute([$student_id]);

    admin_student_redirect_with_success('Producer removed. The student is now unassigned.');

    return '';
}
