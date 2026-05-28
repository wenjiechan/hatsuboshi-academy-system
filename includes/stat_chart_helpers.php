<?php

function get_student_weekly_stat_chart(PDO $pdo, array $student): array
{
    $stat_types = ['vocal', 'dance', 'visual'];

    ensure_daily_student_stats_table($pdo);
    save_today_student_stats($pdo, $student, $stat_types);
    delete_old_student_stat_snapshots($pdo, (int) $student['id']);

    $today = new DateTimeImmutable('today');
    $start_date = $today->modify('-6 days');
    $start_date_sql = $start_date->format('Y-m-d');
    $end_date_sql = $today->format('Y-m-d');

    $carry_values = [];

    foreach ($stat_types as $type) {
        $carry_values[$type] = (int) ($student[$type] ?? 0);
    }

    $previous_snapshot = get_previous_stat_snapshot($pdo, (int) $student['id'], $start_date_sql);

    if ($previous_snapshot) {
        foreach ($stat_types as $type) {
            $carry_values[$type] = (int) $previous_snapshot[$type];
        }
    }

    $weekly_snapshots = get_weekly_stat_snapshots(
        $pdo,
        (int) $student['id'],
        $start_date_sql,
        $end_date_sql
    );

    $chart_labels = [];
    $chart_data = array_fill_keys($stat_types, []);

    for ($day = 0; $day < 7; $day++) {
        $date = $start_date->modify('+' . $day . ' days');
        $date_key = $date->format('Y-m-d');

        if (isset($weekly_snapshots[$date_key])) {
            $carry_values = $weekly_snapshots[$date_key];
        }

        $chart_labels[] = $date->format('M j');

        foreach ($stat_types as $type) {
            $chart_data[$type][] = $carry_values[$type];
        }
    }

    return [
        'labels' => $chart_labels,
        'data' => $chart_data,
        'has_data' => !empty($chart_labels),
        'previous_snapshot' => $previous_snapshot ?: null,
    ];
}

function ensure_daily_student_stats_table(PDO $pdo): void
{
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS daily_student_stats (
            id int(11) NOT NULL AUTO_INCREMENT,
            student_id int(11) NOT NULL,
            stat_date date NOT NULL,
            vocal int(11) NOT NULL,
            dance int(11) NOT NULL,
            visual int(11) NOT NULL,
            created_at datetime NOT NULL DEFAULT current_timestamp(),
            updated_at datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
            PRIMARY KEY (id),
            UNIQUE KEY uniq_daily_student_stats_student_date (student_id, stat_date),
            KEY idx_daily_student_stats_date (stat_date)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );
}

function save_today_student_stats(PDO $pdo, array $student, array $stat_types): void
{
    $stmt = $pdo->prepare(
        'INSERT INTO daily_student_stats (student_id, stat_date, vocal, dance, visual)
         VALUES (?, CURDATE(), ?, ?, ?)
         ON DUPLICATE KEY UPDATE
            vocal = VALUES(vocal),
            dance = VALUES(dance),
            visual = VALUES(visual)'
    );

    $stmt->execute([
        (int) $student['id'],
        (int) ($student['vocal'] ?? 0),
        (int) ($student['dance'] ?? 0),
        (int) ($student['visual'] ?? 0),
    ]);
}

function get_previous_stat_snapshot(PDO $pdo, int $student_id, string $start_date_sql): ?array
{
    $stmt = $pdo->prepare(
        'SELECT vocal, dance, visual
         FROM daily_student_stats
         WHERE student_id = ?
           AND stat_date < ?
         ORDER BY stat_date DESC
         LIMIT 1'
    );

    $stmt->execute([$student_id, $start_date_sql]);

    return $stmt->fetch() ?: null;
}

function get_weekly_stat_snapshots(PDO $pdo, int $student_id, string $start_date_sql, string $end_date_sql): array
{
    $stmt = $pdo->prepare(
        'SELECT stat_date, vocal, dance, visual
         FROM daily_student_stats
         WHERE student_id = ?
           AND stat_date BETWEEN ? AND ?
         ORDER BY stat_date ASC'
    );

    $stmt->execute([$student_id, $start_date_sql, $end_date_sql]);

    $snapshots = [];

    foreach ($stmt->fetchAll() as $row) {
        $snapshots[$row['stat_date']] = [
            'vocal' => (int) $row['vocal'],
            'dance' => (int) $row['dance'],
            'visual' => (int) $row['visual'],
        ];
    }

    return $snapshots;
}

function delete_old_student_stat_snapshots(PDO $pdo, int $student_id): void
{
    $stmt = $pdo->prepare(
        'DELETE FROM daily_student_stats
         WHERE student_id = ?
           AND stat_date < DATE_SUB(CURDATE(), INTERVAL 6 DAY)'
    );

    $stmt->execute([$student_id]);
}