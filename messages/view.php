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
$pinned_messages = get_pinned_conversation_messages($pdo, (int) $conversation_id, $user_id);
mark_conversation_read($pdo, (int) $conversation_id, $user_id);

$message_error = $_SESSION['message_error'] ?? null;
unset($_SESSION['message_error']);
$is_group_conversation = $conversation['conversation_type'] === 'group';
$current_user_display_name = message_user_display_name($pdo, $user_id);
$group_members = $is_group_conversation
    ? get_group_members($pdo, (int) $conversation_id, $user_id)
    : [];
$group_member_ids = array_map(static fn(array $member): int => (int) $member['user_id'], $group_members);
$group_add_contacts = $is_group_conversation && !empty($conversation['is_group_admin'])
    ? array_values(array_filter(
        get_group_message_contacts($pdo, $user_id, (string) $_SESSION['role']),
        static fn(array $contact): bool => !in_array((int) $contact['id'], $group_member_ids, true)
    ))
    : [];
$group_read_receipts = $is_group_conversation
    ? get_group_message_read_receipts($pdo, (int) $conversation_id, $user_id)
    : [];
$group_mention_members = $is_group_conversation
    ? array_values(array_map(
        static fn(array $member): array => [
            'user_id' => (int) $member['user_id'],
            'display_name' => (string) $member['display_name'],
            'role_detail' => group_member_role_detail($member),
            'avatar' => message_avatar_path($member['avatar'], $member['role']),
        ],
        $group_members
    ))
    : [];
$current_group_avatar = trim((string) ($conversation['group_avatar'] ?? $conversation['other_avatar'] ?? ''));

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

function chat_sender_display_name(array $message): string
{
    return trim((string) ($message['sender_student_name'] ?? ''))
        ?: trim((string) ($message['sender_teacher_name'] ?? ''))
        ?: trim((string) ($message['sender_username'] ?? ''))
        ?: 'System';
}

function pinned_message_preview(string $body, int $limit = 120): string
{
    $body = trim(preg_replace('/\s+/', ' ', $body));

    return mb_strlen($body) > $limit
        ? mb_substr($body, 0, $limit - 1) . '...'
        : $body;
}

function group_member_role_detail(array $member): string
{
    return ($member['role'] ?? '') === 'teacher' && !empty($member['specialty'])
        ? ucfirst((string) $member['specialty']) . ' teacher'
        : ucfirst((string) ($member['role'] ?? 'member'));
}

function chat_message_body_html(
    string $body,
    array $mention_members,
    string $current_user_display_name,
    bool $is_group_conversation
): string
{
    if (!$is_group_conversation) {
        return nl2br(htmlspecialchars($body, ENT_QUOTES, 'UTF-8'));
    }

    $mention_names = array_values(array_unique(array_filter(array_map(
        static fn(array $member): string => (string) ($member['display_name'] ?? ''),
        $mention_members
    ))));
    $mention_names[] = 'everyone';
    usort($mention_names, static fn(string $left, string $right): int => mb_strlen($right) <=> mb_strlen($left));
    $mention_pattern = implode('|', array_map(static fn(string $name): string => preg_quote($name, '/'), $mention_names));

    if ($mention_pattern === '') {
        return nl2br(htmlspecialchars($body, ENT_QUOTES, 'UTF-8'));
    }

    $pattern = '/(^|[\s(])@(' . $mention_pattern . ')(?=$|[\s.,!?;:)\]])/iu';
    $html = '';
    $last_end = 0;

    if (preg_match_all($pattern, $body, $matches, PREG_OFFSET_CAPTURE) !== false) {
        foreach ($matches[0] as $index => $match) {
            [$full_match, $offset] = $match;
            $prefix = (string) $matches[1][$index][0];
            $name = (string) $matches[2][$index][0];
            $mention_start = $offset + strlen($prefix);

            if ($mention_start > $last_end) {
                $html .= nl2br(htmlspecialchars(substr($body, $last_end, $mention_start - $last_end), ENT_QUOTES, 'UTF-8'));
            }

            $mention_text = '@' . $name;
            // All valid mentions are colored; only mentions for the current viewer get the background.
            $is_targeted = strcasecmp($name, 'everyone') === 0 || strcasecmp($name, $current_user_display_name) === 0;
            $class = 'chat-mention' . ($is_targeted ? ' chat-mention-targeted' : '');
            $html .= '<span class="' . $class . '">' . htmlspecialchars($mention_text, ENT_QUOTES, 'UTF-8') . '</span>';
            $last_end = $offset + strlen($full_match);
        }
    }

    if ($last_end < strlen($body)) {
        $html .= nl2br(htmlspecialchars(substr($body, $last_end), ENT_QUOTES, 'UTF-8'));
    }

    return $html !== '' ? $html : nl2br(htmlspecialchars($body, ENT_QUOTES, 'UTF-8'));
}

$page_title = 'Conversation';
$page_styles = ['/gakumas-sms/assets/css/pages/messages.css'];
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<main class="dashboard-main messages-main conversation-page">
    <section class="conversation-box<?= $conversation['conversation_type'] === 'system' ? ' system-conversation' : '' ?><?= $is_group_conversation ? ' group-conversation' : '' ?>">
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
                <h2>
                    <?php if ($is_group_conversation): ?>
                        <button type="button" class="group-title-button" aria-haspopup="dialog" data-modal-open="groupMembersModal">
                            <?= htmlspecialchars($conversation['other_display_name'] ?? 'Group chat', ENT_QUOTES, 'UTF-8') ?>
                        </button>
                    <?php else: ?>
                        <?= htmlspecialchars($conversation['other_display_name'] ?? 'System', ENT_QUOTES, 'UTF-8') ?>
                    <?php endif; ?>
                </h2>
                <p>
                    <!--Hide the reply form for system conversations because they are read-only.-->
                    <?= $is_group_conversation
                        ? htmlspecialchars((int) $conversation['member_count'] . ' members', ENT_QUOTES, 'UTF-8')
                        : ($conversation['conversation_type'] === 'system'
                            ? 'System alerts'
                            : htmlspecialchars(ucfirst((string) ($conversation['other_role'] ?? 'system')), ENT_QUOTES, 'UTF-8')) ?>
                    <?php if (!$is_group_conversation && $conversation['conversation_type'] !== 'system' && !empty($conversation['other_specialty'])): ?>
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

                    <?php if ($is_group_conversation): ?>
                        <?php if (!empty($conversation['is_group_admin'])): ?>
                            <button type="button" role="menuitem" aria-haspopup="dialog" data-modal-open="groupSettingsModal">
                                <i class="bi bi-pencil-square"></i>
                                <span>Group settings</span>
                            </button>
                        <?php endif; ?>

                        <?php if (!empty($conversation['is_group_admin']) && !empty($group_add_contacts)): ?>
                            <button type="button" role="menuitem" aria-haspopup="dialog" data-modal-open="groupAddMembersModal">
                                <i class="bi bi-person-plus"></i>
                                <span>Add members</span>
                            </button>
                        <?php endif; ?>

                        <form method="post" action="/gakumas-sms/messages/group_leave.php" role="none">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="conversation_id" value="<?= (int) $conversation_id ?>">
                            <button type="submit" role="menuitem" data-group-leave-submit>
                                <i class="bi bi-box-arrow-right"></i>
                                <span>Leave group</span>
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </header>

        <?php if ($message_error): ?>
            <div class="message-form-error" role="alert">
                <i class="bi bi-exclamation-circle"></i>
                <?= htmlspecialchars($message_error, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <?php if ($is_group_conversation): ?>
            <?php if (!empty($conversation['is_group_admin'])): ?>
                <div class="message-modal" id="groupSettingsModal" role="dialog" aria-modal="true" aria-labelledby="groupSettingsTitle" hidden>
                    <div class="message-modal-backdrop" data-modal-close></div>
                    <section class="message-modal-panel group-settings-modal">
                        <header class="message-modal-header">
                            <div>
                                <h3 id="groupSettingsTitle">Group settings</h3>
                                <p>Rename the group or change its avatar.</p>
                            </div>
                            <button type="button" class="message-modal-close" aria-label="Close" data-modal-close>
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </header>

                        <form method="post" action="/gakumas-sms/messages/group_update.php" class="group-settings-form" enctype="multipart/form-data">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="conversation_id" value="<?= (int) $conversation_id ?>">

                            <div class="group-settings-body">
                                <label class="group-name-field" for="settingsGroupName">
                                    <span>Group name</span>
                                    <input
                                        type="text"
                                        id="settingsGroupName"
                                        name="group_name"
                                        maxlength="100"
                                        value="<?= htmlspecialchars($conversation['other_display_name'] ?? 'Group chat', ENT_QUOTES, 'UTF-8') ?>"
                                        required
                                    >
                                </label>

                                <label class="group-avatar-upload" for="settingsGroupAvatar">
                                    <span>Group avatar</span>
                                    <span class="group-avatar-current">
                                        <img
                                            src="<?= htmlspecialchars(message_avatar_path($current_group_avatar, 'group'), ENT_QUOTES, 'UTF-8') ?>"
                                            alt=""
                                        >
                                        <small>Current avatar</small>
                                    </span>
                                    <input type="file" id="settingsGroupAvatar" name="group_avatar" accept="image/jpeg,image/png,image/webp">
                                    <small>No file selected will keep the current avatar.</small>
                                </label>
                            </div>

                            <footer class="message-modal-actions">
                                <button type="button" class="message-modal-secondary" data-modal-close>Cancel</button>
                                <button type="submit" class="message-modal-primary">
                                    <i class="bi bi-check2"></i>
                                    Save changes
                                </button>
                            </footer>
                        </form>
                    </section>
                </div>
            <?php endif; ?>

            <div class="message-modal" id="groupMembersModal" role="dialog" aria-modal="true" aria-labelledby="groupMembersTitle" hidden>
                <div class="message-modal-backdrop" data-modal-close></div>
                <section class="message-modal-panel group-members-modal">
                    <header class="message-modal-header">
                        <div>
                            <h3 id="groupMembersTitle"><?= htmlspecialchars($conversation['other_display_name'] ?? 'Group chat', ENT_QUOTES, 'UTF-8') ?></h3>
                            <p><?= count($group_members) ?> members</p>
                        </div>
                        <button type="button" class="message-modal-close" aria-label="Close" data-modal-close>
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </header>

                    <label class="modal-search-field" for="groupMembersSearch">
                        <span class="visually-hidden">Search members</span>
                        <i class="bi bi-search" aria-hidden="true"></i>
                        <input
                            type="search"
                            id="groupMembersSearch"
                            placeholder="Search members"
                            autocomplete="off"
                            data-modal-search
                            data-modal-search-target="groupMembersList"
                        >
                    </label>

                    <div class="group-member-list" id="groupMembersList">
                        <?php foreach ($group_members as $member): ?>
                            <article
                                class="group-member-item"
                                data-modal-search-row
                                data-modal-search-text="<?= htmlspecialchars(
                                    strtolower($member['display_name'] . ' ' . group_member_role_detail($member)),
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                            >
                                <?php if ((int) $member['user_id'] !== $user_id): ?>
                                    <form method="post" action="/gakumas-sms/messages/member_conversation.php" class="group-member-open-form">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                                        <input type="hidden" name="group_conversation_id" value="<?= (int) $conversation_id ?>">
                                        <input type="hidden" name="member_id" value="<?= (int) $member['user_id'] ?>">
                                        <button type="submit" class="group-member-open-button">
                                            <img
                                                src="<?= htmlspecialchars(message_avatar_path($member['avatar'], $member['role']), ENT_QUOTES, 'UTF-8') ?>"
                                                alt=""
                                                class="group-member-avatar"
                                            >
                                            <span>
                                                <strong><?= htmlspecialchars($member['display_name'], ENT_QUOTES, 'UTF-8') ?></strong>
                                                <small>
                                                    <?= htmlspecialchars(group_member_role_detail($member), ENT_QUOTES, 'UTF-8') ?>
                                                    <?= !empty($member['is_group_admin']) ? ' &middot; Admin' : '' ?>
                                                </small>
                                            </span>
                                            <i class="bi bi-chevron-right" aria-hidden="true"></i>
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <div class="group-member-open-button is-current-user">
                                        <img
                                            src="<?= htmlspecialchars(message_avatar_path($member['avatar'], $member['role']), ENT_QUOTES, 'UTF-8') ?>"
                                            alt=""
                                            class="group-member-avatar"
                                        >
                                        <span>
                                            <strong><?= htmlspecialchars($member['display_name'], ENT_QUOTES, 'UTF-8') ?></strong>
                                            <small>
                                                <?= htmlspecialchars(group_member_role_detail($member), ENT_QUOTES, 'UTF-8') ?>
                                                <?= !empty($member['is_group_admin']) ? ' &middot; Admin' : '' ?>
                                            </small>
                                        </span>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($conversation['is_group_admin']) && (int) $member['user_id'] !== $user_id): ?>
                                    <form method="post" action="/gakumas-sms/messages/group_remove_member.php">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                                        <input type="hidden" name="conversation_id" value="<?= (int) $conversation_id ?>">
                                        <input type="hidden" name="member_id" value="<?= (int) $member['user_id'] ?>">
                                        <button
                                            type="submit"
                                            aria-label="Remove <?= htmlspecialchars($member['display_name'], ENT_QUOTES, 'UTF-8') ?>"
                                            data-member-name="<?= htmlspecialchars($member['display_name'], ENT_QUOTES, 'UTF-8') ?>"
                                            data-group-remove-submit
                                        >
                                            <i class="bi bi-person-dash"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </article>
                        <?php endforeach; ?>
                    </div>

                    <div class="modal-no-results" data-modal-search-empty="groupMembersList" hidden>
                        <i class="bi bi-search"></i>
                        <strong>No members found</strong>
                    </div>
                </section>
            </div>

            <?php if (!empty($conversation['is_group_admin']) && !empty($group_add_contacts)): ?>
                <div class="message-modal" id="groupAddMembersModal" role="dialog" aria-modal="true" aria-labelledby="groupAddMembersTitle" hidden>
                    <div class="message-modal-backdrop" data-modal-close></div>
                    <section class="message-modal-panel group-add-members-modal">
                        <header class="message-modal-header">
                            <div>
                                <h3 id="groupAddMembersTitle">Add members</h3>
                                <p><?= count($group_add_contacts) ?> available users</p>
                            </div>
                            <button type="button" class="message-modal-close" aria-label="Close" data-modal-close>
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </header>

                        <form method="post" action="/gakumas-sms/messages/group_add_member.php" class="group-add-members-form">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="conversation_id" value="<?= (int) $conversation_id ?>">

                            <label class="modal-search-field" for="groupAddMembersSearch">
                                <span class="visually-hidden">Search available users</span>
                                <i class="bi bi-search" aria-hidden="true"></i>
                                <input
                                    type="search"
                                    id="groupAddMembersSearch"
                                    placeholder="Search users to add"
                                    autocomplete="off"
                                    data-modal-search
                                    data-modal-search-target="groupAddMembersList"
                                >
                            </label>

                            <div class="group-add-member-list" id="groupAddMembersList">
                                <?php foreach ($group_add_contacts as $contact): ?>
                                    <label
                                        class="group-add-member-item"
                                        data-modal-search-row
                                        data-modal-search-text="<?= htmlspecialchars(
                                            strtolower($contact['display_name'] . ' ' . group_member_role_detail($contact)),
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>"
                                    >
                                        <input type="checkbox" name="member_ids[]" value="<?= (int) $contact['id'] ?>">
                                        <img
                                            src="<?= htmlspecialchars(message_avatar_path($contact['avatar'], $contact['role']), ENT_QUOTES, 'UTF-8') ?>"
                                            alt=""
                                            class="group-member-avatar"
                                        >
                                        <span>
                                            <strong><?= htmlspecialchars($contact['display_name'], ENT_QUOTES, 'UTF-8') ?></strong>
                                            <small><?= htmlspecialchars(group_member_role_detail($contact), ENT_QUOTES, 'UTF-8') ?></small>
                                        </span>
                                        <i class="bi bi-check2"></i>
                                    </label>
                                <?php endforeach; ?>
                            </div>

                            <div class="modal-no-results" data-modal-search-empty="groupAddMembersList" hidden>
                                <i class="bi bi-search"></i>
                                <strong>No users found</strong>
                            </div>

                            <footer class="message-modal-actions">
                                <button type="button" class="message-modal-secondary" data-modal-close>Cancel</button>
                                <button type="submit" class="message-modal-primary">
                                    <i class="bi bi-person-plus"></i>
                                    Add selected
                                </button>
                            </footer>
                        </form>
                    </section>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <?php if ($is_group_conversation): ?>
            <div class="message-modal" id="readReceiptModal" role="dialog" aria-modal="true" aria-labelledby="readReceiptTitle" hidden>
                <div class="message-modal-backdrop" data-modal-close></div>
                <section class="message-modal-panel read-receipt-modal">
                    <header class="message-modal-header">
                        <div>
                            <h3 id="readReceiptTitle">Read receipts</h3>
                            <p data-read-receipt-summary>Read by 0</p>
                        </div>
                        <button type="button" class="message-modal-close" aria-label="Close" data-modal-close>
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </header>

                    <div class="read-receipt-list" data-read-receipt-list></div>
                </section>
            </div>
        <?php endif; ?>

        <?php if (!empty($pinned_messages)): ?>
            <section class="pinned-messages-panel" aria-label="Pinned messages">
                <div class="pinned-messages-heading">
                    <i class="bi bi-pin-angle-fill"></i>
                    <strong>Pinned messages</strong>
                    <span><?= count($pinned_messages) ?></span>
                </div>

                <div class="pinned-message-list">
                    <?php foreach ($pinned_messages as $pinned_message): ?>
                        <a href="#message-<?= (int) $pinned_message['id'] ?>" class="pinned-message-item">
                            <span>
                                <strong><?= htmlspecialchars($pinned_message['sender_display_name'], ENT_QUOTES, 'UTF-8') ?></strong>
                                <small><?= htmlspecialchars(format_chat_message_time((string) $pinned_message['created_at']), ENT_QUOTES, 'UTF-8') ?></small>
                            </span>
                            <p><?= htmlspecialchars(pinned_message_preview((string) $pinned_message['body']), ENT_QUOTES, 'UTF-8') ?></p>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <div
            class="conversation-thread"
            aria-label="Conversation messages"
            data-conversation-thread
            data-conversation-id="<?= (int) $conversation_id ?>"
            data-conversation-type="<?= htmlspecialchars((string) $conversation['conversation_type'], ENT_QUOTES, 'UTF-8') ?>"
            data-current-user-id="<?= (int) $user_id ?>"
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
                    $read_receipt = $group_read_receipts[(int) $message['id']] ?? [
                        'read_count' => 0,
                        'read_names' => '',
                        'read_users' => [],
                    ];
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
                        id="message-<?= (int) $message['id'] ?>"
                        class="chat-message<?= $is_own_message ? ' own' : '' ?><?= $type_label ? ' special' : '' ?><?= $is_deleted_message ? ' deleted' : '' ?>"
                        data-message-id="<?= (int) $message['id'] ?>"
                    >
                        <div class="chat-message-bubble">
                            <?php if ($type_label): ?>
                                <span class="chat-message-type">
                                    <?= htmlspecialchars($type_label, ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            <?php endif; ?>

                            <?php if ($is_group_conversation && !$is_own_message && !$is_deleted_message && $message['message_type'] !== MESSAGE_TYPE_SYSTEM): ?>
                                <span class="chat-message-sender">
                                    <?= htmlspecialchars(chat_sender_display_name($message), ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            <?php endif; ?>

                            <p data-message-body>
                                <?php if ($is_deleted_message): ?>
                                    <i class="bi bi-slash-circle" aria-hidden="true"></i>
                                    <em>This message was deleted.</em>
                                <?php else: ?>
                                    <?= chat_message_body_html((string) $message['body'], $group_members, $current_user_display_name, $is_group_conversation) ?>
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

                                <?php if ($is_group_conversation && $is_own_message && !$is_deleted_message && (string) $message['message_type'] !== MESSAGE_TYPE_SYSTEM): ?>
                                    <button
                                        type="button"
                                        class="chat-read-receipt"
                                        data-read-receipt
                                        data-read-count="<?= (int) $read_receipt['read_count'] ?>"
                                        data-read-names="<?= htmlspecialchars((string) $read_receipt['read_names'], ENT_QUOTES, 'UTF-8') ?>"
                                        data-read-users="<?= htmlspecialchars(json_encode($read_receipt['read_users'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?>"
                                        <?= (int) $read_receipt['read_count'] === 0 ? 'hidden' : '' ?>
                                    >
                                        Read by <?= (int) $read_receipt['read_count'] ?>
                                    </button>
                                <?php endif; ?>

                                <?php if (!empty($message['can_edit']) || !empty($message['can_delete']) || !empty($message['can_pin'])): ?>
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

                                            <?php if (!empty($message['can_pin'])): ?>
                                                <form method="post" action="/gakumas-sms/messages/pin.php" class="chat-message-pin-form" role="none">
                                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                                                    <input type="hidden" name="conversation_id" value="<?= (int) $conversation_id ?>">
                                                    <input type="hidden" name="message_id" value="<?= (int) $message['id'] ?>">
                                                    <input type="hidden" name="action" value="<?= !empty($message['pinned_at']) ? 'unpin' : 'pin' ?>">
                                                    <button type="submit" role="menuitem">
                                                        <i class="bi <?= !empty($message['pinned_at']) ? 'bi-pin-angle' : 'bi-pin-angle-fill' ?>"></i>
                                                        <span><?= !empty($message['pinned_at']) ? 'Unpin' : 'Pin' ?></span>
                                                    </button>
                                                </form>
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
                <?php if ($is_group_conversation): ?>
                    <script type="application/json" data-mention-members>
                        <?= json_encode($group_mention_members, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>
                    </script>
                    <div class="mention-suggestions" data-mention-suggestions hidden></div>
                <?php endif; ?>

                <div class="message-typing-indicator" data-typing-indicator hidden></div>
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
