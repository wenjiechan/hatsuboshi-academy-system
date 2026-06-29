<?php

date_default_timezone_set('Asia/Kuala_Lumpur');

function get_producer_message(PDO $pdo, array $student, array $today_schedules, ?array $previous_snapshot): string
{
    // Check if today is the student birthday, birthday messages have the highest priority
    if (is_student_birthday_today($student)) {
        return get_random_producer_message($pdo, (int) $student['id'], 'birthday')
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
    return get_random_producer_message($pdo, (int) $student['id'], $message_type)
        ?: 'Keep your pace steady today. Small progress still counts.';
}

//Gets one random message from the database
function get_random_producer_message(PDO $pdo, int $student_id, string $message_type): ?string
{
    $stmt = $pdo->prepare(
        'SELECT message_text
         FROM producer_messages
         WHERE student_id = ?
           AND message_type = ?
         ORDER BY RAND()
         LIMIT 1'
    );

    $stmt->execute([$student_id, $message_type]);

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
