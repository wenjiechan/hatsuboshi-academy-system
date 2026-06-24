<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/notifications_helpers.php';

// Manual runner script
$result = generate_automatic_notifications($pdo);

// Print the result
echo 'Notifications generated:' . PHP_EOL;
echo 'Schedule or lesson starts: ' . (int) $result['schedule_start'] . PHP_EOL;
echo 'Upcoming birthdays: ' . (int) $result['birthday_upcoming'] . PHP_EOL;
echo 'Birthdays today: ' . (int) $result['birthday_today'] . PHP_EOL;
