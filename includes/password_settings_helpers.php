<?php

function verify_current_user_password(PDO $pdo, int $user_id, string $current_password): bool
{
    if ($current_password === '') {
        return false;
    }

    $stmt = $pdo->prepare('SELECT password FROM users WHERE id = ? LIMIT 1');
    $stmt->execute([$user_id]);
    $stored_hash = $stmt->fetchColumn();

    return is_string($stored_hash) && password_verify($current_password, $stored_hash);
}

function change_verified_user_password(
    PDO $pdo,
    int $user_id,
    string $new_password,
    string $confirm_password
): string {
    if ($new_password === '' || $confirm_password === '') {
        return 'Both new password fields are required.';
    }

    if (strlen($new_password) < 6) {
        return 'The new password must be at least 6 characters.';
    }

    if (!hash_equals($new_password, $confirm_password)) {
        return 'The new password and confirmation do not match.';
    }

    $stmt = $pdo->prepare('SELECT password FROM users WHERE id = ? LIMIT 1');
    $stmt->execute([$user_id]);
    $stored_hash = $stmt->fetchColumn();

    if (!is_string($stored_hash)) {
        return 'The user account could not be found.';
    }

    if (password_verify($new_password, $stored_hash)) {
        return 'The new password must be different from the current password.';
    }

    $new_hash = password_hash($new_password, PASSWORD_DEFAULT);

    if ($new_hash === false) {
        return 'The password could not be secured. Please try again.';
    }

    $stmt = $pdo->prepare('UPDATE users SET password = ? WHERE id = ?');
    $stmt->execute([$new_hash, $user_id]);

    return '';
}
