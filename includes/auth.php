<?php
if (session_status() === PHP_SESSION_NONE) {     // Check the PHP session has already started
    session_set_cookie_params([     // If no, set the cookie setting first
        'httponly' => true,     // Protect against XSS stealing cookies
        'samesite' => 'Lax',     // Limiting cross-site sending
        'secure'   => !empty($_SERVER['HTTPS']),      // Cookie is marked secure only the site using HTTPS
    ]);
    session_start();
}

if(!isset($_SESSION['id'])){
    header("Location: /gakumas-sms/login.php");
    exit();     // If the session does not contain id, redirected the user back to login page
}

$id = $_SESSION['id'];
$username = $_SESSION['user_name'];
$role = $_SESSION['role'];

function require_role(string ...$allowed_roles): void {     // Restrict pages by role
    global $role;
    if (!in_array($role, $allowed_roles, true)) {
        http_response_code(403);
        exit('Access denied');
    }
}

function csrf_token(): string {     // Generate a random CSRF token and stores inside the session
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf(string $submitted): void {     // Check whether the submitted tokens matches the session token
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $submitted)) {
        http_response_code(403);
        exit('Invalid CSRF token');
    }
}
?>
