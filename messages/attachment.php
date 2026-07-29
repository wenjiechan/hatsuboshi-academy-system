<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/messages_helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Allow: GET');
    http_response_code(405);
    exit;
}

$attachment_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$attachment_id || $attachment_id <= 0) {
    http_response_code(404);
    exit;
}

$attachment = get_message_attachment_for_user(
    $pdo,
    (int) $attachment_id,
    (int) $_SESSION['id']
);

if (!$attachment) {
    http_response_code(404);
    exit;
}

$storage_root = realpath(message_attachment_storage_root());
$relative_path = str_replace(
    ['/', '\\'],
    DIRECTORY_SEPARATOR,
    (string) $attachment['stored_path']
);
$absolute_path = $storage_root !== false
    ? realpath($storage_root . DIRECTORY_SEPARATOR . $relative_path)
    : false;
$root_prefix = $storage_root !== false
    ? rtrim($storage_root, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR
    : '';

if (
    $absolute_path === false
    || $root_prefix === ''
    || strncasecmp($absolute_path, $root_prefix, strlen($root_prefix)) !== 0
    || !is_file($absolute_path)
) {
    http_response_code(404);
    exit;
}

$original_name = str_replace(["\r", "\n"], '', (string) $attachment['original_name']);
$fallback_name = (string) preg_replace('/[^A-Za-z0-9._-]+/', '_', $original_name);
$fallback_name = trim($fallback_name, '._') ?: 'attachment';
$download_requested = filter_input(INPUT_GET, 'download', FILTER_VALIDATE_BOOL);
$disposition = !$download_requested && $attachment['attachment_type'] === 'image'
    ? 'inline'
    : 'attachment';

header('Content-Type: ' . (string) $attachment['mime_type']);
header('Content-Length: ' . filesize($absolute_path));
header('Content-Disposition: ' . $disposition
    . '; filename="' . $fallback_name . '"'
    . "; filename*=UTF-8''" . rawurlencode($original_name));
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, no-store, max-age=0');

session_write_close();
readfile($absolute_path);
exit;
