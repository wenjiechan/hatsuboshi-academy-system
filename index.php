<?php
require_once __DIR__ . '/includes/auth.php';

switch ($role) {
    case 'producer':
        header('Location: /gakumas-sms/admin/dashboard.php');
        break;

    case 'teacher':
        header('Location: /gakumas-sms/teacher/dashboard.php');
        break;

    case 'student':
        header('Location: /gakumas-sms/student/dashboard.php');
        break;

    default:
        $_SESSION = [];
        session_destroy();
        header('Location: /gakumas-sms/login.php');
        break;
}

exit();