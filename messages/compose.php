<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/messages_helpers.php';

$user_id = (int) $_SESSION['id'];

// Allow the user selected someone to message
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf((string) ($_POST['csrf_token'] ?? ''));

    $compose_type = (string) ($_POST['compose_type'] ?? 'direct');

    if ($compose_type === 'group') {
        try {
            $conversation_id = create_group_conversation(
                $pdo,
                $user_id,
                (string) $_SESSION['role'],
                (string) ($_POST['group_name'] ?? ''),
                (array) ($_POST['member_ids'] ?? []),
                $_FILES['group_avatar'] ?? null
            );

            header('Location: /gakumas-sms/messages/view.php?id=' . $conversation_id);
            exit;
        } catch (InvalidArgumentException | RuntimeException $exception) {
            $_SESSION['message_error'] = $exception->getMessage();
            header('Location: /gakumas-sms/messages/compose.php?mode=group');
            exit;
        }
    }

    $recipient_id = filter_input(INPUT_POST, 'recipient_id', FILTER_VALIDATE_INT);
    $recipient = $recipient_id ? get_message_user($pdo, (int) $recipient_id) : null;

    // Prevent messaging yourself
    if (!$recipient || (int) $recipient['id'] === $user_id) {
        redirect_to_account_issue(
            'Recipient unavailable',
            'The selected user is unavailable or cannot receive messages.',
            400,
            '/gakumas-sms/messages/compose.php',
            'Back to Compose',
            false
        );
    }

    // Finds or creates a direct conservation
    $conversation_id = find_or_create_direct_conversation(
        $pdo,
        $user_id,
        (int) $recipient['id']
    );

    header('Location: /gakumas-sms/messages/view.php?id=' . $conversation_id);
    exit;
}

// Load active users that the current user can start a conversation with.
$contacts = get_message_contacts($pdo, $user_id);
$group_contacts = get_group_message_contacts($pdo, $user_id, (string) $_SESSION['role']);
$message_error = $_SESSION['message_error'] ?? null;
unset($_SESSION['message_error']);

$page_title = 'New Message';
$page_styles = ['/gakumas-sms/assets/css/pages/messages.css'];
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<main class="dashboard-main messages-main">
    <section class="message-page-heading">
        <a href="/gakumas-sms/messages/inbox.php" class="message-back-link" aria-label="Back to inbox">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <p class="dashboard-eyebrow">Start a conversation</p>
            <h2>New message</h2>
            <p>Select an active student, producer, or teacher.</p>
        </div>
    </section>

    <?php if ($message_error): ?>
        <div class="message-form-error" role="alert">
            <i class="bi bi-exclamation-circle"></i>
            <?= htmlspecialchars($message_error, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <!--Search is handled on the page by filtering the available contact cards.-->
    <section class="compose-panel">
        <div class="compose-mode-tabs" role="tablist" aria-label="Compose type">
            <a href="/gakumas-sms/messages/compose.php" class="<?= ($_GET['mode'] ?? '') !== 'group' ? 'active' : '' ?>">
                <i class="bi bi-person"></i>
                Direct
            </a>
            <a href="/gakumas-sms/messages/compose.php?mode=group" class="<?= ($_GET['mode'] ?? '') === 'group' ? 'active' : '' ?>">
                <i class="bi bi-people"></i>
                Group
            </a>
        </div>

        <label class="messages-search-field compose-search" for="recipientSearch">
            <span class="visually-hidden">Search users</span>
            <i class="bi bi-search" aria-hidden="true"></i>
            <input
                type="search"
                id="recipientSearch"
                placeholder="Search by name or role"
                autocomplete="off"
                data-recipient-search
            >
        </label>

        <?php if (($_GET['mode'] ?? '') === 'group'): ?>
            <?php if (empty($group_contacts)): ?>
                <div class="messages-empty-state">
                    <i class="bi bi-person-x"></i>
                    <h3>No users available</h3>
                    <p>There are no active users you can add to a group.</p>
                </div>
            <?php else: ?>
                <form method="post" class="group-compose-form" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="compose_type" value="group">

                    <label class="group-name-field" for="groupName">
                        <span>Group name</span>
                        <input type="text" id="groupName" name="group_name" maxlength="100" required>
                    </label>

                    <label class="group-avatar-upload" for="groupAvatar">
                        <span>Group avatar</span>
                        <input type="file" id="groupAvatar" name="group_avatar" accept="image/jpeg,image/png,image/webp">
                        <small>No file selected will use the default group avatar.</small>
                    </label>

                    <div class="recipient-list" aria-label="Available group members">
                        <?php foreach ($group_contacts as $contact): ?>
                            <?php
                            $contact_detail = $contact['role'] === 'teacher' && !empty($contact['specialty'])
                                ? ucfirst((string) $contact['specialty']) . ' teacher'
                                : ucfirst((string) $contact['role']);
                            ?>
                            <label
                                class="recipient-row group-recipient-row"
                                data-recipient-row
                                data-recipient-search="<?= htmlspecialchars(
                                    strtolower($contact['display_name'] . ' ' . $contact_detail),
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                            >
                                <input type="checkbox" name="member_ids[]" value="<?= (int) $contact['id'] ?>">

                                <img
                                    src="<?= htmlspecialchars(
                                        message_avatar_path($contact['avatar'], $contact['role']),
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                                    alt=""
                                    class="conversation-avatar"
                                >

                                <span class="recipient-select-button">
                                    <span>
                                        <strong><?= htmlspecialchars($contact['display_name'], ENT_QUOTES, 'UTF-8') ?></strong>
                                        <small><?= htmlspecialchars($contact_detail, ENT_QUOTES, 'UTF-8') ?></small>
                                    </span>
                                    <i class="bi bi-check2"></i>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>

                    <button type="submit" class="group-create-button">
                        <i class="bi bi-people-fill"></i>
                        Create group
                    </button>
                </form>

                <div class="messages-no-results" data-recipient-no-results hidden>
                    <i class="bi bi-search"></i>
                    <h3>No users found</h3>
                    <p>Try a different name or role.</p>
                </div>
            <?php endif; ?>
        <?php elseif (empty($contacts)): ?>
            <div class="messages-empty-state">
                <i class="bi bi-person-x"></i>
                <h3>No users available</h3>
                <p>There are no active users you can message.</p>
            </div>
        <?php else: ?>
            <div class="recipient-list" aria-label="Available users">
                <?php foreach ($contacts as $contact): ?>
                    <?php
                    $contact_detail = $contact['role'] === 'teacher' && !empty($contact['specialty'])
                        ? ucfirst((string) $contact['specialty']) . ' teacher'
                        : ucfirst((string) $contact['role']);
                    ?>
                    <form
                        method="post"
                        class="recipient-row"
                        data-recipient-row
                        data-recipient-search="<?= htmlspecialchars(
                            strtolower($contact['display_name'] . ' ' . $contact_detail),
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"
                    >
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" name="recipient_id" value="<?= (int) $contact['id'] ?>">

                        <img
                            src="<?= htmlspecialchars(
                                message_avatar_path($contact['avatar'], $contact['role']),
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                            alt=""
                            class="conversation-avatar"
                        >

                        <button type="submit" class="recipient-select-button">
                            <span>
                                <strong><?= htmlspecialchars($contact['display_name'], ENT_QUOTES, 'UTF-8') ?></strong>
                                <small><?= htmlspecialchars($contact_detail, ENT_QUOTES, 'UTF-8') ?></small>
                            </span>
                            <i class="bi bi-chevron-right"></i>
                        </button>
                    </form>
                <?php endforeach; ?>
            </div>

            <div class="messages-no-results" data-recipient-no-results hidden>
                <i class="bi bi-search"></i>
                <h3>No users found</h3>
                <p>Try a different name or role.</p>
            </div>
        <?php endif; ?>
    </section>
</main>

<script src="/gakumas-sms/assets/js/messages.js" defer></script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
