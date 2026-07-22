<?php

require_once __DIR__ . '/producer_request_helpers.php';

function ensure_admin_request_route_columns(PDO $pdo): void
{
    static $ensured = false;

    if ($ensured) {
        return;
    }

    $columns = [];
    foreach ($pdo->query('SHOW COLUMNS FROM student_update_requests')->fetchAll() as $column) {
        $columns[$column['Field']] = true;
    }

    if (empty($columns['forwarded_by'])) {
        $pdo->exec('ALTER TABLE student_update_requests ADD COLUMN forwarded_by INT NULL AFTER handled_by');
    }

    if (empty($columns['forwarded_at'])) {
        $pdo->exec('ALTER TABLE student_update_requests ADD COLUMN forwarded_at DATETIME NULL AFTER forwarded_by');
    }

    $indexes = [];
    foreach ($pdo->query('SHOW INDEX FROM student_update_requests')->fetchAll() as $index) {
        $indexes[$index['Key_name']] = true;
    }

    if (empty($indexes['idx_update_request_forwarded_by'])) {
        $pdo->exec('ALTER TABLE student_update_requests ADD KEY idx_update_request_forwarded_by (forwarded_by)');
    }

    $ensured = true;
}

function apply_admin_request_theme(): void
{
    $_SESSION['theme_primary_color'] = $_SESSION['theme_primary_color'] ?? DEFAULT_THEME_PRIMARY;
    $_SESSION['theme_secondary_color'] = $_SESSION['theme_secondary_color'] ?? DEFAULT_THEME_SECONDARY;
}

function load_admin_request_inbox(PDO $pdo): array
{
    ensure_admin_request_route_columns($pdo);

    $stmt = $pdo->query(
        'SELECT
            "student_update" AS source,
            r.*,
            s.name AS student_name,
            s.name_jp AS student_name_jp,
            s.school_year,
            s.rank,
            u.avatar AS student_avatar,
            recipient.username AS recipient_name,
            recipient.role AS recipient_role,
            forwarder.username AS forwarder_name,
            forwarder.role AS forwarder_role,
            forwarder.avatar AS forwarder_avatar
         FROM student_update_requests r
         INNER JOIN students s ON s.id = r.student_id
         INNER JOIN users u ON u.id = s.user_id
         INNER JOIN users recipient ON recipient.id = r.recipient_id
         LEFT JOIN users forwarder ON forwarder.id = r.forwarded_by
         WHERE recipient.role = "admin"
         ORDER BY FIELD(r.status, "pending", "in_progress", "approved", "rejected", "cancelled"),
                  r.created_at DESC'
    );

    return $stmt->fetchAll();
}

function load_admin_request_detail(PDO $pdo, int $request_id): ?array
{
    ensure_admin_request_route_columns($pdo);

    $stmt = $pdo->prepare(
        'SELECT
            "student_update" AS source,
            r.*,
            s.name AS student_name,
            s.name_jp AS student_name_jp,
            s.school_year,
            s.rank,
            s.birthday,
            s.zodiac,
            s.blood_type,
            s.height,
            s.weight,
            s.three_size,
            s.vocal,
            s.dance,
            s.visual,
            u.avatar AS student_avatar,
            recipient.username AS recipient_name,
            recipient.role AS recipient_role,
            forwarder.username AS forwarder_name,
            forwarder.role AS forwarder_role,
            forwarder.avatar AS forwarder_avatar,
            handler.username AS handler_name
         FROM student_update_requests r
         INNER JOIN students s ON s.id = r.student_id
         INNER JOIN users u ON u.id = s.user_id
         INNER JOIN users recipient ON recipient.id = r.recipient_id
         LEFT JOIN users forwarder ON forwarder.id = r.forwarded_by
         LEFT JOIN users handler ON handler.id = r.handled_by
         WHERE r.id = ?
           AND recipient.role = "admin"
         LIMIT 1'
    );
    $stmt->execute([$request_id]);

    return $stmt->fetch() ?: null;
}

function admin_request_source_label(array $request): string
{
    return !empty($request['forwarded_by'])
        ? 'Forwarded by Producer'
        : 'Direct from Student';
}

function admin_request_route_class(array $request): string
{
    return !empty($request['forwarded_by']) ? 'forwarded' : 'direct';
}
