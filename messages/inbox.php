<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/messages_helpers.php';

$user_id = (int) $_SESSION['id'];
$conversations = get_user_conversations($pdo, $user_id, true);
$active_conversations = array_values(array_filter(
    $conversations,
    static fn(array $conversation): bool => empty($conversation['is_archived'])
));
$archived_conversations = array_values(array_filter(
    $conversations,
    static fn(array $conversation): bool => !empty($conversation['is_archived'])
));
$total_unread = array_sum(array_map(
    static fn(array $conversation): int => (int) $conversation['unread_count'],
    $active_conversations
));

// Format the latest message time nicely
function format_message_inbox_time(?string $date): string
{
    if (empty($date)) {
        return '';
    }

    $timestamp = strtotime($date);

    if ($timestamp === false) {
        return $date;
    }

    if (date('Y-m-d', $timestamp) === date('Y-m-d')) {
        return date('g:i A', $timestamp);
    }

    if (date('Y', $timestamp) === date('Y')) {
        return date('M j', $timestamp);
    }

    return date('M j, Y', $timestamp);
}

// Shortens long messages for the inbox preview
function message_preview(?string $body, int $limit = 110): string
{
    $body = trim(preg_replace('/\s+/', ' ', (string) $body));

    if ($body === '') {
        return 'No messages yet.';
    }

    return mb_strlen($body) > $limit
        ? mb_substr($body, 0, $limit - 1) . '...'
        : $body;
}

// Deside the avatar image
function inbox_avatar_path(array $conversation): string
{
    $avatar = trim((string) ($conversation['other_avatar'] ?? ''));
    $role = (string) ($conversation['other_role'] ?? '');

    if ($avatar !== '') {
        if (preg_match('/^https?:\/\//i', $avatar) || str_starts_with($avatar, '/')) {
            return $avatar;
        }

        return $role === 'student'
            ? '/gakumas-sms/assets/images/avatars/idols/' . rawurlencode($avatar)
            : '/gakumas-sms/assets/images/avatars/' . rawurlencode($avatar);
    }

    return match ($role) {
        'producer' => '/gakumas-sms/assets/images/avatars/default_producer.webp',
        'teacher' => '/gakumas-sms/assets/images/avatars/default_teacher.webp',
        default => '/gakumas-sms/assets/images/avatars/default.webp',
    };
}

$page_title = 'Messages';
$page_styles = ['/gakumas-sms/assets/css/pages/messages.css'];
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<main class="dashboard-main messages-main">
    <section class="messages-hero">
        <div class="messages-hero-copy">
            <p class="dashboard-eyebrow">Conversations</p>
            <h2>Messages</h2>
            <p>Chat with students, producers, and teachers.</p>
        </div>

        <div class="messages-summary-grid" aria-label="Message summary" data-inbox-summary>
            <div>
                <span>Conversations</span>
                <strong><?= count($active_conversations) ?></strong>
            </div>
            <div>
                <span>Unread</span>
                <strong><?= $total_unread ?></strong>
            </div>
        </div>
    </section>

    <section class="messages-search-row" aria-label="Search conversations">
        <div class="messages-search-controls">
            <label class="messages-search-field" for="conversationSearch">
                <span class="visually-hidden">Search conversations</span>
                <i class="bi bi-search" aria-hidden="true"></i>
                <input
                    type="search"
                    id="conversationSearch"
                    placeholder="Search conversations"
                    autocomplete="off"
                    data-conversation-search
                >
            </label>

            <div class="messages-search-filter" data-search-filter-menu>
                <i class="bi bi-funnel messages-search-filter-icon" aria-hidden="true"></i>
                <button
                    type="button"
                    class="messages-search-filter-button"
                    aria-haspopup="listbox"
                    aria-expanded="false"
                    data-conversation-search-filter
                    data-filter-value="all"
                >
                    <span data-filter-label>All</span>
                </button>
                <i class="bi bi-chevron-down messages-search-filter-chevron" aria-hidden="true"></i>

                <div class="messages-search-filter-options" role="listbox" aria-label="Search by" hidden>
                    <button type="button" role="option" aria-selected="true" data-filter-option="all">
                        <i class="bi bi-grid"></i>
                        <span>All</span>
                        <i class="bi bi-check2 option-check"></i>
                    </button>
                    <button type="button" role="option" aria-selected="false" data-filter-option="name">
                        <i class="bi bi-person"></i>
                        <span>Name</span>
                        <i class="bi bi-check2 option-check"></i>
                    </button>
                    <button type="button" role="option" aria-selected="false" data-filter-option="messages">
                        <i class="bi bi-chat-left-text"></i>
                        <span>Messages</span>
                        <i class="bi bi-check2 option-check"></i>
                    </button>
                </div>
            </div>
        </div>

        <a href="/gakumas-sms/messages/compose.php" class="messages-compose-button">
            <i class="bi bi-pencil-square"></i>
            <span>New message</span>
        </a>
    </section>

    <div data-inbox-live-region>
        <?php if (empty($conversations)): ?>
            <section class="messages-empty-state">
                <i class="bi bi-chat-square-text"></i>
                <h3>No conversations yet</h3>
                <p>Start a conversation with another active user.</p>
                <a href="/gakumas-sms/messages/compose.php" class="messages-empty-action">
                    <i class="bi bi-pencil-square"></i>
                    New message
                </a>
            </section>
        <?php else: ?>
            <section class="messages-list-toolbar" aria-label="Inbox views">
                <div class="messages-view-tabs" role="group" aria-label="Filter conversations">
                    <button type="button" class="active" data-inbox-view="all" aria-pressed="true">
                        All
                        <span><?= count($active_conversations) ?></span>
                    </button>
                    <button type="button" data-inbox-view="unread" aria-pressed="false">
                        Unread
                        <span><?= count(array_filter(
                            $active_conversations,
                            static fn(array $conversation): bool => (int) $conversation['unread_count'] > 0
                        )) ?></span>
                    </button>
                    <button type="button" data-inbox-view="archived" aria-pressed="false">
                        Archived
                        <span><?= count($archived_conversations) ?></span>
                    </button>
                </div>

                <p class="messages-result-count" aria-live="polite">
                    Showing <strong data-visible-conversation-count><?= count($active_conversations) ?></strong>
                    <span data-conversation-count-label>conversation<?= count($active_conversations) === 1 ? '' : 's' ?></span>
                </p>
            </section>

            <section class="conversation-list" aria-label="Your conversations">
                <?php foreach ($conversations as $conversation): ?>
                    <?php
                    $display_name = get_message_user_display_name($conversation);
                    $unread_count = (int) $conversation['unread_count'];
                    $is_own_latest = (int) ($conversation['latest_sender_id'] ?? 0) === $user_id;
                    ?>
                    <a
                        href="/gakumas-sms/messages/view.php?id=<?= (int) $conversation['id'] ?>"
                        class="conversation-row<?= $unread_count > 0 ? ' unread' : '' ?>"
                        data-conversation-row
                        data-unread="<?= $unread_count > 0 ? 'true' : 'false' ?>"
                        data-archived="<?= !empty($conversation['is_archived']) ? 'true' : 'false' ?>"
                        data-search-name="<?= htmlspecialchars(
                            strtolower($display_name),
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"
                        data-search-content="<?= htmlspecialchars(
                            strtolower((string) ($conversation['latest_message_body'] ?? '')),
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"
                    >
                        <img
                            src="<?= htmlspecialchars(inbox_avatar_path($conversation), ENT_QUOTES, 'UTF-8') ?>"
                            alt=""
                            class="conversation-avatar"
                        >

                        <div class="conversation-copy">
                            <div class="conversation-heading">
                                <h3><?= htmlspecialchars($display_name, ENT_QUOTES, 'UTF-8') ?></h3>
                                <?php if (!empty($conversation['latest_message_at'])): ?>
                                    <time datetime="<?= htmlspecialchars($conversation['latest_message_at'], ENT_QUOTES, 'UTF-8') ?>">
                                        <?= htmlspecialchars(format_message_inbox_time($conversation['latest_message_at']), ENT_QUOTES, 'UTF-8') ?>
                                    </time>
                                <?php endif; ?>
                            </div>

                            <div class="conversation-preview">
                                <p>
                                    <?php if ($is_own_latest): ?>
                                        <span class="conversation-you">You:</span>
                                    <?php endif; ?>
                                    <?= htmlspecialchars(message_preview($conversation['latest_message_body']), ENT_QUOTES, 'UTF-8') ?>
                                </p>

                                <div class="conversation-indicators">
                                    <?php if (!empty($conversation['is_muted'])): ?>
                                        <i class="bi bi-bell-slash" title="Muted" aria-label="Muted"></i>
                                    <?php endif; ?>

                                    <?php if ($unread_count > 0): ?>
                                        <span class="conversation-unread-count" aria-label="<?= $unread_count ?> unread messages">
                                            <?= $unread_count > 99 ? '99+' : $unread_count ?>
                                        </span>
                                    <?php else: ?>
                                        <i class="bi bi-chevron-right" aria-hidden="true"></i>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <span class="conversation-role">
                                <?= htmlspecialchars(ucfirst((string) ($conversation['other_role'] ?? 'system')), ENT_QUOTES, 'UTF-8') ?>
                            </span>

                            <?php if (
                                !empty($conversation['latest_message_type']) &&
                                $conversation['latest_message_type'] !== MESSAGE_TYPE_TEXT
                            ): ?>
                                <span class="conversation-message-type">
                                    <?= htmlspecialchars(
                                        match ($conversation['latest_message_type']) {
                                            MESSAGE_TYPE_BIRTHDAY => 'Birthday',
                                            MESSAGE_TYPE_PRODUCER_ADD_REQUEST,
                                            MESSAGE_TYPE_PRODUCER_REMOVE_REQUEST => 'Request',
                                            default => 'System',
                                        },
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </a>
                <?php endforeach; ?>
            </section>

            <section class="messages-no-results" data-conversation-no-results hidden>
                <i class="bi bi-search"></i>
                <h3>No conversations found</h3>
                <p>Try a different name or message keyword.</p>
            </section>
        <?php endif; ?>
    </div>
</main>

<script src="/gakumas-sms/assets/js/messages.js" defer></script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
