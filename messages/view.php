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

$conversation = get_conversation_details($pdo, (int) $conversation_id, $user_id);

if (!$conversation) {
    header('Location: ' . message_dashboard_url((string) $_SESSION['role']));
    exit;
}

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

            <div>
                <h2><?= htmlspecialchars($conversation['other_display_name'] ?? 'System', ENT_QUOTES, 'UTF-8') ?></h2>
                <p>
                    <?= $conversation['conversation_type'] === 'system'
                        ? 'System alerts'
                        : htmlspecialchars(ucfirst((string) ($conversation['other_role'] ?? 'system')), ENT_QUOTES, 'UTF-8') ?>
                    <?php if ($conversation['conversation_type'] !== 'system' && !empty($conversation['other_specialty'])): ?>
                        &middot; <?= htmlspecialchars(ucfirst((string) $conversation['other_specialty']), ENT_QUOTES, 'UTF-8') ?>
                    <?php endif; ?>
                </p>
            </div>
        </header>

        <?php if ($message_error): ?>
            <div class="message-form-error" role="alert">
                <i class="bi bi-exclamation-circle"></i>
                <?= htmlspecialchars($message_error, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <div class="conversation-thread" aria-label="Conversation messages" data-conversation-thread>
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
                    $type_label = chat_message_type_label((string) $message['message_type']);
                    ?>
                    <article class="chat-message<?= $is_own_message ? ' own' : '' ?><?= $type_label ? ' special' : '' ?>">
                        <div class="chat-message-bubble">
                            <?php if ($type_label): ?>
                                <span class="chat-message-type">
                                    <?= htmlspecialchars($type_label, ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            <?php endif; ?>

                            <p data-message-body><?= nl2br(htmlspecialchars($message['body'], ENT_QUOTES, 'UTF-8')) ?></p>

                            <div class="chat-message-meta">
                                <?php if (!empty($message['edited_at'])): ?>
                                    <span>Edited</span>
                                <?php endif; ?>
                                <time datetime="<?= htmlspecialchars($message['created_at'], ENT_QUOTES, 'UTF-8') ?>">
                                    <?= htmlspecialchars(format_chat_message_time($message['created_at']), ENT_QUOTES, 'UTF-8') ?>
                                </time>

                                <!--Only editable messages show the pencil button-->
                                <?php if (!empty($message['can_edit'])): ?>
                                    <button
                                        type="button"
                                        class="chat-message-edit-button"
                                        data-message-edit-open
                                        aria-label="Edit message"
                                        title="Edit message"
                                    >
                                        <i class="bi bi-pencil"></i>
                                    </button>
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
                <form method="post" action="/gakumas-sms/messages/send.php" class="message-composer">
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

                    <button type="submit" class="message-send-button" aria-label="Send message">
                        <i class="bi bi-send-fill"></i>
                    </button>
                </form>
            <?php endif; ?>
        </footer>
    </section>
</main>

<script src="/gakumas-sms/assets/js/messages.js" defer></script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
