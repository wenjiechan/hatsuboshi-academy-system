<?php
// Start the session only if it has not already been started
if(session_status() === PHP_SESSION_NONE){
session_start();
}

// Clear all session variables
$_SESSION = [];

// Check whether PHP is using cookies to store the session ID
if(ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();

    // Delete the session (by replacing it with an expired cookie)
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

// Destroys the session
session_destroy();

//Redirect the user and stops the script
header('Location: /gakumas-sms/login.php');
exit;