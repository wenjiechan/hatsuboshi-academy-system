<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/messages_helpers.php';

// Get the conversation ID from URL
$user_id = (int) $_SESSION['id'];
$conversation_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$conversation_id) {
    header('Location: ' . message_dashboard_url((string) $_SESSION['role']));
    exit;
}

// Load the selected conversation and make sure the current user can access it.
$conversation = get_conversation_details($pdo, (int) $conversation_id, $user_id);

if (!$conversation) {
    header('Location: ' . message_dashboard_url((string) $_SESSION['role']));
    exit;
}

// Load messages for this conversation from oldest to newest.
// Mark the conversation as read after loading messages
$messages = get_conversation_messages($pdo, (int) $conversation_id, $user_id);
mark_conversation_read($pdo, (int) $conversation_id, $user_id);

$message_error = $_SESSION['message_error'] ?? null;
unset($_SESSION['message_error']);

// Displays messages time
function format_chat_message_time(string $date): string
{
    $timestamp = strtotime($date);

    return $timestamp ? date('M j, Y \a\t g:i A', $timestamp) : $date;
}

// Convert special messages into labels
function chat_message_type_label(string $type): ?string
{
    return match ($type) {
        MESSAGE_TYPE_BIRTHDAY => 'Birthday message',
        MESSAGE_TYPE_PRODUCER_ADD_REQUEST => 'Producer request',
        MESSAGE_TYPE_PRODUCER_REMOVE_REQUEST => 'Release request',
        MESSAGE_TYPE_SYSTEM => 'System message',
        default => null,
    };
}

$page_title = 'Conversation';
$page_styles = ['/gakumas-sms/assets/css/pages/messages.css'];
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<main class="dashboard-main messages-main conversation-page">
    <section class="conversation-box<?= $conversation['conversation_type'] === 'system' ? ' system-conversation' : '' ?>">
        <header class="conversation-header">
            <a href="/gakumas-sms/messages/inbox.php" class="message-back-link" aria-label="Back to inbox">
                <i class="bi bi-arrow-left"></i>
            </a>

            <img
                src="<?= htmlspecialchars(
                    message_avatar_path($conversation['other_avatar'], $conversation['other_role']),
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>"
                alt=""
                class="conversation-header-avatar"
            >

            <div class="conversation-header-copy">
                <h2><?= htmlspecialchars($conversation['other_display_name'] ?? 'System', ENT_QUOTES, 'UTF-8') ?></h2>
                <p>
                    <!--Hide the reply form for system conversations because they are read-only.-->
                    <?= $conversation['conversation_type'] === 'system'
                        ? 'System alerts'
                        : htmlspecialchars(ucfirst((string) ($conversation['other_role'] ?? 'system')), ENT_QUOTES, 'UTF-8') ?>
                    <?php if ($conversation['conversation_type'] !== 'system' && !empty($conversation['other_specialty'])): ?>
                        &middot; <?= htmlspecialchars(ucfirst((string) $conversation['other_specialty']), ENT_QUOTES, 'UTF-8') ?>
                    <?php endif; ?>
                </p>
            </div>

            <div class="conversation-action-menu" data-conversation-action-menu>
                <button
                    type="button"
                    class="conversation-action-menu-toggle"
                    aria-label="Conversation options"
                    aria-haspopup="menu"
                    aria-expanded="false"
                    data-conversation-action-toggle
                >
                    <i class="bi bi-three-dots-vertical" aria-hidden="true"></i>
                </button>

                <div class="conversation-action-menu-panel" role="menu" data-conversation-action-panel hidden>
                    <form method="post" action="/gakumas-sms/messages/mute.php" role="none">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" name="conversation_id" value="<?= (int) $conversation_id ?>">
                        <input type="hidden" name="action" value="<?= !empty($conversation['is_muted']) ? 'unmute' : 'mute' ?>">
                        <button type="submit" role="menuitem">
                            <i class="bi <?= !empty($conversation['is_muted']) ? 'bi-bell' : 'bi-bell-slash' ?>"></i>
                            <span><?= !empty($conversation['is_muted']) ? 'Unmute conversation' : 'Mute conversation' ?></span>
                        </button>
                    </form>

                    <form method="post" action="/gakumas-sms/messages/archive.php" role="none">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" name="conversation_id" value="<?= (int) $conversation_id ?>">
                        <input type="hidden" name="action" value="<?= !empty($conversation['is_archived']) ? 'restore' : 'archive' ?>">
                        <button type="submit" role="menuitem">
                            <i class="bi <?= !empty($conversation['is_archived']) ? 'bi-arrow-counterclockwise' : 'bi-archive' ?>"></i>
                            <span><?= !empty($conversation['is_archived']) ? 'Restore conversation' : 'Archive conversation' ?></span>
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <?php if ($message_error): ?>
            <div class="message-form-error" role="alert">
                <i class="bi bi-exclamation-circle"></i>
                <?= htmlspecialchars($message_error, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <div
            class="conversation-thread"
            aria-label="Conversation messages"
            data-conversation-thread
            data-conversation-id="<?= (int) $conversation_id ?>"
            data-last-message-id="<?= !empty($messages) ? (int) end($messages)['id'] : 0 ?>"
            data-csrf-token="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>"
        >
            <?php if (empty($messages)): ?>
                <div class="conversation-start-state">
                    <i class="bi bi-chat-heart"></i>
                    <strong>Start the conversation</strong>
                    <p>Send the first message to <?= htmlspecialchars($conversation['other_display_name'], ENT_QUOTES, 'UTF-8') ?>.</p>
                </div>
            <?php else: ?>
                <?php foreach ($messages as $message): ?>
                    <?php
                    $is_own_message = (int) ($message['sender_id'] ?? 0) === $user_id;
                    $is_deleted_message = !empty($message['deleted_at']);
                    $type_label = chat_message_type_label((string) $message['message_type']);
                    // Show Accept or Reject buttons when the student receives a pending producer request.
                    $is_pending_producer_request = !$is_deleted_message
                        && in_array($message['message_type'], [
                            MESSAGE_TYPE_PRODUCER_ADD_REQUEST,
                            MESSAGE_TYPE_PRODUCER_REMOVE_REQUEST,
                        ], true)
                        && ($message['related_type'] ?? '') === 'producer_student_request'
                        && in_array(($message['request_type'] ?? ''), ['add', 'remove'], true)
                        && ($message['request_status'] ?? '') === 'pending'
                        && $_SESSION['role'] === 'student'
                        && !$is_own_message;
                    ?>
                    <article
                        class="chat-message<?= $is_own_message ? ' own' : '' ?><?= $type_label ? ' special' : '' ?><?= $is_deleted_message ? ' deleted' : '' ?>"
                        data-message-id="<?= (int) $message['id'] ?>"
                    >
                        <div class="chat-message-bubble">
                            <?php if ($type_label): ?>
                                <span class="chat-message-type">
                                    <?= htmlspecialchars($type_label, ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            <?php endif; ?>

                            <p data-message-body>
                                <?php if ($is_deleted_message): ?>
                                    <i class="bi bi-slash-circle" aria-hidden="true"></i>
                                    <em>This message was deleted.</em>
                                <?php else: ?>
                                    <?= nl2br(htmlspecialchars($message['body'], ENT_QUOTES, 'UTF-8')) ?>
                                <?php endif; ?>
                            </p>

                            <div class="chat-message-meta">
                                <?php if (
                                    !$is_deleted_message
                                    && in_array($message['message_type'], [
                                        MESSAGE_TYPE_PRODUCER_ADD_REQUEST,
                                        MESSAGE_TYPE_PRODUCER_REMOVE_REQUEST,
                                    ], true)
                                    && !empty($message['request_status'])
                                ): ?>
                                    <span
                                        class="chat-request-status chat-request-status-<?= htmlspecialchars((string) $message['request_status'], ENT_QUOTES, 'UTF-8') ?>"
                                        data-request-status
                                    >
                                        <?= htmlspecialchars(ucwords(str_replace('_', ' ', (string) $message['request_status'])), ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                <?php endif; ?>

                                <?php if (!$is_deleted_message && !empty($message['edited_at'])): ?>
                                    <span data-message-edited>Edited</span>
                                <?php endif; ?>
                                <time datetime="<?= htmlspecialchars($message['created_at'], ENT_QUOTES, 'UTF-8') ?>">
                                    <?= htmlspecialchars(format_chat_message_time($message['created_at']), ENT_QUOTES, 'UTF-8') ?>
                                </time>

                                <?php if (!empty($message['can_edit']) || !empty($message['can_delete'])): ?>
                                    <div class="chat-message-action-menu" data-message-action-menu>
                                        <button
                                            type="button"
                                            class="chat-message-action-toggle"
                                            data-message-action-toggle
                                            aria-label="Message options"
                                            aria-haspopup="menu"
                                            aria-expanded="false"
                                        >
                                            <i class="bi bi-three-dots" aria-hidden="true"></i>
                                        </button>

                                        <div class="chat-message-action-panel" role="menu" data-message-action-panel hidden>
                                            <?php if (!empty($message['can_edit'])): ?>
                                                <button type="button" role="menuitem" data-message-edit-open>
                                                    <i class="bi bi-pencil"></i>
                                                    <span>Edit</span>
                                                </button>
                                            <?php endif; ?>

                                            <?php if (!empty($message['can_delete'])): ?>
                                                <form method="post" action="/gakumas-sms/messages/delete.php" class="chat-message-delete-form" role="none">
                                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                                                    <input type="hidden" name="conversation_id" value="<?= (int) $conversation_id ?>">
                                                    <input type="hidden" name="message_id" value="<?= (int) $message['id'] ?>">
                                                    <button type="submit" role="menuitem" data-message-delete-submit>
                                                        <i class="bi bi-trash3"></i>
                                                        <span>Delete</span>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <?php if (!empty($message['can_edit'])): ?>
                                <form
                                    method="post"
                                    action="/gakumas-sms/messages/edit.php"
                                    class="chat-message-edit-form"
                                    data-message-edit-form
                                    hidden
                                >
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="conversation_id" value="<?= (int) $conversation_id ?>">
                                    <input type="hidden" name="message_id" value="<?= (int) $message['id'] ?>">

                                    <label class="visually-hidden" for="editMessage<?= (int) $message['id'] ?>">Edit message</label>
                                    <textarea
                                        id="editMessage<?= (int) $message['id'] ?>"
                                        name="body"
                                        rows="2"
                                        maxlength="5000"
                                        required
                                    ><?= htmlspecialchars($message['body'], ENT_QUOTES, 'UTF-8') ?></textarea>

                                    <div>
                                        <button type="button" class="message-edit-cancel" data-message-edit-cancel>Cancel</button>
                                        <button type="submit" class="message-edit-save">Save</button>
                                    </div>
                                </form>
                            <?php endif; ?>

                            <?php if ($is_pending_producer_request): ?>
                                <form
                                    method="post"
                                    action="/gakumas-sms/messages/request_action.php"
                                    class="chat-request-actions"
                                    data-request-action-form
                                >
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="request_id" value="<?= (int) $message['request_id'] ?>">

                                    <button type="submit" name="action" value="reject" class="chat-request-button reject">
                                        <i class="bi bi-x-lg" aria-hidden="true"></i>
                                        Reject
                                    </button>

                                    <button type="submit" name="action" value="accept" class="chat-request-button accept">
                                        <i class="bi bi-check-lg" aria-hidden="true"></i>
                                        Accept
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <footer class="conversation-input-area">
            <?php if ($conversation['conversation_type'] === 'system'): ?>
                <div class="system-conversation-notice">
                    <i class="bi bi-shield-check"></i>
                    <span>System messages cannot be replied to.</span>
                </div>
            <?php else: ?>
                <div class="message-send-error" role="alert" data-message-send-error hidden></div>

                <form method="post" action="/gakumas-sms/messages/send.php" class="message-composer" data-message-composer>
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="conversation_id" value="<?= (int) $conversation_id ?>">

                    <label for="messageBody" class="visually-hidden">Message</label>
                    <textarea
                        id="messageBody"
                        name="body"
                        rows="1"
                        maxlength="5000"
                        placeholder="Write a message"
                        required
                        data-message-input
                    ></textarea>

                    <span class="message-character-count" data-message-character-count>0 / 5000</span>

                    <button type="submit" class="message-send-button" aria-label="Send message" data-message-send-button>
                        <i class="bi bi-send-fill"></i>
                    </button>
                </form>
            <?php endif; ?>
        </footer>
    </section>
</main>

<script src="/gakumas-sms/assets/js/messages.js" defer></script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
