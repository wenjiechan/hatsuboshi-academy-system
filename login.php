<?php
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}
require_once __DIR__ . '/config/database.php';

if(isset($_SESSION['id'])) {
    $role = $_SESSION['role'] ?? 'student';
    if ($role === 'producer') {
        header("Location: /gakumas-sms/admin/dashboard.php");
    }elseif ($role === 'teacher') {
        header("Location: /gakumas-sms/teacher/dashboard.php");
    } else {
        header("Location: /gakumas-sms/student/dashboard.php");
    }
    exit();
}

$error = "";
$prefill = "";

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $prefill = $username;

    if(!$username) {
        $error = "Username required";
    } elseif (!$password) {
        $error = "Password Required";
    } else {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ? AND is_active = 1');
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['id'] = $user['id'];
            $_SESSION['user_name'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            try{
                $upd = $pdo->prepare('Update users SET last_login = NOW() WHERE id = ?');
                $upd ->execute([$user['id']]);
            }catch(PDOException $e){
                error_log('Failed to update last_login: ' . $e->getMessage());
            }

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

    <style>
    /* Login-page-specific styling (kept inline so this page works standalone) */
    body.login-page {
        min-height: 100vh;
        background-image: url("/gakumas-sms/assets/images/login_bg.webp");
        background-size: cover;
        background-position: center;
        display: flex;
        align-items: center;
        justify-content: flex-end;
        /* push to right */
        padding-right: 8%;
        position: relative;
        overflow: hidden;
    }

    /* Soft animated petals for login background */
    .petal {
        position: absolute;
        z-index: 3;
        pointer-events: none;
        opacity: 0.45;
        filter: drop-shadow(0 4px 6px rgba(255, 150, 180, 0.18));
        animation-name: petal-fall-soft;
        animation-timing-function: linear;
        animation-iteration-count: infinite;
    }

    .petal-1 {
        top: -8%;
        left: 18%;
        width: 34px;
        animation-duration: 26s;
        animation-delay: 0s;
    }

    .petal-2 {
        top: -10%;
        left: 48%;
        width: 28px;
        opacity: 0.35;
        animation-duration: 32s;
        animation-delay: 7s;
    }

    .petal-3 {
        top: -12%;
        left: 76%;
        width: 38px;
        opacity: 0.4;
        animation-duration: 29s;
        animation-delay: 14s;
    }

    @keyframes petal-fall-soft {
        0% {
            transform: translateY(-10vh) translateX(0) rotate(0deg);
            opacity: 0;
        }

        12% {
            opacity: 0.45;
        }

        30% {
            transform: translateY(25vh) translateX(16px) rotate(35deg);
        }

        55% {
            transform: translateY(55vh) translateX(-12px) rotate(80deg);
        }

        80% {
            transform: translateY(85vh) translateX(18px) rotate(125deg);
        }

        100% {
            transform: translateY(115vh) translateX(-8px) rotate(170deg);
            opacity: 0;
        }
    }

    .login-card {
        background: var(--bg-card);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-strong);
        padding: var(--space-7) var(--space-6);
        width: 100%;
        max-width: 420px;
        position: relative;
        z-index: 2;
    }

    .login-logo {
        text-align: center;
        margin-bottom: var(--space-5);
    }

    .login-logo img {
        width: 220px;
        max-width: 100%;
        height: auto;
    }

    .login-logo .star {
        color: var(--accent-gold);
        font-size: 1.5rem;
        animation: sparkle-pulse 1.5s infinite ease-in-out;
    }

    .login-subtitle {
        text-align: center;
        color: var(--text-secondary);
        margin-bottom: var(--space-6);
        font-size: 0.95rem;
    }

    .login-input-group {
        position: relative;
        margin-bottom: var(--space-4);
    }

    .login-input-group .input-icon {
        position: absolute;
        top: 50%;
        left: 16px;
        transform: translateY(-50%);
        color: var(--text-muted);
        pointer-events: none;
    }

    .login-input-group .form-control {
        padding-left: 44px;
    }

    .password-toggle {
        position: absolute;
        top: 50%;
        right: 12px;
        transform: translateY(-50%);
        background: transparent;
        border: none;
        color: var(--text-muted);
        cursor: pointer;
        padding: 4px 8px;
    }

    .password-toggle:hover {
        color: var(--primary);
    }

    .login-btn {
        position: relative;
        overflow: hidden;
        width: 100%;
        padding: 13px 18px;
        margin-top: var(--space-2);
        border: none;
        border-radius: 999px;
        background: linear-gradient(135deg, #f7a8c8 0%, #f5c1d8 45%, #f8dca8 100%);
        color: #ffffff;
        font-weight: 600;
        letter-spacing: 0.4px;
        box-shadow:
            0 10px 24px rgba(239, 132, 177, 0.32),
            inset 0 1px 0 rgba(255, 255, 255, 0.55);
        transition: all 0.25s ease;
    }

    .login-btn-content {
        position: relative;
        z-index: 2;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .login-btn-glow {
        position: absolute;
        top: 0;
        left: -80%;
        width: 60%;
        height: 100%;
        background: linear-gradient(120deg,
                transparent,
                rgba(255, 255, 255, 0.55),
                transparent);
        transform: skewX(-20deg);
        transition: 0.6s;
    }

    .login-btn:hover {
        color: #ffffff;
        transform: translateY(-2px);
        box-shadow:
            0 14px 30px rgba(239, 132, 177, 0.42),
            inset 0 1px 0 rgba(255, 255, 255, 0.7);
    }

    .login-btn:hover .login-btn-glow {
        left: 130%;
    }

    .login-btn:active {
        transform: translateY(0);
        box-shadow:
            0 7px 18px rgba(239, 132, 177, 0.3),
            inset 0 2px 4px rgba(180, 90, 125, 0.2);
    }

    .login-btn:focus {
        outline: none;
        box-shadow:
            0 0 0 4px rgba(247, 168, 200, 0.28),
            0 12px 26px rgba(239, 132, 177, 0.35);
    }

    .login-footer {
        text-align: center;
        margin-top: var(--space-5);
        font-size: 0.875rem;
        color: var(--text-muted);
    }

    @media (max-width: 768px) {
        body.login-page {
            justify-content: center;
            padding-right: 0;
        }
    }
    </style>

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
            <span class="star">★</span>
            Welcome back to Hatsuboshi Academy
            <span class="star">★</span>
        </p>

        <?php if($error !== ''): ?>
        <div class="alert alert-danger d-flex align-items-center" role="alert">
            <i class="bi bi-exclamation-circle-fill me-2"></i>
            <div><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        </div>
        <?php endif; ?>

        <form method="post" action="login.php" novalidate>
            <div class="login-input-group">
                <i class="bi bi-person-fill input-icon"></i>
                <input type="text" name="username" class="form-control" placeholder="Username"
                    value="<?= htmlspecialchars($prefill, ENT_QUOTES, "UTF-8") ?>" autocomplete="username" autofocus
                    required>
            </div>

            <div class="login-input-group">
                <i class="bi bi-lock-fill input-icon"></i>
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
            ★ Hatsuboshi Gakuen © 2026 ★
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