<?php

const MESSAGE_ATTACHMENT_MAX_FILES = 5;
const MESSAGE_ATTACHMENT_MAX_FILE_SIZE = 10 * 1024 * 1024;
const MESSAGE_ATTACHMENT_MAX_TOTAL_SIZE = 25 * 1024 * 1024;

function ensure_message_attachment_schema(PDO $pdo): void
{
    static $ensured_connections = [];
    $connection_id = spl_object_id($pdo);

    if (!empty($ensured_connections[$connection_id])) {
        return;
    }

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS message_attachments (
            id INT NOT NULL AUTO_INCREMENT,
            message_id INT NOT NULL,
            original_name VARCHAR(255) NOT NULL,
            stored_path VARCHAR(255) NOT NULL,
            mime_type VARCHAR(150) NOT NULL,
            file_size BIGINT UNSIGNED NOT NULL,
            attachment_type VARCHAR(16) NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_message_attachments_message (message_id),
            CONSTRAINT fk_message_attachments_message
                FOREIGN KEY (message_id) REFERENCES messages (id)
                ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );

    $ensured_connections[$connection_id] = true;
}

function message_attachment_storage_root(): string
{
    return dirname(__DIR__) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'message_attachments';
}

function message_attachment_allowed_files(): array
{
    return [
        'jpg' => ['type' => 'image', 'mime' => ['image/jpeg']],
        'jpeg' => ['type' => 'image', 'mime' => ['image/jpeg']],
        'png' => ['type' => 'image', 'mime' => ['image/png']],
        'gif' => ['type' => 'image', 'mime' => ['image/gif']],
        'webp' => ['type' => 'image', 'mime' => ['image/webp']],
        'pdf' => ['type' => 'file', 'mime' => ['application/pdf']],
        'txt' => ['type' => 'file', 'mime' => ['text/plain']],
        'md' => ['type' => 'file', 'mime' => ['text/plain', 'text/markdown', 'text/html']],
        'csv' => ['type' => 'file', 'mime' => ['text/plain', 'text/csv', 'application/csv', 'application/vnd.ms-excel']],
        'json' => ['type' => 'file', 'mime' => ['application/json', 'text/plain']],
        'xml' => ['type' => 'file', 'mime' => ['application/xml', 'text/xml', 'text/plain']],
        'rtf' => ['type' => 'file', 'mime' => ['application/rtf', 'text/rtf']],
        'zip' => ['type' => 'file', 'mime' => ['application/zip', 'application/x-zip-compressed']],
        '7z' => ['type' => 'file', 'mime' => ['application/x-7z-compressed']],
        'rar' => ['type' => 'file', 'mime' => ['application/vnd.rar', 'application/x-rar-compressed']],
        'doc' => ['type' => 'file', 'mime' => ['application/msword', 'application/x-ole-storage']],
        'docx' => ['type' => 'file', 'mime' => [
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/zip',
        ]],
        'xls' => ['type' => 'file', 'mime' => ['application/vnd.ms-excel', 'application/x-ole-storage']],
        'xlsx' => ['type' => 'file', 'mime' => [
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/zip',
        ]],
        'ppt' => ['type' => 'file', 'mime' => ['application/vnd.ms-powerpoint', 'application/x-ole-storage']],
        'pptx' => ['type' => 'file', 'mime' => [
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'application/zip',
        ]],
        'odt' => ['type' => 'file', 'mime' => ['application/vnd.oasis.opendocument.text', 'application/zip']],
        'ods' => ['type' => 'file', 'mime' => ['application/vnd.oasis.opendocument.spreadsheet', 'application/zip']],
        'odp' => ['type' => 'file', 'mime' => ['application/vnd.oasis.opendocument.presentation', 'application/zip']],
    ];
}

function normalize_message_attachment_uploads(?array $upload): array
{
    if (!$upload || !isset($upload['name'], $upload['tmp_name'], $upload['error'], $upload['size'])) {
        return [];
    }

    $names = is_array($upload['name']) ? $upload['name'] : [$upload['name']];
    $temporary_names = is_array($upload['tmp_name']) ? $upload['tmp_name'] : [$upload['tmp_name']];
    $errors = is_array($upload['error']) ? $upload['error'] : [$upload['error']];
    $sizes = is_array($upload['size']) ? $upload['size'] : [$upload['size']];
    $uploads = [];

    foreach ($names as $index => $name) {
        $error = (int) ($errors[$index] ?? UPLOAD_ERR_NO_FILE);

        if ($error === UPLOAD_ERR_NO_FILE) {
            continue;
        }

        $uploads[] = [
            'name' => (string) $name,
            'tmp_name' => (string) ($temporary_names[$index] ?? ''),
            'error' => $error,
            'size' => (int) ($sizes[$index] ?? 0),
        ];
    }

    return $uploads;
}

function message_attachment_upload_error(int $error): string
{
    return match ($error) {
        UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'One of the attachments is larger than the server allows.',
        UPLOAD_ERR_PARTIAL => 'One of the attachments was only partly uploaded. Please try again.',
        UPLOAD_ERR_NO_TMP_DIR => 'The upload folder is unavailable.',
        UPLOAD_ERR_CANT_WRITE => 'The attachment could not be saved.',
        UPLOAD_ERR_EXTENSION => 'The server stopped one of the attachment uploads.',
        default => 'One of the attachments could not be uploaded.',
    };
}

function sanitize_message_attachment_name(string $name): string
{
    $name = basename(str_replace('\\', '/', $name));
    $name = trim((string) preg_replace('/[\x00-\x1F\x7F]+/u', ' ', $name));

    if ($name === '' || $name === '.' || $name === '..') {
        return 'attachment';
    }

    if (mb_strlen($name) <= 240) {
        return $name;
    }

    $extension = pathinfo($name, PATHINFO_EXTENSION);
    $suffix = $extension !== '' ? '.' . $extension : '';

    return mb_substr(pathinfo($name, PATHINFO_FILENAME), 0, 240 - mb_strlen($suffix)) . $suffix;
}

function cleanup_prepared_message_attachments(array $attachments): void
{
    foreach ($attachments as $attachment) {
        $absolute_path = (string) ($attachment['absolute_path'] ?? '');

        if (!empty($attachment['is_temporary']) && $absolute_path !== '' && is_file($absolute_path)) {
            @unlink($absolute_path);
        }
    }
}

function prepare_message_attachment_uploads(?array $upload): array
{
    $uploads = normalize_message_attachment_uploads($upload);

    if (count($uploads) > MESSAGE_ATTACHMENT_MAX_FILES) {
        throw new InvalidArgumentException(
            'You can attach up to ' . MESSAGE_ATTACHMENT_MAX_FILES . ' files to one message.'
        );
    }

    if ($uploads === []) {
        return [];
    }

    if (!class_exists('finfo')) {
        throw new RuntimeException('File type checking is unavailable on this server.');
    }

    $total_size = array_sum(array_column($uploads, 'size'));

    if ($total_size > MESSAGE_ATTACHMENT_MAX_TOTAL_SIZE) {
        throw new InvalidArgumentException('The selected attachments exceed the 25 MB total limit.');
    }

    $allowed_files = message_attachment_allowed_files();
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $prepared = [];

    try {
        foreach ($uploads as $upload_item) {
            if ((int) $upload_item['error'] !== UPLOAD_ERR_OK) {
                throw new InvalidArgumentException(message_attachment_upload_error((int) $upload_item['error']));
            }

            $size = (int) $upload_item['size'];

            if ($size <= 0) {
                throw new InvalidArgumentException('Empty files cannot be attached.');
            }

            if ($size > MESSAGE_ATTACHMENT_MAX_FILE_SIZE) {
                throw new InvalidArgumentException('Each attachment must be 10 MB or smaller.');
            }

            $temporary_path = (string) $upload_item['tmp_name'];

            if ($temporary_path === '' || !is_uploaded_file($temporary_path)) {
                throw new RuntimeException('The attachment upload could not be verified.');
            }

            $original_name = sanitize_message_attachment_name((string) $upload_item['name']);
            $extension = strtolower((string) pathinfo($original_name, PATHINFO_EXTENSION));
            $file_rule = $allowed_files[$extension] ?? null;

            if (!$file_rule) {
                throw new InvalidArgumentException(
                    'This file type is not supported. Attach an image, document, spreadsheet, presentation, or archive.'
                );
            }

            $mime_type = (string) $finfo->file($temporary_path);

            if ($mime_type === '' || !in_array($mime_type, $file_rule['mime'], true)) {
                throw new InvalidArgumentException('The contents of "' . $original_name . '" do not match its file type.');
            }

            if ($file_rule['type'] === 'image' && @getimagesize($temporary_path) === false) {
                throw new InvalidArgumentException('"' . $original_name . '" is not a valid image.');
            }

            $relative_directory = date('Y') . '/' . date('m');
            $absolute_directory = message_attachment_storage_root()
                . DIRECTORY_SEPARATOR
                . str_replace('/', DIRECTORY_SEPARATOR, $relative_directory);

            if (!is_dir($absolute_directory) && !mkdir($absolute_directory, 0775, true) && !is_dir($absolute_directory)) {
                throw new RuntimeException('The attachment folder could not be created.');
            }

            $stored_name = bin2hex(random_bytes(24)) . '.bin';
            $relative_path = $relative_directory . '/' . $stored_name;
            $absolute_path = $absolute_directory . DIRECTORY_SEPARATOR . $stored_name;

            if (!move_uploaded_file($temporary_path, $absolute_path)) {
                throw new RuntimeException('The attachment could not be saved.');
            }

            $prepared[] = [
                'original_name' => $original_name,
                'stored_path' => $relative_path,
                'mime_type' => $mime_type,
                'file_size' => $size,
                'attachment_type' => (string) $file_rule['type'],
                'absolute_path' => $absolute_path,
                'is_temporary' => true,
            ];
        }
    } catch (Throwable $exception) {
        cleanup_prepared_message_attachments($prepared);
        throw $exception;
    }

    return $prepared;
}

function insert_message_attachments(PDO $pdo, int $message_id, array $attachments): void
{
    if ($attachments === []) {
        return;
    }

    ensure_message_attachment_schema($pdo);
    $stmt = $pdo->prepare(
        'INSERT INTO message_attachments
            (message_id, original_name, stored_path, mime_type, file_size, attachment_type)
         VALUES (?, ?, ?, ?, ?, ?)'
    );

    foreach ($attachments as $attachment) {
        $stmt->execute([
            $message_id,
            (string) $attachment['original_name'],
            (string) $attachment['stored_path'],
            (string) $attachment['mime_type'],
            (int) $attachment['file_size'],
            (string) $attachment['attachment_type'],
        ]);
    }
}

function get_message_attachment_map(PDO $pdo, array $message_ids): array
{
    $message_ids = array_values(array_unique(array_filter(array_map('intval', $message_ids))));

    if ($message_ids === []) {
        return [];
    }

    ensure_message_attachment_schema($pdo);
    $placeholders = implode(',', array_fill(0, count($message_ids), '?'));
    $stmt = $pdo->prepare(
        'SELECT id, message_id, original_name, stored_path, mime_type, file_size, attachment_type
         FROM message_attachments
         WHERE message_id IN (' . $placeholders . ')
         ORDER BY message_id ASC, id ASC'
    );
    $stmt->execute($message_ids);
    $attachment_map = [];

    foreach ($stmt->fetchAll() as $attachment) {
        $message_id = (int) $attachment['message_id'];
        $attachment['id'] = (int) $attachment['id'];
        $attachment['message_id'] = $message_id;
        $attachment['file_size'] = (int) $attachment['file_size'];
        $attachment_map[$message_id][] = $attachment;
    }

    return $attachment_map;
}

function message_attachment_public_data(array $attachment): array
{
    $attachment_id = (int) ($attachment['id'] ?? 0);
    $base_url = '/gakumas-sms/messages/attachment.php?id=' . $attachment_id;

    return [
        'id' => $attachment_id,
        'original_name' => (string) ($attachment['original_name'] ?? 'Attachment'),
        'mime_type' => (string) ($attachment['mime_type'] ?? 'application/octet-stream'),
        'file_size' => (int) ($attachment['file_size'] ?? 0),
        'attachment_type' => (string) ($attachment['attachment_type'] ?? 'file'),
        'url' => $base_url,
        'download_url' => $base_url . '&download=1',
    ];
}

function hydrate_message_attachments(PDO $pdo, array $messages): array
{
    if ($messages === []) {
        return [];
    }

    $attachment_map = get_message_attachment_map(
        $pdo,
        array_column($messages, 'id')
    );

    foreach ($messages as &$message) {
        $message_id = (int) ($message['id'] ?? 0);
        $message['attachments'] = !empty($message['deleted_at'])
            ? []
            : array_map('message_attachment_public_data', $attachment_map[$message_id] ?? []);
    }
    unset($message);

    return $messages;
}

function get_message_attachment_records(PDO $pdo, int $message_id): array
{
    return get_message_attachment_map($pdo, [$message_id])[$message_id] ?? [];
}

function get_message_attachment_for_user(
    PDO $pdo,
    int $attachment_id,
    int $user_id
): ?array {
    ensure_message_attachment_schema($pdo);
    ensure_message_clear_schema($pdo);

    $stmt = $pdo->prepare(
        'SELECT
            attachment.id,
            attachment.message_id,
            attachment.original_name,
            attachment.stored_path,
            attachment.mime_type,
            attachment.file_size,
            attachment.attachment_type
         FROM message_attachments attachment
         INNER JOIN messages m ON m.id = attachment.message_id
         INNER JOIN conversations c ON c.id = m.conversation_id
         INNER JOIN conversation_participants current_participant
            ON current_participant.conversation_id = m.conversation_id
           AND current_participant.user_id = ?
           AND current_participant.deleted_at IS NULL
         WHERE attachment.id = ?
           AND m.deleted_at IS NULL
           AND (
               c.conversation_type <> "group"
               OR m.created_at >= current_participant.joined_at
           )
           AND (
               current_participant.cleared_at IS NULL
               OR m.created_at > current_participant.cleared_at
           )
         LIMIT 1'
    );
    $stmt->execute([$user_id, $attachment_id]);
    $attachment = $stmt->fetch();

    if (!$attachment) {
        return null;
    }

    $attachment['id'] = (int) $attachment['id'];
    $attachment['message_id'] = (int) $attachment['message_id'];
    $attachment['file_size'] = (int) $attachment['file_size'];

    return $attachment;
}
