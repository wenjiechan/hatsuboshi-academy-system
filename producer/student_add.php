<?php
require_once '../includes/auth.php';
require_role('producer');

require_once '../config/database.php';
require_once '../includes/theme_settings_helpers.php';
require_once '../includes/messages_helpers.php';
require_once '../includes/notifications_helpers.php';

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

$producer_id = (int) $_SESSION['id'];
$success = $_SESSION['student_add_success'] ?? null;
$error = $_SESSION['student_add_error'] ?? null;
unset($_SESSION['student_add_success'], $_SESSION['student_add_error']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf((string) ($_POST['csrf_token'] ?? ''));

    $student_id = filter_input(INPUT_POST, 'student_id', FILTER_VALIDATE_INT);

    if (!$student_id || $student_id <= 0) {
        $_SESSION['student_add_error'] = 'Please choose a valid unassigned student.';
        header('Location: /gakumas-sms/producer/student_add.php');
        exit;
    }

    try {
        create_producer_add_student_request($pdo, $producer_id, (int) $student_id);
        $_SESSION['student_add_success'] = 'Producer request sent. The student must accept before joining your roster.';
    } catch (InvalidArgumentException | RuntimeException $exception) {
        $_SESSION['student_add_error'] = $exception->getMessage();
    } catch (Throwable $exception) {
        $_SESSION['student_add_error'] = 'The producer request could not be sent. Please try again.';
    }

    header('Location: /gakumas-sms/producer/student_add.php');
    exit;
}

$students_stmt = $pdo->prepare(
    'SELECT
        s.*,
        u.avatar,
        pending.id AS pending_request_id
     FROM students s
     INNER JOIN users u
        ON u.id = s.user_id
       AND u.is_active = 1
     LEFT JOIN producer_student_requests pending
        ON pending.student_id = s.id
       AND pending.producer_id = ?
       AND pending.request_type = "add"
       AND pending.status = "pending"
     WHERE s.producer_id IS NULL
     ORDER BY s.school_year, s.name'
);
$students_stmt->execute([$producer_id]);
$unassigned_students = $students_stmt->fetchAll();

$page_title = 'Add Unassigned Student';
$page_styles = ['/gakumas-sms/assets/css/pages/student.css'];
require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

<main class="dashboard-main students-main">
    <section class="section-heading students-toolbar">
        <a href="/gakumas-sms/producer/students.php" class="students-back-icon" aria-label="Back to students">
            <i class="bi bi-arrow-left" aria-hidden="true"></i>
        </a>

        <label class="students-search" for="unassignedSearch">
            <i class="bi bi-search" aria-hidden="true"></i>
            <input type="search" id="unassignedSearch" placeholder="Search unassigned students">
        </label>
    </section>

    <?php if ($success): ?>
        <div class="student-page-alert success" role="status">
            <i class="bi bi-check-circle" aria-hidden="true"></i>
            <?= e($success) ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="student-page-alert error" role="alert">
            <i class="bi bi-exclamation-circle" aria-hidden="true"></i>
            <?= e($error) ?>
        </div>
    <?php endif; ?>

    <section data-unassigned-students-live-region>
        <?php if (empty($unassigned_students)): ?>
            <div class="empty-dashboard-state">
                <strong>No unassigned students</strong>
                <p>Every active student currently has a producer.</p>
            </div>
        <?php else: ?>
            <section class="unassigned-student-section">
                <div class="students-class-heading">
                    <div>
                        <i class="bi bi-person-plus-fill" aria-hidden="true"></i>
                        <h2>Unassigned Students</h2>
                    </div>

                    <span><?= count($unassigned_students) ?> available</span>
                </div>

                <div class="unassigned-student-list" id="unassignedStudentList" role="list">
                    <div class="unassigned-student-list-header" aria-hidden="true">
                        <span>
                            Name
                            <button type="button" class="student-sort-button" data-unassigned-sort-column="name" data-sort-direction="asc" aria-label="Sort name ascending">
                                <i class="bi bi-sort-alpha-down" aria-hidden="true"></i>
                            </button>
                        </span>
                        <span>
                            Class
                            <button type="button" class="student-sort-button" data-unassigned-sort-column="class" data-sort-direction="asc" aria-label="Sort class ascending">
                                <i class="bi bi-sort-alpha-down" aria-hidden="true"></i>
                            </button>
                        </span>
                        <span>
                            Vocal
                            <button type="button" class="student-sort-button" data-unassigned-sort-column="vocal" data-sort-direction="asc" aria-label="Sort vocal ascending">
                                <i class="bi bi-sort-numeric-down" aria-hidden="true"></i>
                            </button>
                        </span>
                        <span>
                            Dance
                            <button type="button" class="student-sort-button" data-unassigned-sort-column="dance" data-sort-direction="asc" aria-label="Sort dance ascending">
                                <i class="bi bi-sort-numeric-down" aria-hidden="true"></i>
                            </button>
                        </span>
                        <span>
                            Visual
                            <button type="button" class="student-sort-button" data-unassigned-sort-column="visual" data-sort-direction="asc" aria-label="Sort visual ascending">
                                <i class="bi bi-sort-numeric-down" aria-hidden="true"></i>
                            </button>
                        </span>
                        <span></span>
                    </div>

                    <?php foreach ($unassigned_students as $student): ?>
                        <?php
                        $collapse_id = 'unassignedStudent' . (int) $student['id'];
                        $avatar_path = message_avatar_path($student['avatar'] ?? '', 'student');
                        $has_pending_request = !empty($student['pending_request_id']);
                        $search_text = strtolower(implode(' ', [
                            $student['name'] ?? '',
                            $student['name_jp'] ?? '',
                            $student['rank'] ?? '',
                            $student['school_year'] ?? '',
                            $student['hometown'] ?? '',
                            $student['hobbies'] ?? '',
                            $student['special_skill'] ?? '',
                        ]));
                        ?>
                        <article
                            class="unassigned-student-track"
                            data-unassigned-student-row
                            data-unassigned-search="<?= e($search_text) ?>"
                            data-sort-name="<?= e($student['name']) ?>"
                            data-sort-class="<?= e($student['school_year'] ?: 'Unassigned') ?>"
                            data-sort-vocal="<?= (int) $student['vocal'] ?>"
                            data-sort-dance="<?= (int) $student['dance'] ?>"
                            data-sort-visual="<?= (int) $student['visual'] ?>"
                            role="listitem"
                        >
                            <button
                                type="button"
                                class="unassigned-student-button collapsed"
                                data-bs-toggle="collapse"
                                data-bs-target="#<?= e($collapse_id) ?>"
                                aria-expanded="false"
                                aria-controls="<?= e($collapse_id) ?>"
                            >
                                <span class="unassigned-student-name">
                                    <img src="<?= e($avatar_path) ?>" alt="<?= e($student['name']) ?>" class="student-avatar">
                                    <span>
                                        <strong><?= e($student['name']) ?></strong>
                                        <?php if (!empty($student['name_jp'])): ?>
                                            <small lang="ja"><?= e($student['name_jp']) ?></small>
                                        <?php endif; ?>
                                    </span>
                                </span>
                                <span><?= e($student['school_year'] ?: 'Unassigned') ?></span>
                                <span><?= (int) $student['vocal'] ?></span>
                                <span><?= (int) $student['dance'] ?></span>
                                <span><?= (int) $student['visual'] ?></span>
                                <i class="bi bi-chevron-down unassigned-student-chevron" aria-hidden="true"></i>
                            </button>

                            <div class="collapse" id="<?= e($collapse_id) ?>">
                                <div class="unassigned-student-details">
                                    <div class="student-readonly-grid">
                                        <div><span>Rank</span><strong><?= e($student['rank']) ?></strong></div>
                                        <div><span>Birthday</span><strong><?= e($student['birthday'] ?: 'Not set') ?></strong></div>
                                        <div><span>Zodiac</span><strong><?= e($student['zodiac'] ?: 'Not set') ?></strong></div>
                                        <div><span>Blood Type</span><strong><?= e($student['blood_type'] ?: 'Not set') ?></strong></div>
                                        <div><span>Height</span><strong><?= e($student['height'] ? $student['height'] . ' cm' : 'Not set') ?></strong></div>
                                        <div><span>Weight</span><strong><?= e($student['weight'] ? $student['weight'] . ' kg' : 'Not set') ?></strong></div>
                                        <div><span>Three Size</span><strong><?= e($student['three_size'] ?: 'Not set') ?></strong></div>
                                        <div><span>Hometown</span><strong><?= e($student['hometown'] ?: 'Not set') ?></strong></div>
                                    </div>

                                    <div class="student-readonly-notes">
                                        <div>
                                            <span>Special Skill</span>
                                            <p><?= nl2br(e($student['special_skill'] ?: 'Not set')) ?></p>
                                        </div>
                                        <div>
                                            <span>Hobbies</span>
                                            <p><?= nl2br(e($student['hobbies'] ?: 'Not set')) ?></p>
                                        </div>
                                        <div>
                                            <span>Bio</span>
                                            <p><?= nl2br(e($student['bio'] ?: 'Not set')) ?></p>
                                        </div>
                                    </div>

                                    <form method="post" action="/gakumas-sms/producer/student_add.php" class="unassigned-request-form">
                                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="student_id" value="<?= (int) $student['id'] ?>">
                                        <button
                                            type="submit"
                                            class="btn btn-primary"
                                            <?= $has_pending_request ? 'disabled' : '' ?>
                                            data-produce-request-button
                                        >
                                            <i class="bi <?= $has_pending_request ? 'bi-hourglass-split' : 'bi-person-plus' ?>" aria-hidden="true"></i>
                                            <?= $has_pending_request ? 'Request Pending' : 'Request to Produce' ?>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>

            <section class="empty-dashboard-state students-search-empty d-none" id="unassignedSearchEmpty">
                <strong>No matching students</strong>
                <p>Try another name, class, rank, or note.</p>
            </section>
        <?php endif; ?>
    </section>
</main>

<script>
const unassignedSearch = document.getElementById('unassignedSearch');
let unassignedLiveRegion = document.querySelector('[data-unassigned-students-live-region]');
let unassignedRows = [];
let unassignedSections = [];
let unassignedSearchEmpty = null;
let unassignedPollInProgress = false;
let unassignedSortColumn = '';
let unassignedSortDirection = 'asc';

function refreshUnassignedDomReferences() {
    unassignedLiveRegion = document.querySelector('[data-unassigned-students-live-region]');
    unassignedRows = Array.from(document.querySelectorAll('[data-unassigned-student-row]'));
    unassignedSections = Array.from(document.querySelectorAll('.unassigned-student-section'));
    unassignedSearchEmpty = document.getElementById('unassignedSearchEmpty');
}

function updateUnassignedVisibility() {
    const query = unassignedSearch ? unassignedSearch.value.trim().toLowerCase() : '';
    let visibleCount = 0;

    unassignedRows.forEach((row) => {
        const isVisible = !query || row.dataset.unassignedSearch.includes(query);
        row.classList.toggle('d-none', !isVisible);
        visibleCount += isVisible ? 1 : 0;
    });

    unassignedSections.forEach((section) => {
        const hasVisibleStudent = section.querySelector('[data-unassigned-student-row]:not(.d-none)');
        section.classList.toggle('d-none', !hasVisibleStudent);
    });

    if (unassignedSearchEmpty) {
        unassignedSearchEmpty.classList.toggle('d-none', visibleCount > 0);
    }
}

if (unassignedSearch) {
    unassignedSearch.addEventListener('input', updateUnassignedVisibility);
}

function sortUnassignedStudents(column, direction) {
    const list = document.getElementById('unassignedStudentList');

    if (!list || !column) {
        return;
    }

    const rows = Array.from(list.querySelectorAll('[data-unassigned-student-row]'));
    const multiplier = direction === 'desc' ? -1 : 1;

    rows.sort((firstRow, secondRow) => {
        const firstValue = firstRow.dataset[`sort${column.charAt(0).toUpperCase() + column.slice(1)}`] || '';
        const secondValue = secondRow.dataset[`sort${column.charAt(0).toUpperCase() + column.slice(1)}`] || '';

        if (['vocal', 'dance', 'visual'].includes(column)) {
            return (Number(firstValue) - Number(secondValue)) * multiplier;
        }

        return firstValue.trim().localeCompare(secondValue.trim(), undefined, {
            numeric: true,
            sensitivity: 'base',
        }) * multiplier;
    });

    rows.forEach((row) => list.appendChild(row));
}

function updateUnassignedSortButtons(activeButton = null) {
    document.querySelectorAll('[data-unassigned-sort-column]').forEach((button) => {
        const column = button.dataset.unassignedSortColumn || '';
        const icon = button.querySelector('i');
        const isActive = activeButton === button || (!activeButton && column === unassignedSortColumn);
        const nextDirection = isActive && unassignedSortDirection === 'asc' ? 'desc' : 'asc';

        button.dataset.sortDirection = nextDirection;
        button.setAttribute('aria-label', `Sort ${column} ${nextDirection === 'asc' ? 'ascending' : 'descending'}`);

        if (icon) {
            const isNumeric = ['vocal', 'dance', 'visual'].includes(column);
            icon.className = `bi ${isNumeric
                ? (nextDirection === 'asc' ? 'bi-sort-numeric-down' : 'bi-sort-numeric-up')
                : (nextDirection === 'asc' ? 'bi-sort-alpha-down' : 'bi-sort-alpha-up')}`;
        }
    });
}

document.addEventListener('click', (event) => {
    const requestButton = event.target.closest('[data-produce-request-button]');
    const sortButton = event.target.closest('[data-unassigned-sort-column]');

    if (!requestButton) {
        if (!sortButton) {
            return;
        }

        const column = sortButton.dataset.unassignedSortColumn || '';
        const currentDirection = sortButton.dataset.sortDirection === 'desc' ? 'desc' : 'asc';

        unassignedSortColumn = column;
        unassignedSortDirection = currentDirection;
        sortUnassignedStudents(unassignedSortColumn, unassignedSortDirection);
        updateUnassignedSortButtons(sortButton);
        updateUnassignedVisibility();

        return;
    }

    if (!window.confirm('Send a producer request to this student? The student must accept before joining your roster.')) {
        event.preventDefault();
    }
});

async function pollUnassignedStudents() {
    if (!unassignedLiveRegion || unassignedPollInProgress || document.hidden) {
        return;
    }

    unassignedPollInProgress = true;

    try {
        const response = await fetch('/gakumas-sms/producer/student_add.php', {
            headers: { Accept: 'text/html' },
            cache: 'no-store',
        });

        if (response.redirected && !response.url.includes('/producer/student_add.php')) {
            window.location.assign(response.url);
            return;
        }

        if (!response.ok) {
            return;
        }

        const html = await response.text();
        const freshDocument = new DOMParser().parseFromString(html, 'text/html');
        const freshLiveRegion = freshDocument.querySelector('[data-unassigned-students-live-region]');

        if (!freshLiveRegion) {
            return;
        }

        const openIds = Array.from(document.querySelectorAll('.unassigned-student-track .collapse.show'))
            .map((panel) => panel.id);

        unassignedLiveRegion.replaceChildren(...Array.from(freshLiveRegion.childNodes));
        refreshUnassignedDomReferences();

        if (unassignedSortColumn) {
            sortUnassignedStudents(unassignedSortColumn, unassignedSortDirection);
            updateUnassignedSortButtons();
        }

        openIds.forEach((id) => {
            const panel = document.getElementById(id);
            const button = document.querySelector(`[data-bs-target="#${CSS.escape(id)}"]`);

            panel?.classList.add('show');
            button?.classList.remove('collapsed');
            button?.setAttribute('aria-expanded', 'true');
        });

        updateUnassignedVisibility();
    } catch (error) {
        // Temporary polling errors are retried on the next interval.
    } finally {
        unassignedPollInProgress = false;
    }
}

refreshUnassignedDomReferences();
updateUnassignedVisibility();
window.setInterval(pollUnassignedStudents, 3000);
document.addEventListener('visibilitychange', () => {
    if (!document.hidden) {
        pollUnassignedStudents();
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>
