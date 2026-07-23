<?php
require_once '../includes/auth.php';
require_role('producer');

require_once '../config/database.php';
require_once '../includes/theme_settings_helpers.php';
require_once '../includes/producer_message_helpers.php';

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

$producer_id = (int) $_SESSION['id'];
$student_id = filter_input(INPUT_GET, 'student_id', FILTER_VALIDATE_INT) ?: 0;
$message_success = $_SESSION['producer_message_success'] ?? null;
$message_error = $_SESSION['producer_message_error'] ?? null;
unset($_SESSION['producer_message_success'], $_SESSION['producer_message_error']);

$student_stmt = $pdo->prepare(
    'SELECT
        s.id,
        s.name,
        s.name_jp,
        s.school_year,
        u.avatar,
        u.theme_primary_color,
        u.theme_secondary_color
     FROM students s
     INNER JOIN users u ON u.id = s.user_id
     WHERE s.id = ?
       AND s.producer_id = ?
       AND u.is_active = 1
     LIMIT 1'
);
$student_stmt->execute([$student_id, $producer_id]);
$student = $student_stmt->fetch();

if (!$student) {
    redirect_to_account_issue(
        'Student messages unavailable',
        'This student was not found, or this student is not assigned to your producer account.',
        404,
        '/gakumas-sms/producer/students.php',
        'Back to Students',
        false
    );
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf((string) ($_POST['csrf_token'] ?? ''));

    $action = (string) ($_POST['action'] ?? '');

    try {
        if ($action === 'create') {
            create_producer_message(
                $pdo,
                $producer_id,
                $student_id,
                (string) ($_POST['message_type'] ?? ''),
                (string) ($_POST['tone'] ?? ''),
                (string) ($_POST['message_text'] ?? '')
            );
            $_SESSION['producer_message_success'] = 'Producer message added.';
        } elseif ($action === 'update') {
            $message_id = filter_input(INPUT_POST, 'message_id', FILTER_VALIDATE_INT);

            if (!$message_id) {
                throw new InvalidArgumentException('Message unavailable.');
            }

            update_producer_message(
                $pdo,
                $producer_id,
                (int) $message_id,
                (string) ($_POST['message_type'] ?? ''),
                (string) ($_POST['tone'] ?? ''),
                (string) ($_POST['message_text'] ?? '')
            );
            $_SESSION['producer_message_success'] = 'Producer message updated.';
        } elseif ($action === 'delete') {
            $message_id = filter_input(INPUT_POST, 'message_id', FILTER_VALIDATE_INT);

            if (!$message_id) {
                throw new InvalidArgumentException('Message unavailable.');
            }

            delete_producer_message($pdo, $producer_id, (int) $message_id);
            $_SESSION['producer_message_success'] = 'Producer message deleted.';
        } else {
            throw new InvalidArgumentException('Choose a valid action.');
        }
    } catch (InvalidArgumentException | RuntimeException $exception) {
        $_SESSION['producer_message_error'] = $exception->getMessage();
    }

    header('Location: /gakumas-sms/producer/student_messages.php?student_id=' . $student_id);
    exit;
}

$producer_messages = get_producer_messages($pdo, $producer_id, $student_id);
$message_type_options = producer_message_type_options();
$messages_by_type = [];

foreach ($message_type_options as $type => $label) {
    $messages_by_type[$type] = [];
}

foreach ($producer_messages as $message) {
    $messages_by_type[$message['message_type']][] = $message;
}

$student_theme_primary = $student['theme_primary_color'] ?: DEFAULT_THEME_PRIMARY;
$student_theme_secondary = $student['theme_secondary_color'] ?: DEFAULT_THEME_SECONDARY;
$profile_avatar = trim((string) ($student['avatar'] ?? ''));

if ($profile_avatar !== '') {
    $profile_avatar_path = str_replace('\\', '/', $profile_avatar);

    if (!str_starts_with($profile_avatar_path, '/') && !preg_match('/^https?:\/\//i', $profile_avatar_path)) {
        $profile_avatar_path = '/gakumas-sms/assets/images/avatars/idols/' . rawurlencode($profile_avatar_path);
    }
} else {
    $profile_avatar_path = '/gakumas-sms/assets/images/avatars/default.webp';
}

$page_title = 'Producer Messages';
$page_styles = ['/gakumas-sms/assets/css/pages/producer-messages.css'];
require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

<main
    class="dashboard-main producer-messages-main"
    style="--primary: <?= e($student_theme_primary) ?>; --secondary: <?= e($student_theme_secondary) ?>;"
>
    <section class="producer-messages-heading">
        <div class="producer-message-avatar-wrap">
            <img src="<?= e($profile_avatar_path) ?>" alt="<?= e($student['name']) ?>" class="producer-message-avatar">
        </div>

        <div class="producer-message-hero-copy">
            <p class="dashboard-eyebrow">Producer Messages</p>
            <h2><?= e($student['name']) ?></h2>
            <?php if (!empty($student['name_jp'])): ?>
                <p lang="ja"><?= e($student['name_jp']) ?></p>
            <?php else: ?>
                <p><?= e($student['school_year'] ?: 'Unassigned Class') ?></p>
            <?php endif; ?>
        </div>

        <div class="producer-message-header-actions">
            <a href="/gakumas-sms/producer/student_edit.php?id=<?= $student_id ?>" class="producer-message-header-button">
                <i class="bi bi-person-badge" aria-hidden="true"></i>
                Student Profile
            </a>
            <button type="button" class="producer-message-header-button producer-message-add-toggle" data-add-message-toggle aria-expanded="false">
                <i class="bi bi-plus-lg" aria-hidden="true"></i>
                Add Message
            </button>
        </div>
    </section>

    <?php if ($message_success): ?>
        <div class="producer-message-alert success" role="status">
            <i class="bi bi-check-circle" aria-hidden="true"></i>
            <?= e($message_success) ?>
        </div>
    <?php endif; ?>

    <?php if ($message_error): ?>
        <div class="producer-message-alert error" role="alert">
            <i class="bi bi-exclamation-circle" aria-hidden="true"></i>
            <?= e($message_error) ?>
        </div>
    <?php endif; ?>

    <section class="producer-message-form d-none" data-add-message-panel>
        <div class="producer-message-section-title">
            <i class="bi bi-plus-circle" aria-hidden="true"></i>
            <h3>Add Message</h3>
        </div>

        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="action" value="create">

            <div class="producer-message-form-grid">
                <label>
                    <span>Type</span>
                    <select name="message_type" required>
                        <?php foreach ($message_type_options as $value => $label): ?>
                            <option value="<?= e($value) ?>"><?= e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label>
                    <span>Tone</span>
                    <input type="text" name="tone" maxlength="20" placeholder="warm">
                </label>
            </div>

            <label class="producer-message-text-field">
                <span>Message</span>
                <textarea name="message_text" maxlength="500" rows="3" required></textarea>
            </label>

            <div class="producer-message-form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-plus-lg" aria-hidden="true"></i>
                    Add Message
                </button>
            </div>
        </form>
    </section>

    <section class="producer-message-type-grid">
        <?php foreach ($message_type_options as $type => $label): ?>
            <?php $type_messages = $messages_by_type[$type] ?? []; ?>
            <details class="producer-message-type-section">
                <summary class="producer-message-type-heading">
                    <div>
                        <i class="bi bi-chat-square-heart" aria-hidden="true"></i>
                        <h3><?= e($label) ?></h3>
                    </div>
                    <span>
                        <?= count($type_messages) ?>
                        <i class="bi bi-chevron-down" aria-hidden="true"></i>
                    </span>
                </summary>

                <?php if (empty($type_messages)): ?>
                    <p class="producer-message-type-empty">No messages in this type yet.</p>
                <?php else: ?>
                    <div class="producer-message-list">
                        <?php foreach ($type_messages as $message): ?>
                            <?php $edit_form_id = 'producerMessageEdit' . (int) $message['id']; ?>
                            <article class="producer-message-item">
                                <form method="post" class="producer-message-edit-form" id="<?= e($edit_form_id) ?>">
                                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                    <input type="hidden" name="action" value="update">
                                    <input type="hidden" name="message_id" value="<?= (int) $message['id'] ?>">

                                    <div class="producer-message-form-grid compact">
                                        <label>
                                            <span>Type</span>
                                            <select name="message_type" required>
                                                <?php foreach ($message_type_options as $value => $option_label): ?>
                                                    <option value="<?= e($value) ?>" <?= $message['message_type'] === $value ? 'selected' : '' ?>>
                                                        <?= e($option_label) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </label>

                                        <label>
                                            <span>Tone</span>
                                            <input type="text" name="tone" maxlength="20" value="<?= e((string) ($message['tone'] ?? '')) ?>">
                                        </label>
                                    </div>

                                    <label class="producer-message-text-field">
                                        <span>Message</span>
                                        <textarea name="message_text" maxlength="500" rows="3" required><?= e($message['message_text']) ?></textarea>
                                    </label>
                                </form>

                                <div class="producer-message-item-actions">
                                    <button type="submit" class="btn btn-primary" form="<?= e($edit_form_id) ?>">
                                        <i class="bi bi-check2" aria-hidden="true"></i>
                                        Save
                                    </button>

                                    <form method="post" class="producer-message-delete-form">
                                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="message_id" value="<?= (int) $message['id'] ?>">
                                        <button type="submit" class="producer-message-delete-button">
                                            <i class="bi bi-trash" aria-hidden="true"></i>
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </details>
        <?php endforeach; ?>
    </section>
</main>

<script>
const addMessageToggle = document.querySelector('[data-add-message-toggle]');
const addMessagePanel = document.querySelector('[data-add-message-panel]');

if (addMessageToggle && addMessagePanel) {
    addMessageToggle.addEventListener('click', () => {
        const isOpening = addMessagePanel.classList.contains('d-none');

        addMessagePanel.classList.toggle('d-none', !isOpening);
        addMessageToggle.setAttribute('aria-expanded', isOpening ? 'true' : 'false');

        if (isOpening) {
            addMessagePanel.querySelector('select, input, textarea')?.focus();
        }
    });
}

document.addEventListener('submit', (event) => {
    if (!event.target.matches('.producer-message-delete-form')) {
        return;
    }

    if (!window.confirm('Delete this producer message?')) {
        event.preventDefault();
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>
