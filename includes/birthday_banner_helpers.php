<?php

date_default_timezone_set('Asia/Kuala_Lumpur');

//Get all students whose birthday is today
function get_dashboard_birthday_students(PDO $pdo): array
{
    $stmt = $pdo->prepare(
        'SELECT
            s.id,
            s.name,
            s.birthday,
            s.theme_primary_color,
            s.theme_secondary_color
         FROM students s
         INNER JOIN users u ON u.id = s.user_id
         WHERE DATE_FORMAT(s.birthday, "%m-%d") = ?
           AND u.is_active = 1
         ORDER BY s.name'
    );

    // Ignore the year and compare only month and date
    $stmt->execute([date('m-d')]);

    return $stmt->fetchAll();
}

//Checks whether a color is vaild
function birthday_banner_color(?string $color, string $fallback): string
{
    $color = trim((string) $color);

    return preg_match('/^#[0-9a-fA-F]{6}$/', $color) ? $color : $fallback;
}
