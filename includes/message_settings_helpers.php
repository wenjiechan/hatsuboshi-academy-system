<?php

const DEFAULT_MESSAGE_BACKGROUND = 'default';
const DEFAULT_MESSAGE_TEXT_SIZE = 'normal';
const DEFAULT_MESSAGE_COMPACT_LAYOUT = false;

function ensure_user_message_settings_schema(PDO $pdo): void
{
    static $ensured = false;

    if ($ensured) {
        return;
    }

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS user_message_settings (
            user_id INT NOT NULL,
            message_background VARCHAR(30) NOT NULL DEFAULT "default",
            message_text_size VARCHAR(20) NOT NULL DEFAULT "normal",
            compact_layout TINYINT(1) NOT NULL DEFAULT 0,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (user_id),
            CONSTRAINT fk_user_message_settings_user
                FOREIGN KEY (user_id) REFERENCES users(id)
                ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );

    $ensured = true;
}

function message_background_options(): array
{
    return [
        'default' => 'Default',
        'primary' => 'Primary tint',
        'secondary' => 'Secondary tint',
        'gradient' => 'Theme gradient',
        'plain' => 'Plain',
    ];
}

function message_text_size_options(): array
{
    return [
        'small' => 'Small',
        'normal' => 'Normal',
        'large' => 'Large',
    ];
}

function normalize_message_background(string $value): string
{
    return array_key_exists($value, message_background_options()) ? $value : DEFAULT_MESSAGE_BACKGROUND;
}

function normalize_message_text_size(string $value): string
{
    return array_key_exists($value, message_text_size_options()) ? $value : DEFAULT_MESSAGE_TEXT_SIZE;
}

function load_user_message_settings(PDO $pdo, int $user_id): array
{
    ensure_user_message_settings_schema($pdo);

    $stmt = $pdo->prepare(
        'SELECT message_background, message_text_size, compact_layout
         FROM user_message_settings
         WHERE user_id = ?
         LIMIT 1'
    );
    $stmt->execute([$user_id]);
    $settings = $stmt->fetch() ?: [];

    return [
        'message_background' => normalize_message_background((string) ($settings['message_background'] ?? DEFAULT_MESSAGE_BACKGROUND)),
        'message_text_size' => normalize_message_text_size((string) ($settings['message_text_size'] ?? DEFAULT_MESSAGE_TEXT_SIZE)),
        'compact_layout' => !empty($settings['compact_layout']),
    ];
}

function save_user_message_settings(
    PDO $pdo,
    int $user_id,
    string $message_background,
    string $message_text_size,
    bool $compact_layout
): void {
    ensure_user_message_settings_schema($pdo);

    $stmt = $pdo->prepare(
        'INSERT INTO user_message_settings
            (user_id, message_background, message_text_size, compact_layout)
         VALUES (?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE
            message_background = VALUES(message_background),
            message_text_size = VALUES(message_text_size),
            compact_layout = VALUES(compact_layout),
            updated_at = CURRENT_TIMESTAMP'
    );
    $stmt->execute([
        $user_id,
        normalize_message_background($message_background),
        normalize_message_text_size($message_text_size),
        $compact_layout ? 1 : 0,
    ]);
}

function message_settings_conversation_classes(array $settings): string
{
    $background = normalize_message_background((string) ($settings['message_background'] ?? DEFAULT_MESSAGE_BACKGROUND));
    $text_size = normalize_message_text_size((string) ($settings['message_text_size'] ?? DEFAULT_MESSAGE_TEXT_SIZE));
    $classes = [
        'message-bg-' . $background,
        'message-text-' . $text_size,
    ];

    if (!empty($settings['compact_layout'])) {
        $classes[] = 'message-layout-compact';
    }

    return implode(' ', $classes);
}
