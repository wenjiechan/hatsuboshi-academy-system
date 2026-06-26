<?php
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => !empty($_SERVER['HTTPS']),
    ]);
    session_start();
}

$expects_json = str_contains(
    strtolower((string) ($_SERVER['HTTP_ACCEPT'] ?? '')),
    'application/json'
);

function request_action_response(array $payload, int $status_code = 200): void
{
    http_response_code($status_code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

if ($expects_json) {
    if (empty($_SESSION['id']) || empty($_SESSION['role'])) {
        request_action_response([
            'error' => 'Please log in again.',
            'redirect_url' => '/gakumas-sms/login.php',
        ], 401);
    }

    if ($_SESSION['role'] !== 'student') {
        request_action_response([
            'error' => 'Only the student can answer this request.',
        ], 403);
    }
} else {
    require_once __DIR__ . '/../includes/auth.php';
    require_role('student');
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/messages_helpers.php';
require_once __DIR__ . '/../includes/notifications_helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if ($expects_json) {
        header('Allow: POST');
        request_action_response(['error' => 'Method not allowed'], 405);
    }

    header('Location: /gakumas-sms/messages/inbox.php');
    exit;
}

$submitted_csrf = (string) ($_POST['csrf_token'] ?? '');

if (!hash_equals($_SESSION['csrf_token'] ?? '', $submitted_csrf)) {
    if ($expects_json) {
        request_action_response(['error' => 'The security check could not be verified.'], 403);
    }

    verify_csrf($submitted_csrf);
}

$request_id = filter_input(INPUT_POST, 'request_id', FILTER_VALIDATE_INT);
$action = (string) ($_POST['action'] ?? '');

if (!$request_id || $request_id <= 0) {
    if ($expects_json) {
        request_action_response(['error' => 'This request is unavailable.'], 422);
    }

    $_SESSION['message_error'] = 'This request is unavailable.';
    header('Location: /gakumas-sms/messages/inbox.php');
    exit;
}

try {
    $conversation_id = respond_to_producer_student_request(
        $pdo,
        (int) $request_id,
        (int) $_SESSION['id'],
        $action
    );

    if ($expects_json) {
        $request_stmt = $pdo->prepare(
            'SELECT status, response_message_id
             FROM producer_student_requests
             WHERE id = ?
             LIMIT 1'
        );
        $request_stmt->execute([(int) $request_id]);
        $request = $request_stmt->fetch();

        $message = null;

        if (!empty($request['response_message_id'])) {
            $message_stmt = $pdo->prepare(
                'SELECT id, body, message_type, created_at, edited_at, deleted_at
                 FROM messages
                 WHERE id = ?
                   AND conversation_id = ?
                 LIMIT 1'
            );
            $message_stmt->execute([(int) $request['response_message_id'], $conversation_id]);
            $response_message = $message_stmt->fetch();

            if ($response_message) {
                $message = [
                    'id' => (int) $response_message['id'],
                    'body' => (string) $response_message['body'],
                    'message_type' => (string) $response_message['message_type'],
                    'created_at' => (string) $response_message['created_at'],
                    'edited_at' => $response_message['edited_at'],
                    'deleted_at' => $response_message['deleted_at'],
                    'is_own' => true,
                    'can_edit' => false,
                    'can_delete' => false,
                    'request_id' => (int) $request_id,
                    'request_status' => $request['status'] ?? null,
                    'can_respond_request' => false,
                ];
            }
        }

        request_action_response([
            'success' => true,
            'request_id' => (int) $request_id,
            'request_status' => (string) ($request['status'] ?? ($action === 'accept' ? 'accepted' : 'rejected')),
            'conversation_id' => $conversation_id,
            'message' => $message,
        ]);
    }

    header('Location: /gakumas-sms/messages/view.php?id=' . $conversation_id);
    exit;
} catch (InvalidArgumentException | RuntimeException $exception) {
    if ($expects_json) {
        request_action_response(['error' => $exception->getMessage()], 422);
    }

    $_SESSION['message_error'] = $exception->getMessage();
} catch (Throwable $exception) {
    if ($expects_json) {
        request_action_response(['error' => 'The request could not be updated. Please try again.'], 500);
    }

    $_SESSION['message_error'] = 'The request could not be updated. Please try again.';
}

header('Location: /gakumas-sms/messages/inbox.php');
exit;
