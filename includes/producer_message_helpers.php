<?php

date_default_timezone_set('Asia/Kuala_Lumpur');

function producer_message_type_options(): array
{
    return [
        'morning' => 'Morning',
        'afternoon' => 'Afternoon',
        'evening' => 'Evening',
        'rest_day' => 'Rest day',
        'audition_day' => 'Audition day',
        'good_progress' => 'Good progress',
        'low_vocal' => 'Low vocal',
        'low_dance' => 'Low dance',
        'low_visual' => 'Low visual',
        'birthday' => 'Birthday',
    ];
}

function producer_message_type_label(?string $message_type): string
{
    $options = producer_message_type_options();

    return $options[$message_type ?? ''] ?? ucwords(str_replace('_', ' ', (string) $message_type));
}

function get_producer_message_students(PDO $pdo, int $producer_id): array
{
    $stmt = $pdo->prepare(
        'SELECT
            s.id,
            s.name,
            s.name_jp,
            s.school_year,
            s.rank,
            u.avatar,
            COUNT(pm.id) AS message_count
         FROM students s
         INNER JOIN users u ON u.id = s.user_id
         LEFT JOIN producer_messages pm
            ON pm.student_id = s.id
           AND pm.producer_id = ?
         WHERE s.producer_id = ?
           AND u.is_active = 1
         GROUP BY s.id, s.name, s.name_jp, s.school_year, s.rank, u.avatar
         ORDER BY s.school_year, s.name'
    );
    $stmt->execute([$producer_id, $producer_id]);

    return $stmt->fetchAll();
}

function get_producer_message_student(PDO $pdo, int $producer_id, int $student_id): ?array
{
    $stmt = $pdo->prepare(
        'SELECT s.id, s.name
         FROM students s
         INNER JOIN users u
            ON u.id = s.user_id
           AND u.is_active = 1
         WHERE s.id = ?
           AND s.producer_id = ?
         LIMIT 1'
    );
    $stmt->execute([$student_id, $producer_id]);
    $student = $stmt->fetch();

    return $student ?: null;
}

function get_producer_messages(PDO $pdo, int $producer_id, ?int $student_id = null): array
{
    $student_sql = $student_id ? 'AND pm.student_id = ?' : '';
    $params = [$producer_id, $producer_id];

    if ($student_id) {
        $params[] = $student_id;
    }

    $stmt = $pdo->prepare(
        'SELECT
            pm.id,
            pm.student_id,
            pm.message_type,
            pm.tone,
            pm.message_text,
            s.name AS student_name,
            s.school_year
         FROM producer_messages pm
         INNER JOIN students s
            ON s.id = pm.student_id
           AND s.producer_id = ?
         WHERE pm.producer_id = ?
           ' . $student_sql . '
         ORDER BY s.school_year, s.name, pm.message_type, pm.id DESC'
    );
    $stmt->execute($params);

    return $stmt->fetchAll();
}

function get_producer_owned_message(PDO $pdo, int $producer_id, int $message_id): ?array
{
    $stmt = $pdo->prepare(
        'SELECT pm.*
         FROM producer_messages pm
         INNER JOIN students s
            ON s.id = pm.student_id
           AND s.producer_id = ?
         WHERE pm.id = ?
           AND pm.producer_id = ?
         LIMIT 1'
    );
    $stmt->execute([$producer_id, $message_id, $producer_id]);
    $message = $stmt->fetch();

    return $message ?: null;
}

function validate_producer_message_payload(string $message_type, string $tone, string $message_text): array
{
    $message_type = trim($message_type);
    $tone = trim($tone);
    $message_text = trim($message_text);

    if (!array_key_exists($message_type, producer_message_type_options())) {
        throw new InvalidArgumentException('Choose a valid message type.');
    }

    if ($message_text === '') {
        throw new InvalidArgumentException('Message text is required.');
    }

    if (strlen($message_text) > 500) {
        throw new InvalidArgumentException('Message text must be 500 characters or fewer.');
    }

    if (strlen($tone) > 20) {
        throw new InvalidArgumentException('Tone must be 20 characters or fewer.');
    }

    return [$message_type, $tone === '' ? null : $tone, $message_text];
}

function create_producer_message(
    PDO $pdo,
    int $producer_id,
    int $student_id,
    string $message_type,
    string $tone,
    string $message_text
): int {
    if (!get_producer_message_student($pdo, $producer_id, $student_id)) {
        throw new RuntimeException('This student is not assigned to your producer account.');
    }

    [$message_type, $tone, $message_text] = validate_producer_message_payload(
        $message_type,
        $tone,
        $message_text
    );

    $stmt = $pdo->prepare(
        'INSERT INTO producer_messages (producer_id, student_id, message_type, tone, message_text)
         VALUES (?, ?, ?, ?, ?)'
    );
    $stmt->execute([$producer_id, $student_id, $message_type, $tone, $message_text]);

    return (int) $pdo->lastInsertId();
}

function update_producer_message(
    PDO $pdo,
    int $producer_id,
    int $message_id,
    string $message_type,
    string $tone,
    string $message_text
): void {
    if (!get_producer_owned_message($pdo, $producer_id, $message_id)) {
        throw new RuntimeException('This producer message is not available.');
    }

    [$message_type, $tone, $message_text] = validate_producer_message_payload(
        $message_type,
        $tone,
        $message_text
    );

    $stmt = $pdo->prepare(
        'UPDATE producer_messages
         SET message_type = ?,
             tone = ?,
             message_text = ?
         WHERE id = ?
           AND producer_id = ?'
    );
    $stmt->execute([$message_type, $tone, $message_text, $message_id, $producer_id]);
}

function delete_producer_message(PDO $pdo, int $producer_id, int $message_id): void
{
    if (!get_producer_owned_message($pdo, $producer_id, $message_id)) {
        throw new RuntimeException('This producer message is not available.');
    }

    $stmt = $pdo->prepare(
        'DELETE FROM producer_messages
         WHERE id = ?
           AND producer_id = ?'
    );
    $stmt->execute([$message_id, $producer_id]);
}

function get_producer_message(PDO $pdo, array $student, array $today_schedules, ?array $previous_snapshot): string
{
    // Check if today is the student birthday, birthday messages have the highest priority
    if (is_student_birthday_today($student)) {
        return get_random_producer_message($pdo, (int) $student['id'], 'birthday', (int) ($student['producer_id'] ?? 0))
            ?: 'Happy birthday. Today is yours, so let yourself enjoy it.';
    }

    $stat_types = ['vocal', 'dance', 'visual'];

    //Create messaage weights
    $message_weights = [
        get_time_based_message_type() => 20,
    ];

    // Audition day
    if (has_audition_today($today_schedules)) {
        $message_weights['audition_day'] = 50;
    }

    // No schedule
    if (empty($today_schedules)) {
        $message_weights['rest_day'] = 45;
    }

    // Increase the chance of a warning message if any student stat decreased.
    $decreased_stat_type = get_decreased_stat_type($student, $previous_snapshot, $stat_types);

    if ($decreased_stat_type !== null) {
        $message_weights['low_' . $decreased_stat_type] = 40;
    }

    // Increase the chance of a positive message if the student's total stats improved.
    if (has_good_progress($student, $previous_snapshot, $stat_types)) {
        $message_weights['good_progress'] = 30;
    }

    // Pick one message type based on the calculated weights.
    $message_type = pick_weighted_message_type($message_weights);

    // Get one random producer message that matches the selected message type.
    return get_random_producer_message($pdo, (int) $student['id'], $message_type, (int) ($student['producer_id'] ?? 0))
        ?: 'Keep your pace steady today. Small progress still counts.';
}

//Gets one random message from the database
function get_random_producer_message(PDO $pdo, int $student_id, string $message_type, int $producer_id = 0): ?string
{
    $producer_sql = $producer_id > 0 ? 'AND producer_id = ?' : 'AND producer_id IS NULL';
    $params = [$student_id, $message_type];

    if ($producer_id > 0) {
        $params[] = $producer_id;
    }

    $stmt = $pdo->prepare(
        'SELECT message_text
         FROM producer_messages
         WHERE student_id = ?
           AND message_type = ?
           ' . $producer_sql . '
         ORDER BY RAND()
         LIMIT 1'
    );

    $stmt->execute($params);

    return $stmt->fetchColumn() ?: null;
}

// Check whether today is the student's birthday
function is_student_birthday_today(array $student): bool
{
    if (empty($student['birthday'])) {
        return false;
    }

    $birthday_time = strtotime((string) $student['birthday']);

    return $birthday_time !== false && date('m-d', $birthday_time) === date('m-d');
}

// Return the message type based on current hour
function get_time_based_message_type(): string
{
    $hour = (int) date('G');

    if ($hour < 12) {
        return 'morning';
    }

    if ($hour < 18) {
        return 'afternoon';
    }

    return 'evening';
}

// Check today's schedule list
function has_audition_today(array $today_schedules): bool
{
    foreach ($today_schedules as $schedule) {
        $activity_type = strtolower($schedule['activity_type'] ?? '');
        $title = strtolower($schedule['title'] ?? '');

        if ($activity_type === 'audition' || str_contains($title, 'audition')) {
            return true;
        }
    }

    return false;
}

//Check whether the student's total stats increased
function has_good_progress(array $student, ?array $previous_snapshot, array $stat_types): bool
{
    if (!$previous_snapshot) {
        return false;
    }

    $previous_total = 0;
    $current_total = 0;

    foreach ($stat_types as $type) {
        $previous_total += (int) ($previous_snapshot[$type] ?? 0);
        $current_total += (int) ($student[$type] ?? 0);
    }

    // Return true when the student's current total stats are higher than the previous snapshot.
    return $current_total > $previous_total;
}

//Finds which stat dropped the most
function get_decreased_stat_type(array $student, ?array $previous_snapshot, array $stat_types): ?string
{
    if (!$previous_snapshot) {
        return null;
    }

    $largest_drop_type = null;
    $largest_drop_amount = 0;

    foreach ($stat_types as $type) {
        $current_value = (int) ($student[$type] ?? 0);
        $previous_value = (int) ($previous_snapshot[$type] ?? 0);
        $drop_amount = $previous_value - $current_value;

        if ($drop_amount > $largest_drop_amount) {
            $largest_drop_amount = $drop_amount;
            $largest_drop_type = $type;
        }
    }

    return $largest_drop_type;
}

//Randomly selects a message type based on weight
function pick_weighted_message_type(array $weights): string
{
    $weights = array_filter(
        $weights,
        fn ($weight) => is_int($weight) && $weight > 0
    );

    if (empty($weights)) {
        return get_time_based_message_type();
    }

    $total_weight = array_sum($weights);
    $random = random_int(1, $total_weight);

    foreach ($weights as $type => $weight) {
        $random -= $weight;

        if ($random <= 0) {
            return $type;
        }
    }

    return array_key_first($weights);
}
