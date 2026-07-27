<?php

// Starts a producer request to end a student relationship.
function create_producer_remove_student_request(PDO $pdo, int $producer_id, int $student_id): int
{
    $student_stmt = $pdo->prepare(
        'SELECT
            s.id,
            s.user_id,
            s.name,
            s.producer_id,
            s.producer_status,
            producer.username AS producer_name
         FROM students s
         INNER JOIN users student_user
            ON student_user.id = s.user_id
           AND student_user.is_active = 1
         INNER JOIN users producer
            ON producer.id = s.producer_id
           AND producer.role = "producer"
           AND producer.is_active = 1
         WHERE s.id = ?
           AND s.producer_id = ?
         LIMIT 1'
    );
    $student_stmt->execute([$student_id, $producer_id]);
    $student = $student_stmt->fetch();

    if (!$student) {
        throw new RuntimeException('This student is not assigned to your producer account.');
    }

    $pending_stmt = $pdo->prepare(
        'SELECT id
         FROM producer_student_requests
         WHERE producer_id = ?
           AND student_id = ?
           AND request_type = "remove"
           AND status = "pending"
         LIMIT 1'
    );
    $pending_stmt->execute([$producer_id, $student_id]);
    $existing_request_id = $pending_stmt->fetchColumn();

    if ($existing_request_id !== false) {
        return (int) $existing_request_id;
    }

    $conversation_id = find_or_create_direct_conversation(
        $pdo,
        $producer_id,
        (int) $student['user_id']
    );

    $body = sprintf(
        '%s requested to end your producer relationship. Please choose Accept if you agree to become unassigned, or Reject if you want to keep this producer.',
        $student['producer_name'] ?: 'Your producer'
    );

    $pdo->beginTransaction();

    try {
        $request_stmt = $pdo->prepare(
            'INSERT INTO producer_student_requests
                (producer_id, student_id, request_type, status)
             VALUES
                (?, ?, "remove", "pending")'
        );
        $request_stmt->execute([$producer_id, $student_id]);
        $request_id = (int) $pdo->lastInsertId();

        $status_stmt = $pdo->prepare(
            'UPDATE students
             SET producer_status = "removal_pending"
             WHERE id = ?
               AND producer_id = ?'
        );
        $status_stmt->execute([$student_id, $producer_id]);

        $pdo->commit();
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        throw $exception;
    }

    $message_id = send_conversation_message(
        $pdo,
        $conversation_id,
        $producer_id,
        $body,
        MESSAGE_TYPE_PRODUCER_REMOVE_REQUEST,
        'producer_student_request',
        $request_id,
        'producer_remove_request:' . $request_id
    );

    $update_stmt = $pdo->prepare(
        'UPDATE producer_student_requests
         SET request_message_id = ?
         WHERE id = ?'
    );
    $update_stmt->execute([$message_id, $request_id]);

    if (
        function_exists('create_notification') &&
        defined('NOTIFICATION_TYPE_NEW_MESSAGE') &&
        !is_conversation_muted($pdo, $conversation_id, (int) $student['user_id'])
    ) {
        create_notification(
            $pdo,
            (int) $student['user_id'],
            NOTIFICATION_TYPE_NEW_MESSAGE,
            'Producer release request',
            ($student['producer_name'] ?: 'Your producer') . ' asked to end your relationship.',
            'message',
            $message_id,
            '/gakumas-sms/messages/view.php?id=' . $conversation_id,
            'new_message:' . $message_id . ':' . (int) $student['user_id']
        );
    }

    return $request_id;
}

// Starts a producer request to add an unassigned student.
function create_producer_add_student_request(PDO $pdo, int $producer_id, int $student_id): int
{
    $student_stmt = $pdo->prepare(
        'SELECT
            s.id,
            s.user_id,
            s.name,
            s.producer_id,
            s.producer_status,
            producer.username AS producer_name
         FROM students s
         INNER JOIN users student_user
            ON student_user.id = s.user_id
           AND student_user.is_active = 1
         INNER JOIN users producer
            ON producer.id = ?
           AND producer.role = "producer"
           AND producer.is_active = 1
         WHERE s.id = ?
           AND s.producer_id IS NULL
         LIMIT 1'
    );
    $student_stmt->execute([$producer_id, $student_id]);
    $student = $student_stmt->fetch();

    if (!$student) {
        throw new RuntimeException('This student is no longer unassigned.');
    }

    $pending_stmt = $pdo->prepare(
        'SELECT id, request_message_id
         FROM producer_student_requests
         WHERE producer_id = ?
           AND student_id = ?
           AND request_type = "add"
           AND status = "pending"
         LIMIT 1'
    );
    $pending_stmt->execute([$producer_id, $student_id]);
    $existing_request = $pending_stmt->fetch();

    if ($existing_request && !empty($existing_request['request_message_id'])) {
        return (int) $existing_request['id'];
    }

    if ($existing_request) {
        $cancel_stmt = $pdo->prepare(
            'UPDATE producer_student_requests
             SET status = "cancelled",
                 cancelled_at = NOW()
             WHERE id = ?
               AND status = "pending"'
        );
        $cancel_stmt->execute([(int) $existing_request['id']]);
    }

    $conversation_id = find_or_create_direct_conversation(
        $pdo,
        $producer_id,
        (int) $student['user_id']
    );

    $body = sprintf(
        '%s wants to become your producer. Please choose Accept if you agree, or Reject if you want to stay unassigned.',
        $student['producer_name'] ?: 'A producer'
    );

    $request_stmt = $pdo->prepare(
        'INSERT INTO producer_student_requests
            (producer_id, student_id, request_type, status)
         VALUES
            (?, ?, "add", "pending")'
    );
    $request_stmt->execute([$producer_id, $student_id]);
    $request_id = (int) $pdo->lastInsertId();

    $message_id = send_conversation_message(
        $pdo,
        $conversation_id,
        $producer_id,
        $body,
        MESSAGE_TYPE_PRODUCER_ADD_REQUEST,
        'producer_student_request',
        $request_id,
        'producer_add_request:' . $request_id
    );

    $update_stmt = $pdo->prepare(
        'UPDATE producer_student_requests
         SET request_message_id = ?
         WHERE id = ?'
    );
    $update_stmt->execute([$message_id, $request_id]);

    if (
        function_exists('create_notification') &&
        defined('NOTIFICATION_TYPE_NEW_MESSAGE') &&
        !is_conversation_muted($pdo, $conversation_id, (int) $student['user_id'])
    ) {
        create_notification(
            $pdo,
            (int) $student['user_id'],
            NOTIFICATION_TYPE_NEW_MESSAGE,
            'Producer add request',
            ($student['producer_name'] ?: 'A producer') . ' asked to become your producer.',
            'message',
            $message_id,
            '/gakumas-sms/messages/view.php?id=' . $conversation_id,
            'new_message:' . $message_id . ':' . (int) $student['user_id']
        );
    }

    return $request_id;
}

// Handles a student's accept/reject choice for a producer relationship request.
function respond_to_producer_student_request(
    PDO $pdo,
    int $request_id,
    int $student_user_id,
    string $action
): int {
    if (!in_array($action, ['accept', 'reject'], true)) {
        throw new InvalidArgumentException('Invalid request response.');
    }

    $request_stmt = $pdo->prepare(
        'SELECT
            request.id,
            request.producer_id,
            request.student_id,
            request.request_type,
            request.status,
            s.user_id AS student_user_id,
            s.name AS student_name,
            s.producer_id AS current_producer_id,
            producer.username AS producer_name
         FROM producer_student_requests request
         INNER JOIN students s ON s.id = request.student_id
         INNER JOIN users producer ON producer.id = request.producer_id
         WHERE request.id = ?
         LIMIT 1'
    );
    $request_stmt->execute([$request_id]);
    $request = $request_stmt->fetch();

    if (!$request || (int) $request['student_user_id'] !== $student_user_id) {
        throw new RuntimeException('This request is not available for your account.');
    }

    if (!in_array($request['request_type'], ['add', 'remove'], true)) {
        throw new RuntimeException('This request type is not supported yet.');
    }

    if ($request['status'] !== 'pending') {
        return find_or_create_direct_conversation(
            $pdo,
            (int) $request['producer_id'],
            $student_user_id
        );
    }

    if (
        $request['request_type'] === 'remove'
        && (int) $request['current_producer_id'] !== (int) $request['producer_id']
    ) {
        $cancel_stmt = $pdo->prepare(
            'UPDATE producer_student_requests
             SET status = "cancelled",
                 cancelled_at = NOW()
             WHERE id = ?
               AND status = "pending"'
        );
        $cancel_stmt->execute([$request_id]);

        throw new RuntimeException('This relationship has already changed.');
    }

    if (
        $request['request_type'] === 'add'
        && $request['current_producer_id'] !== null
    ) {
        $cancel_stmt = $pdo->prepare(
            'UPDATE producer_student_requests
             SET status = "cancelled",
                 cancelled_at = NOW()
             WHERE id = ?
               AND status = "pending"'
        );
        $cancel_stmt->execute([$request_id]);

        throw new RuntimeException('This student already has a producer.');
    }

    $conversation_id = find_or_create_direct_conversation(
        $pdo,
        (int) $request['producer_id'],
        $student_user_id
    );
    $accepted = $action === 'accept';

    if ($request['request_type'] === 'add') {
        $response_body = $accepted
            ? sprintf('%s accepted the producer request. The producer relationship is now active.', $request['student_name'])
            : sprintf('%s rejected the producer request. The student will stay unassigned.', $request['student_name']);
    } else {
        $response_body = $accepted
            ? sprintf('%s accepted the release request. The student is now unassigned.', $request['student_name'])
            : sprintf('%s rejected the release request. The producer relationship will continue.', $request['student_name']);
    }

    $message_id = send_conversation_message(
        $pdo,
        $conversation_id,
        $student_user_id,
        $response_body,
        MESSAGE_TYPE_SYSTEM,
        'producer_student_request',
        $request_id,
        'producer_remove_response:' . $request_id . ':' . ($accepted ? 'accepted' : 'rejected')
    );

    $pdo->beginTransaction();

    try {
        $update_request_stmt = $pdo->prepare(
            'UPDATE producer_student_requests
             SET status = ?,
                 response_message_id = ?,
                 responded_at = NOW()
             WHERE id = ?
               AND status = "pending"'
        );
        $update_request_stmt->execute([
            $accepted ? 'accepted' : 'rejected',
            $message_id,
            $request_id,
        ]);

        if ($request['request_type'] === 'add' && $accepted) {
            $student_stmt = $pdo->prepare(
                'UPDATE students
                 SET producer_id = ?,
                     producer_status = "active"
                 WHERE id = ?
                   AND producer_id IS NULL'
            );
            $student_stmt->execute([
                (int) $request['producer_id'],
                (int) $request['student_id'],
            ]);
        } elseif ($request['request_type'] === 'add') {
            $student_stmt = $pdo->prepare(
                'UPDATE students
                 SET producer_status = "unassigned"
                 WHERE id = ?
                   AND producer_id IS NULL'
            );
            $student_stmt->execute([(int) $request['student_id']]);
        } elseif ($accepted) {
            $student_stmt = $pdo->prepare(
                'UPDATE students
                 SET producer_id = NULL,
                     producer_status = "unassigned"
                 WHERE id = ?
                   AND producer_id = ?'
            );
            $student_stmt->execute([
                (int) $request['student_id'],
                (int) $request['producer_id'],
            ]);
        } else {
            $student_stmt = $pdo->prepare(
                'UPDATE students
                 SET producer_status = "active"
                 WHERE id = ?
                   AND producer_id = ?'
            );
            $student_stmt->execute([
                (int) $request['student_id'],
                (int) $request['producer_id'],
            ]);
        }

        $pdo->commit();
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        throw $exception;
    }

    if (
        function_exists('create_notification') &&
        defined('NOTIFICATION_TYPE_NEW_MESSAGE') &&
        !is_conversation_muted($pdo, $conversation_id, (int) $request['producer_id'])
    ) {
        create_notification(
            $pdo,
            (int) $request['producer_id'],
            NOTIFICATION_TYPE_NEW_MESSAGE,
            $request['request_type'] === 'add'
                ? 'Producer request response'
                : 'Release request response',
            $response_body,
            'message',
            $message_id,
            '/gakumas-sms/messages/view.php?id=' . $conversation_id,
            'new_message:' . $message_id . ':' . (int) $request['producer_id']
        );
    }

    return $conversation_id;
}
