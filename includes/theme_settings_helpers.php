<?php

//Default colors
const DEFAULT_THEME_PRIMARY = '#FF6B9D';
const DEFAULT_THEME_SECONDARY = '#FFB3D1';

//Add theme columns to users table
function ensure_user_theme_columns(PDO $pdo): void
{
    $pdo->exec(
        "ALTER TABLE users
         ADD COLUMN IF NOT EXISTS theme_primary_color varchar(20) DEFAULT '#FF6B9D',
         ADD COLUMN IF NOT EXISTS theme_secondary_color varchar(20) DEFAULT '#FFB3D1'"
    );
}

//Validate whether the color is valid HEX format
function normalize_theme_color(string $color, string $fallback): string
{
    $color = trim($color);

    if (preg_match('/^#[0-9A-Fa-f]{6}$/', $color)) {
        return strtoupper($color);
    }

    return $fallback;
}

//Load the user theme
function load_user_theme(PDO $pdo, int $user_id): array
{
    ensure_user_theme_columns($pdo);

    $stmt = $pdo->prepare(
        'SELECT theme_primary_color, theme_secondary_color
         FROM users
         WHERE id = ?
         LIMIT 1'
    );
    $stmt->execute([$user_id]);
    $theme = $stmt->fetch() ?: [];

    return [
        'primary' => $theme['theme_primary_color'] ?: DEFAULT_THEME_PRIMARY,
        'secondary' => $theme['theme_secondary_color'] ?: DEFAULT_THEME_SECONDARY,
    ];
}

//Saves the user theme
function save_user_theme(PDO $pdo, int $user_id, string $primary, string $secondary): void
{
    ensure_user_theme_columns($pdo);

    $stmt = $pdo->prepare(
        'UPDATE users
         SET theme_primary_color = ?, theme_secondary_color = ?
         WHERE id = ?'
    );
    $stmt->execute([$primary, $secondary, $user_id]);
}

//Store theme colors in session
function apply_theme_session(string $primary, string $secondary): void
{
    $_SESSION['theme_primary_color'] = $primary;
    $_SESSION['theme_secondary_color'] = $secondary;
}
