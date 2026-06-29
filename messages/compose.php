<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/messages_helpers.php';

$user_id = (int) $_SESSION['id'];

// Allow the user selected someone to message
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf((string) ($_POST['csrf_token'] ?? ''));

    $recipient_id = filter_input(INPUT_POST, 'recipient_id', FILTER_VALIDATE_INT);
    $recipient = $recipient_id ? get_message_user($pdo, (int) $recipient_id) : null;

    // Prevent messaging yourself
    if (!$recipient || (int) $recipient['id'] === $user_id) {
        redirect_to_account_issue(
            'Recipient unavailable',
            'The selected user is unavailable or cannot receive messages.',
            400
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

    <!--Search is handled on the page by filtering the available contact cards.-->
    <section class="compose-panel">
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

        <?php if (empty($contacts)): ?>
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
