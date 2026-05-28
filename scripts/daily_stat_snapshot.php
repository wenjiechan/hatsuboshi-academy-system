<?php
require_once __DIR__ . '/../config/database.php';

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

$students_stmt = $pdo->query(
    'SELECT id, vocal, dance, visual
     FROM students
     ORDER BY id'
);

$snapshot_stmt = $pdo->prepare(
    'INSERT INTO daily_student_stats (student_id, stat_date, vocal, dance, visual)
     VALUES (?, CURDATE(), ?, ?, ?)
     ON DUPLICATE KEY UPDATE
        vocal = VALUES(vocal),
        dance = VALUES(dance),
        visual = VALUES(visual)'
);

$saved_count = 0;

foreach ($students_stmt->fetchAll() as $student) {
    $snapshot_stmt->execute([
        $student['id'],
        (int) ($student['vocal'] ?? 0),
        (int) ($student['dance'] ?? 0),
        (int) ($student['visual'] ?? 0),
    ]);

    $saved_count++;
}

echo 'Daily student stat snapshots saved: ' . $saved_count . PHP_EOL;
