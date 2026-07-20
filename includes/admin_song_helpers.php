<?php

// Escape text before printing it into HTML.
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

// Display TIME values as MM:SS for the song list.
function format_song_duration(?string $duration): string
{
    if (!$duration) {
        return '--:--';
    }

    $parts = explode(':', $duration);

    if (count($parts) !== 3) {
        return $duration;
    }

    return sprintf('%02d:%02d', (int) $parts[1], (int) $parts[2]);
}

// Display nullable release dates in a readable format.
function format_song_date(?string $date): string
{
    if (!$date) {
        return 'Not dated';
    }

    return date('M j, Y', strtotime($date));
}

// Normalize admin input into MySQL TIME format, while rejecting impossible MM/SS values.
function normalize_song_time(string $duration)
{
    $duration = trim($duration);

    if ($duration === '') {
        return null;
    }

    if (preg_match('/^(\d{1,2}):(\d{2})$/', $duration, $matches)) {
        $minutes = (int) $matches[1];
        $seconds = (int) $matches[2];

        if ($minutes > 59 || $seconds > 59) {
            return false;
        }

        return sprintf('00:%02d:%02d', $minutes, $seconds);
    }

    if (preg_match('/^(\d{1,2}):(\d{2}):(\d{2})$/', $duration, $matches)) {
        $hours = (int) $matches[1];
        $minutes = (int) $matches[2];
        $seconds = (int) $matches[3];

        if ($minutes > 59 || $seconds > 59) {
            return false;
        }

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }

    return false;
}

// HTML date inputs should already submit YYYY-MM-DD, but checkdate catches impossible dates.
function is_valid_song_date(string $date): bool
{
    if ($date === '') {
        return true;
    }

    $date_parts = explode('-', $date);

    if (count($date_parts) !== 3) {
        return false;
    }

    [$year, $month, $day] = array_map('intval', $date_parts);

    return preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) === 1
        && checkdate($month, $day, $year);
}

// Shared empty form state used by the page and by failed POST submissions.
function default_admin_song_form_values(): array
{
    return [
        'title' => '',
        'title_jp' => '',
        'artist' => '',
        'duration' => '',
        'release_date' => '',
        'song_type' => 'Group',
        'notes' => '',
    ];
}

// Pull only the global song fields from POST; assignment fields are handled separately.
function posted_admin_song_form_values(): array
{
    return [
        'title' => trim((string) ($_POST['title'] ?? '')),
        'title_jp' => trim((string) ($_POST['title_jp'] ?? '')),
        'artist' => trim((string) ($_POST['artist'] ?? '')),
        'duration' => trim((string) ($_POST['duration'] ?? '')),
        'release_date' => trim((string) ($_POST['release_date'] ?? '')),
        'song_type' => trim((string) ($_POST['song_type'] ?? 'Group')),
        'notes' => trim((string) ($_POST['notes'] ?? '')),
    ];
}

// Keep selected student IDs unique and numeric before validating them against the database.
function posted_admin_song_student_ids(): array
{
    $posted_student_ids = $_POST['student_ids'] ?? [];

    return is_array($posted_student_ids)
        ? array_values(array_unique(array_filter(array_map('intval', $posted_student_ids), static fn ($id) => $id > 0)))
        : [];
}

// Route every admin song POST action and return page state when validation fails.
function handle_admin_song_post(PDO $pdo, array $valid_types, array $default_form_values): array
{
    verify_csrf((string) ($_POST['csrf_token'] ?? ''));

    $action = (string) ($_POST['action'] ?? '');
    $posted_song_id = filter_input(INPUT_POST, 'song_id', FILTER_VALIDATE_INT);
    $selected_student_ids = posted_admin_song_student_ids();

    $result = [
        'song_page_error' => '',
        'form_values' => $default_form_values,
        'selected_student_ids' => $selected_student_ids,
        'show_create_form' => false,
        'edit_song_id' => 0,
        'manage_student_id' => 0,
    ];

    // Student-song assignment actions are submitted from admin/student_songs.php.
    if (in_array($action, ['admin_add_student_song', 'admin_remove_student_song'], true)) {
        return handle_admin_student_song_assignment($pdo, $action, $posted_song_id, $result);
    }

    // Global song delete actions are submitted from admin/songs.php.
    if (in_array($action, ['delete_song', 'delete_song_with_usage'], true)) {
        return handle_admin_song_delete($pdo, $action, $posted_song_id, $result);
    }

    $form_values = posted_admin_song_form_values();
    $result['form_values'] = $form_values;

    $normalized_duration = normalize_song_time($form_values['duration']);
    $song_page_error = validate_admin_song_form($pdo, $valid_types, $form_values, $normalized_duration, $action, $posted_song_id);

    if ($song_page_error === '') {
        $save_values = [
            $form_values['title'],
            $form_values['title_jp'] !== '' ? $form_values['title_jp'] : null,
            $form_values['artist'] !== '' ? $form_values['artist'] : null,
            $normalized_duration,
            $form_values['release_date'] !== '' ? $form_values['release_date'] : null,
            $form_values['song_type'],
            $form_values['notes'] !== '' ? $form_values['notes'] : null,
        ];

        if ($action === 'create_song') {
            $song_page_error = create_admin_song($pdo, $save_values, $selected_student_ids);
        }

        if ($action === 'update_song' && (!$posted_song_id || $posted_song_id <= 0)) {
            $song_page_error = 'Please choose a valid song to edit.';
        } elseif ($action === 'update_song') {
            $update_stmt = $pdo->prepare(
                'UPDATE songs
                 SET title = ?,
                     title_jp = ?,
                     artist = ?,
                     duration = ?,
                     release_date = ?,
                     song_type = ?,
                     notes = ?
                 WHERE id = ?'
            );
            $update_stmt->execute([...$save_values, (int) $posted_song_id]);
            $_SESSION['admin_song_success'] = 'Song updated successfully.';
            header('Location: /gakumas-sms/admin/songs.php');
            exit;
        }
    }

    $result['song_page_error'] = $song_page_error;
    $result['show_create_form'] = $action === 'create_song';
    $result['edit_song_id'] = $action === 'update_song' ? (int) ($posted_song_id ?? 0) : 0;

    return $result;
}

// Admin can manage song assignments for any active student, including unassigned students.
function handle_admin_student_song_assignment(PDO $pdo, string $action, $posted_song_id, array $result): array
{
    $posted_student_id = filter_input(INPUT_POST, 'student_id', FILTER_VALIDATE_INT);
    $result['manage_student_id'] = $posted_student_id && $posted_student_id > 0 ? (int) $posted_student_id : 0;

    if (!$posted_student_id || $posted_student_id <= 0 || !$posted_song_id || $posted_song_id <= 0) {
        $result['song_page_error'] = 'Please choose a valid student and song.';
        return $result;
    }

    $student_stmt = $pdo->prepare(
        'SELECT s.name
         FROM students s
         INNER JOIN users u ON u.id = s.user_id AND u.is_active = 1
         WHERE s.id = ?
         LIMIT 1'
    );
    $student_stmt->execute([(int) $posted_student_id]);
    $student = $student_stmt->fetch();

    if (!$student) {
        $result['song_page_error'] = 'The selected student could not be found.';
        return $result;
    }

    if ($action === 'admin_add_student_song') {
        // Confirm the global song exists before creating the student_songs link.
        $song_stmt = $pdo->prepare('SELECT title FROM songs WHERE id = ? LIMIT 1');
        $song_stmt->execute([(int) $posted_song_id]);
        $song = $song_stmt->fetch();

        if (!$song) {
            $result['song_page_error'] = 'The selected song could not be found.';
            return $result;
        }

        try {
            $add_stmt = $pdo->prepare(
                'INSERT INTO student_songs (student_id, song_id, added_by)
                 VALUES (?, ?, ?)'
            );
            $add_stmt->execute([(int) $posted_student_id, (int) $posted_song_id, (int) $_SESSION['id']]);
            $_SESSION['admin_song_success'] = 'Song added to ' . $student['name'] . '.';
            header('Location: /gakumas-sms/admin/student_songs.php?manage_student_id=' . (int) $posted_student_id . '#adminStudentSongs');
            exit;
        } catch (PDOException $exception) {
            $result['song_page_error'] = $exception->getCode() === '23000'
                ? 'This student already has that song.'
                : 'The song could not be added. Please try again.';
            return $result;
        }
    }

    if ($action === 'admin_remove_student_song') {
        // Remove only the assignment link. The global songs row remains untouched.
        $remove_stmt = $pdo->prepare(
            'DELETE FROM student_songs
             WHERE student_id = ?
               AND song_id = ?
             LIMIT 1'
        );
        $remove_stmt->execute([(int) $posted_student_id, (int) $posted_song_id]);

        if ($remove_stmt->rowCount() > 0) {
            $_SESSION['admin_song_success'] = 'Song removed from ' . $student['name'] . '.';
            header('Location: /gakumas-sms/admin/student_songs.php?manage_student_id=' . (int) $posted_student_id . '#adminStudentSongs');
            exit;
        }

        $result['song_page_error'] = 'That song is not assigned to this student.';
        return $result;
    }

    $result['song_page_error'] = 'Unknown student song action.';
    return $result;
}

// Validate global song data before insert/update so library rows stay consistent.
function validate_admin_song_form(PDO $pdo, array $valid_types, array $form_values, $normalized_duration, string $action, $posted_song_id): string
{
    if (!in_array($form_values['song_type'], $valid_types, true)) {
        return 'Please choose a valid song type.';
    }

    if ($form_values['title'] === '') {
        return 'Song title is required.';
    }

    if (mb_strlen($form_values['title']) > 150 || mb_strlen($form_values['title_jp']) > 150 || mb_strlen($form_values['artist']) > 150) {
        return 'Title, Japanese title, and artist must each be 150 characters or less.';
    }

    if ($normalized_duration === false) {
        return 'Duration must use MM:SS or HH:MM:SS format, with minutes and seconds from 00 to 59.';
    }

    if (!is_valid_song_date($form_values['release_date'])) {
        return 'Release date must be a real date using YYYY-MM-DD format.';
    }

    if (!in_array($action, ['create_song', 'update_song'], true)) {
        return 'Unknown song action.';
    }

    // Duplicate checking is case-insensitive; edit mode excludes the current song.
    $duplicate_params = [$form_values['title']];
    $duplicate_sql = 'SELECT id FROM songs WHERE LOWER(title) = LOWER(?)';

    if ($action === 'update_song' && $posted_song_id && $posted_song_id > 0) {
        $duplicate_sql .= ' AND id <> ?';
        $duplicate_params[] = (int) $posted_song_id;
    }

    $duplicate_stmt = $pdo->prepare($duplicate_sql . ' LIMIT 1');
    $duplicate_stmt->execute($duplicate_params);

    if ($duplicate_stmt->fetchColumn()) {
        return 'A song with this title already exists.';
    }

    return '';
}

// Create the global song and optional student assignments in one transaction.
function create_admin_song(PDO $pdo, array $save_values, array $selected_student_ids): string
{
    if (!empty($selected_student_ids)) {
        // Only active students may be assigned during create.
        $student_placeholders = implode(',', array_fill(0, count($selected_student_ids), '?'));
        $student_check_stmt = $pdo->prepare(
            'SELECT s.id
             FROM students s
             INNER JOIN users u ON u.id = s.user_id AND u.is_active = 1
             WHERE s.id IN (' . $student_placeholders . ')'
        );
        $student_check_stmt->execute($selected_student_ids);
        $valid_student_ids = array_map('intval', $student_check_stmt->fetchAll(PDO::FETCH_COLUMN));

        if (count($valid_student_ids) !== count($selected_student_ids)) {
            return 'Please choose valid students for this song.';
        }
    }

    try {
        $pdo->beginTransaction();

        $create_stmt = $pdo->prepare(
            'INSERT INTO songs
                (title, title_jp, artist, duration, release_date, song_type, notes, created_by)
             VALUES
                (?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $create_stmt->execute([...$save_values, (int) $_SESSION['id']]);
        $new_song_id = (int) $pdo->lastInsertId();

        if (!empty($selected_student_ids)) {
            $assign_stmt = $pdo->prepare(
                'INSERT INTO student_songs (student_id, song_id, added_by)
                 VALUES (?, ?, ?)'
            );

            foreach ($selected_student_ids as $student_id) {
                $assign_stmt->execute([(int) $student_id, $new_song_id, (int) $_SESSION['id']]);
            }
        }

        $pdo->commit();

        $assignment_count = count($selected_student_ids);
        $_SESSION['admin_song_success'] = 'Song created successfully.'
            . ($assignment_count > 0 ? ' Assigned to ' . $assignment_count . ' student' . ($assignment_count === 1 ? '' : 's') . '.' : '');
        header('Location: /gakumas-sms/admin/songs.php');
        exit;
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        return 'Could not create the song. Please try again.';
    }
}

// Delete safely: normal delete refuses used songs; force delete removes usage first.
function handle_admin_song_delete(PDO $pdo, string $action, $posted_song_id, array $result): array
{
    if (!$posted_song_id || $posted_song_id <= 0) {
        $result['song_page_error'] = 'Please choose a valid song to delete.';
        return $result;
    }

    try {
        if ($action === 'delete_song') {
            $delete_stmt = $pdo->prepare(
                'DELETE so
                 FROM songs so
                 LEFT JOIN student_songs ss ON ss.song_id = so.id
                 WHERE so.id = ?
                 AND ss.id IS NULL'
            );
            $delete_stmt->execute([(int) $posted_song_id]);

            if ($delete_stmt->rowCount() === 0) {
                $result['song_page_error'] = 'This song is still used by students. Remove it from students first, or use remove from all and delete.';
                return $result;
            }

            $_SESSION['admin_song_success'] = 'Song deleted successfully.';
            header('Location: /gakumas-sms/admin/songs.php');
            exit;
        }

        $pdo->beginTransaction();

        $remove_usage_stmt = $pdo->prepare('DELETE FROM student_songs WHERE song_id = ?');
        $remove_usage_stmt->execute([(int) $posted_song_id]);
        $removed_count = $remove_usage_stmt->rowCount();

        $delete_stmt = $pdo->prepare('DELETE FROM songs WHERE id = ?');
        $delete_stmt->execute([(int) $posted_song_id]);

        if ($delete_stmt->rowCount() === 0) {
            $pdo->rollBack();
            $result['song_page_error'] = 'The song you wanted to delete could not be found.';
            return $result;
        }

        $pdo->commit();
        $_SESSION['admin_song_success'] = 'Song removed from ' . $removed_count . ' student'
            . ($removed_count === 1 ? '' : 's') . ' and deleted successfully.';
        header('Location: /gakumas-sms/admin/songs.php');
        exit;
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        $result['song_page_error'] = 'Could not delete the song. Please try again.';
        return $result;
    }
}
