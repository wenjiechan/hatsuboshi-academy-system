<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/stat_chart_helpers.php';

// Make sure the daily_student_stats table exists before saving snapshots.
ensure_daily_student_stats_table($pdo);

// Select every student's current stat values.
$students_stmt = $pdo->query(
    'SELECT id, vocal, dance, visual
     FROM students
     ORDER BY id'
);

$saved_count = 0;

// Save one daily stat snapshot for each student.
foreach ($students_stmt->fetchAll() as $student) {
    save_today_student_stats($pdo, $student);
    delete_old_student_stat_snapshots($pdo, (int) $student['id']);

    $saved_count++;
}

echo 'Daily student stat snapshots saved: ' . $saved_count . PHP_EOL;
