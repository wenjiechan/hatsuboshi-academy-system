<?php
require_once '../includes/auth.php';
require_role('producer');

require_once '../config/database.php';
require_once '../includes/messages_helpers.php';
require_once '../includes/notifications_helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /gakumas-sms/producer/students.php');
    exit;
}

verify_csrf((string) ($_POST['csrf_token'] ?? ''));

$producer_id = (int) $_SESSION['id'];
$student_id = filter_input(INPUT_POST, 'student_id', FILTER_VALIDATE_INT);

if (!$student_id || $student_id <= 0) {
    $_SESSION['student_page_error'] = 'Please choose a valid student.';
    header('Location: /gakumas-sms/producer/students.php');
    exit;
}

try {
    create_producer_remove_student_request($pdo, $producer_id, (int) $student_id);
    $_SESSION['student_page_success'] = 'Release request sent. The student must accept before the relationship ends.';
} catch (InvalidArgumentException | RuntimeException $exception) {
    $_SESSION['student_page_error'] = $exception->getMessage();
} catch (Throwable $exception) {
    $_SESSION['student_page_error'] = 'The release request could not be sent. Please try again.';
}

header('Location: /gakumas-sms/producer/students.php');
exit;
