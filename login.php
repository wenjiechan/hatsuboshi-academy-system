<?php
// Make sure PHP has a session
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => !empty($_SERVER['HTTPS']),
    ]);
    session_start();
}
// Connect to database
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/theme_settings_helpers.php';

// Create a random secret token (CSRF token) and store in session
if (empty($_SESSION['login_csrf_token'])) {
    $_SESSION['login_csrf_token'] = bin2hex(random_bytes(32));
}

// Redirect already logged-in users
if (isset($_SESSION['id'], $_SESSION['role'])) {
    $role = $_SESSION['role'];
    if ($role === 'producer') {
        header("Location: /gakumas-sms/admin/dashboard.php");
    }elseif ($role === 'teacher') {
        header("Location: /gakumas-sms/teacher/dashboard.php");
    } else {
        header("Location: /gakumas-sms/student/dashboard.php");
    }
    exit();
}

if (isset($_SESSION['id']) && empty($_SESSION['role'])) {
    $_SESSION = [];
    session_destroy();
    header("Location: /gakumas-sms/login.php");
    exit();
}

// Prepare default values
$error = "";
$prefill = "";

// User clicks Login
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $submittedToken = $_POST['csrf_token'] ?? '';

    $prefill = $username;

    // Check the CSRF token
    if (!hash_equals($_SESSION['login_csrf_token'], $submittedToken)) {
        $error = "Invalid login request";
    } else {
        // Validate empty fields
        if(!$username) {
            $error = "Username required";
        } elseif (!$password) {
            $error = "Password Required";
        } else {
            // Fetches the user
            $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ? AND is_active = 1');
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            //Check the password
            if ($user && password_verify($password, $user['password'])) {
                // Give the user a fresh session ID after login
                session_regenerate_id(true);
                $_SESSION['id'] = $user['id'];
                $_SESSION['user_name'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['avatar'] = $user['avatar'] ?? '';

                unset($_SESSION['student_name']);

                $user_theme = load_user_theme($pdo, (int) $user['id']);
                apply_theme_session($user_theme['primary'], $user_theme['secondary']);

                if ($user['role'] === 'student') {
                    $student_stmt = $pdo->prepare(
                        'SELECT name
                        FROM students
                        WHERE user_id = ?
                        LIMIT 1'
                    );
                    $student_stmt->execute([$user['id']]);
                    $student = $student_stmt->fetch();

                    if ($student) {
                        $_SESSION['student_name'] = $student['name'];
                    }
                }

                try{
                    $upd = $pdo->prepare('Update users SET last_login = NOW() WHERE id = ?');
                    $upd ->execute([$user['id']]);
                }catch(PDOException $e){
                    error_log('Failed to update last_login: ' . $e->getMessage());
                }

                unset($_SESSION['login_csrf_token']);
                if ($user['role'] === 'producer') {
                    header("Location: /gakumas-sms/admin/dashboard.php");
                }elseif ($user['role'] === 'teacher') {
                    header("Location: /gakumas-sms/teacher/dashboard.php");
                } else {
                    header("Location: /gakumas-sms/student/dashboard.php");
                }
                exit();
            } else {
                $error = "Invalid username or password";
            }
        }
    }
} 

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Hatsuboshi Gakuen</title>
    <link rel="icon" href="/gakumas-sms/assets/images/favicon.ico">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <!-- Hatsuboshi theme -->
    <link rel="stylesheet" href="/gakumas-sms/assets/css/theme.css">
    <link rel="stylesheet" href="/gakumas-sms/assets/css/pages/login.css">

</head>

<body class="login-page">
    <!-- Decorative petals -->
    <img src="/gakumas-sms/assets/images/decoration/petal_1.png" class="petal petal-1" alt="">
    <img src="/gakumas-sms/assets/images/decoration/petal_2.png" class="petal petal-2" alt="">
    <img src="/gakumas-sms/assets/images/decoration/petal_3.png" class="petal petal-3" alt="">

    <main class="login-card">
        <div class="login-logo">
            <img src="/gakumas-sms/assets/images/logo.png" alt="Hatsuboshi Gakuen">
        </div>

        <p class="login-subtitle">
            <span class="star">&#9733;</span>
            Welcome back to Hatsuboshi Academy
            <span class="star">&#9733;</span>
        </p>

        <!--Show an error alert only if $error is not empty-->
        <?php if($error !== ''): ?>
        <div class="alert alert-danger d-flex align-items-center" role="alert">
            <i class="bi bi-exclamation-circle-fill me-2"></i>
            <div><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        </div>
        <?php endif; ?>

        <form method="post" action="login.php" novalidate>
            <!--CSRF token inside the form-->
            <input type="hidden" name="csrf_token"
                value="<?= htmlspecialchars($_SESSION['login_csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
            <div class="login-input-group">
                <i class="bi bi-person-fill input-icon"></i>
                <label for="username" class="visually-hidden">Username</label>
                <input type="text" id="username" name="username" class="form-control" placeholder="Username"
                    value="<?= htmlspecialchars($prefill, ENT_QUOTES, "UTF-8") ?>" autocomplete="username" autofocus
                    required>
            </div>

            <div class="login-input-group">
                <i class="bi bi-lock-fill input-icon"></i>
                <label for="password-field" class="visually-hidden">Password</label>
                <input type="password" name="password" id="password-field" class="form-control" placeholder="Password"
                    autocomplete="current-password" required>
                <button type="button" class="password-toggle" id="password-toggle" aria-label="Show password">
                    <i class="bi bi-eye-fill"></i>
                </button>
            </div>

            <button type="submit" class="btn login-btn">
                <span class="login-btn-glow"></span>
                <span class="login-btn-content">
                    <i class="bi bi-stars me-2"></i>
                    Login
                </span>
            </button>


        </form>

        <p class="login-footer">
            &#9733; Hatsuboshi Gakuen &copy; 2026 &#9733;
        </p>

    </main>

    <script>
    // Toggle password visibility
    const toggle = document.getElementById('password-toggle');
    const field = document.getElementById('password-field');
    const icon = toggle.querySelector('i');

    toggle.addEventListener('click', () => {
        const isPassword = field.type === 'password';
        field.type = isPassword ? 'text' : 'password';
        icon.classList.toggle('bi-eye-fill', !isPassword);
        icon.classList.toggle('bi-eye-slash-fill', isPassword);
        toggle.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
    });
    </script>

</body>

</html>
