<?php

function message_sticker_asset_directory(): string
{
    return dirname(__DIR__) . '/assets/images/stickers';
}

function message_sticker_valid_slug(string $value): bool
{
    return preg_match('/^[a-z0-9][a-z0-9-]{0,47}$/', $value) === 1;
}

function message_sticker_valid_file(string $value): bool
{
    return basename($value) === $value
        && preg_match('/^[a-z0-9][a-z0-9._-]{0,100}\.(?:png|webp|jpe?g)$/i', $value) === 1;
}

function message_sticker_asset_url(string $pack_id, string $file): string
{
    return '/gakumas-sms/assets/images/stickers/'
        . rawurlencode($pack_id)
        . '/'
        . rawurlencode($file);
}

// Built-in packs are local assets. A pack appears when its manifest and sticker files are present.
function message_sticker_packs(): array
{
    static $packs = null;

    if ($packs !== null) {
        return $packs;
    }

    $packs = [];
    $directory = message_sticker_asset_directory();

    if (!is_dir($directory)) {
        return $packs;
    }

    foreach (new DirectoryIterator($directory) as $entry) {
        if (!$entry->isDir() || $entry->isDot()) {
            continue;
        }

        $pack_id = $entry->getFilename();

        if (!message_sticker_valid_slug($pack_id)) {
            continue;
        }

        $manifest_path = $entry->getPathname() . '/manifest.json';

        if (!is_file($manifest_path)) {
            continue;
        }

        // Each local pack owns its metadata so adding default assets needs no database row.
        $manifest = json_decode((string) file_get_contents($manifest_path), true);

        if (!is_array($manifest) || !is_array($manifest['stickers'] ?? null)) {
            continue;
        }

        $name = trim((string) ($manifest['name'] ?? $pack_id));
        $name = mb_substr($name !== '' ? $name : $pack_id, 0, 60);
        $stickers = [];
        $used_ids = [];

        foreach ($manifest['stickers'] as $sticker) {
            if (!is_array($sticker)) {
                continue;
            }

            $sticker_id = trim((string) ($sticker['id'] ?? ''));
            $file = trim((string) ($sticker['file'] ?? ''));

            if (!message_sticker_valid_slug($sticker_id)
                || isset($used_ids[$sticker_id])
                || !message_sticker_valid_file($file)
                || !is_file($entry->getPathname() . '/' . $file)) {
                continue;
            }

            $used_ids[$sticker_id] = true;
            $label = trim((string) ($sticker['label'] ?? $sticker_id));
            $stickers[] = [
                'key' => $pack_id . '/' . $sticker_id,
                'id' => $sticker_id,
                'label' => mb_substr($label !== '' ? $label : $sticker_id, 0, 80),
                'url' => message_sticker_asset_url($pack_id, $file),
            ];
        }

        if ($stickers !== []) {
            $packs[] = [
                'id' => $pack_id,
                'name' => $name,
                'stickers' => $stickers,
            ];
        }
    }

    usort($packs, static fn(array $left, array $right): int => strcasecmp($left['name'], $right['name']));

    return $packs;
}

function get_message_sticker(?string $key): ?array
{
    $key = trim((string) $key);

    if ($key === '') {
        return null;
    }

    foreach (message_sticker_packs() as $pack) {
        foreach ($pack['stickers'] as $sticker) {
            if ($sticker['key'] === $key) {
                return $sticker + ['pack_id' => $pack['id'], 'pack_name' => $pack['name']];
            }
        }
    }

    return null;
}

function message_sticker_public_data(?string $key): ?array
{
    $sticker = get_message_sticker($key);

    return $sticker ? [
        'key' => $sticker['key'],
        'label' => $sticker['label'],
        'url' => $sticker['url'],
    ] : null;
}

function message_sticker_preview_label(?string $key): string
{
    $sticker = get_message_sticker($key);

    return $sticker ? 'Sticker: ' . $sticker['label'] : 'Sticker';
}

function ensure_message_sticker_schema(PDO $pdo): void
{
    static $ensured = false;

    if ($ensured) {
        return;
    }

    // Keep the feature deployable without requiring a manual schema import.
    $column_stmt = $pdo->prepare(
        'SELECT 1
         FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_NAME = "messages"
           AND COLUMN_NAME = "sticker_key"
         LIMIT 1'
    );
    $column_stmt->execute();

    if (!$column_stmt->fetchColumn()) {
        $pdo->exec('ALTER TABLE messages ADD COLUMN sticker_key VARCHAR(100) NULL AFTER related_id');
    }

    $type_stmt = $pdo->prepare(
        'SELECT COLUMN_TYPE
         FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_NAME = "messages"
           AND COLUMN_NAME = "message_type"
         LIMIT 1'
    );
    $type_stmt->execute();
    $column_type = (string) $type_stmt->fetchColumn();

    if (!str_contains(strtolower($column_type), "'sticker'")) {
        $pdo->exec(
            'ALTER TABLE messages MODIFY COLUMN message_type '
            . 'ENUM("text", "birthday", "producer_add_request", "producer_remove_request", "system", "sticker") '
            . 'NOT NULL DEFAULT "text"'
        );
    }

    $index_stmt = $pdo->prepare(
        'SELECT 1
         FROM information_schema.STATISTICS
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_NAME = "messages"
           AND INDEX_NAME = "idx_messages_sticker_key"
         LIMIT 1'
    );
    $index_stmt->execute();

    if (!$index_stmt->fetchColumn()) {
        $pdo->exec('CREATE INDEX idx_messages_sticker_key ON messages (sticker_key)');
    }

    $ensured = true;
}
