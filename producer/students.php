<?php
require_once '../includes/auth.php';
require_role('producer');

require_once '../config/database.php';
require_once '../includes/theme_settings_helpers.php';

// Get current producer account
$stmt = $pdo->prepare(
    'SELECT id, username, theme_primary_color, theme_secondary_color
     FROM users
     WHERE id = ?
       AND role = ?
     LIMIT 1'
);

$stmt->execute([$_SESSION['id'], 'producer']);
$producer = $stmt->fetch();

if (!$producer) {
    redirect_to_account_issue(
        'Producer profile not found',
        'Your login is active, but no producer account is linked to this session. Please log out and ask an administrator to check your account setup.',
        404
    );
}

//Apply producer theme color
$_SESSION['theme_primary_color'] = $producer['theme_primary_color'] ?: ($_SESSION['theme_primary_color'] ?? '#FF6B9D');
$_SESSION['theme_secondary_color'] = $producer['theme_secondary_color'] ?: ($_SESSION['theme_secondary_color'] ?? '#FFB3D1');

//Get student under this producer
$student_stmt = $pdo->prepare(
    'SELECT
        s.*,
        u.avatar
     FROM students s
     INNER JOIN users u ON u.id = s.user_id
     WHERE s.producer_id = ?
     ORDER BY s.school_year, s.name;'
);

$student_stmt->execute([$producer['id']]);
$student_info = $student_stmt->fetchAll();

// Group students by class
$grouped_students = [];

foreach ($student_info as $student) {
    $student_class = $student['school_year'] ?: 'Unassigned Class';
    $grouped_students[$student_class][] = $student;
}

$page_title = 'Students Management';
$page_styles = ['/gakumas-sms/assets/css/pages/student.css'];
require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

<main class="dashboard-main students-main">
    <section class="section-heading students-toolbar">
        <label class="students-search" for="studentSearch">
            <i class="bi bi-search" aria-hidden="true"></i>
            <input type="search" id="studentSearch" placeholder="Search student name, rank, or status">
        </label>

        <button type="button" class="students-pending-button" id="pendingStudentsButton" aria-pressed="false">
            <i class="bi bi-hourglass-split" aria-hidden="true"></i>
            Pending
        </button>

        <a href="/gakumas-sms/producer/student_add.php" class="btn btn-primary students-add-button">
            <i class="bi bi-plus-lg" aria-hidden="true"></i>
            Add Student
        </a>
    </section>

    <?php if (empty($grouped_students)): ?>
        <div class="empty-dashboard-state">
            <strong>No students yet</strong>
            <p>Add a student to start managing your production roster.</p>
        </div>
    <?php else: ?>
        <?php foreach ($grouped_students as $class_name => $students): ?>
            <section class="students-class-section" data-student-class>
                <div class="students-class-heading">
                    <div>
                        <i class="bi bi-people-fill" aria-hidden="true"></i>
                        <h2><?= htmlspecialchars($class_name, ENT_QUOTES, 'UTF-8') ?></h2>
                    </div>

                    <span><?= count($students) ?> student<?= count($students) === 1 ? '' : 's' ?></span>
                </div>

                <div class="students-table-wrap">
                    <table class="table align-middle students-table">
                        <thead>
                            <tr>
                                <th>
                                    Name
                                    <button type="button" class="student-sort-button" data-sort-column="name" data-sort-direction="asc" aria-label="Sort name ascending">
                                        <i class="bi bi-sort-alpha-down" aria-hidden="true"></i>
                                    </button>
                                </th>
                                <th>
                                    Rank
                                    <button type="button" class="student-sort-button" data-sort-column="rank" data-sort-direction="asc" aria-label="Sort rank ascending">
                                        <i class="bi bi-sort-alpha-down" aria-hidden="true"></i>
                                    </button>
                                </th>
                                <th>
                                    Vocal
                                    <button type="button" class="student-sort-button" data-sort-column="vocal" data-sort-direction="asc" aria-label="Sort vocal ascending">
                                        <i class="bi bi-sort-numeric-down" aria-hidden="true"></i>
                                    </button>
                                </th>
                                <th>
                                    Dance
                                    <button type="button" class="student-sort-button" data-sort-column="dance" data-sort-direction="asc" aria-label="Sort dance ascending">
                                        <i class="bi bi-sort-numeric-down" aria-hidden="true"></i>
                                    </button>
                                </th>
                                <th>
                                    Visual
                                    <button type="button" class="student-sort-button" data-sort-column="visual" data-sort-direction="asc" aria-label="Sort visual ascending">
                                        <i class="bi bi-sort-numeric-down" aria-hidden="true"></i>
                                    </button>
                                </th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $student): ?>
                                <?php
                                $student_id = (int) $student['id'];
                                $producer_status = $student['producer_status'] ?? 'active';
                                $producer_status_label = ucwords(str_replace('_', ' ', $producer_status));
                                $profile_avatar = trim((string) ($student['avatar'] ?? ''));

                                if ($profile_avatar !== '') {
                                    $avatar_path = str_replace('\\', '/', $profile_avatar);

                                    if (!str_starts_with($avatar_path, '/') && !preg_match('/^https?:\/\//i', $avatar_path)) {
                                        $avatar_path = '/gakumas-sms/assets/images/avatars/idols/' . rawurlencode($avatar_path);
                                    }
                                } else {
                                    $avatar_path = '/gakumas-sms/assets/images/avatars/default.webp';
                                }
                                //Search data
                                $search_text = strtolower(
                                    implode(' ', [
                                        $student['name'] ?? '',
                                        $student['name_jp'] ?? '',
                                        $student['rank'] ?? '',
                                        $producer_status,
                                        $class_name,
                                    ])
                                );
                                ?>
                                <tr class="student-row"
                                    data-student-search="<?= htmlspecialchars($search_text, ENT_QUOTES, 'UTF-8') ?>"
                                    data-student-status="<?= htmlspecialchars($producer_status, ENT_QUOTES, 'UTF-8') ?>">
                                    <td data-sort-value="<?= htmlspecialchars($student['name'], ENT_QUOTES, 'UTF-8') ?>">
                                        <div class="student-name-cell">
                                            <img src="<?= htmlspecialchars($avatar_path, ENT_QUOTES, 'UTF-8') ?>"
                                                alt="<?= htmlspecialchars($student['name'], ENT_QUOTES, 'UTF-8') ?>"
                                                class="student-avatar">
                                            <div>
                                                <strong><?= htmlspecialchars($student['name'], ENT_QUOTES, 'UTF-8') ?></strong>
                                                <?php if (!empty($student['name_jp'])): ?>
                                                    <small lang="ja"><?= htmlspecialchars($student['name_jp'], ENT_QUOTES, 'UTF-8') ?></small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td data-sort-value="<?= htmlspecialchars($student['rank'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($student['rank'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td data-sort-value="<?= (int) $student['vocal'] ?>"><?= (int) $student['vocal'] ?></td>
                                    <td data-sort-value="<?= (int) $student['dance'] ?>"><?= (int) $student['dance'] ?></td>
                                    <td data-sort-value="<?= (int) $student['visual'] ?>"><?= (int) $student['visual'] ?></td>
                                    <td data-sort-value="<?= htmlspecialchars($producer_status_label, ENT_QUOTES, 'UTF-8') ?>">
                                        <span class="student-status student-status-<?= htmlspecialchars(str_replace('_', '-', $producer_status), ENT_QUOTES, 'UTF-8') ?>">
                                            <?= htmlspecialchars($producer_status_label, ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                    </td>
                                    <td class="text-end student-actions">
                                        <a href="/gakumas-sms/producer/student_edit.php?id=<?= $student_id ?>" aria-label="Edit <?= htmlspecialchars($student['name'], ENT_QUOTES, 'UTF-8') ?>">
                                            <i class="bi bi-pencil" aria-hidden="true"></i>
                                        </a>
                                        <?php if ($producer_status === 'removal_pending'): ?>
                                            <span class="student-action-pending" aria-label="Removal pending for <?= htmlspecialchars($student['name'], ENT_QUOTES, 'UTF-8') ?>" title="Removal Pending">
                                                <i class="bi bi-hourglass-split" aria-hidden="true"></i>
                                            </span>
                                        <?php else: ?>
                                            <a href="/gakumas-sms/producer/student_delete.php?id=<?= $student_id ?>" class="danger" aria-label="Remove <?= htmlspecialchars($student['name'], ENT_QUOTES, 'UTF-8') ?>">
                                                <i class="bi bi-person-dash" aria-hidden="true"></i>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        <?php endforeach; ?>

        <section class="empty-dashboard-state students-search-empty d-none" id="studentSearchEmpty">
            <strong>No matching students</strong>
            <p>Try another name, class, rank, or status.</p>
        </section>
    <?php endif; ?>
</main>

<script>
// Gets all student rows and the search controls
const studentSearch = document.getElementById('studentSearch');
const studentRows = Array.from(document.querySelectorAll('.student-row'));
const studentClassSections = Array.from(document.querySelectorAll('[data-student-class]'));
const studentSearchEmpty = document.getElementById('studentSearchEmpty');
const studentSortButtons = Array.from(document.querySelectorAll('.student-sort-button'));
const pendingStudentsButton = document.getElementById('pendingStudentsButton');
let showPendingOnly = false;

// Row is visibly only if matches the search and pending filter
function updateStudentVisibility() {
    const query = studentSearch ? studentSearch.value.trim().toLowerCase() : '';
    let visibleStudentCount = 0;

    studentRows.forEach((row) => {
        const matchesSearch = !query || row.dataset.studentSearch.includes(query);
        const matchesPending = !showPendingOnly || row.dataset.studentStatus === 'removal_pending';
        const isVisible = matchesSearch && matchesPending;

        row.classList.toggle('d-none', !isVisible);

        if (isVisible) {
            visibleStudentCount += 1;
        }
    });

    //Hide empty class sections
    studentClassSections.forEach((section) => {
        const hasVisibleStudent = section.querySelector('.student-row:not(.d-none)');
        section.classList.toggle('d-none', !hasVisibleStudent);
    });

    if (studentSearchEmpty) {
        studentSearchEmpty.classList.toggle('d-none', visibleStudentCount > 0);
    }
}

if (studentSearch) {
    studentSearch.addEventListener('input', updateStudentVisibility);
}

// Show pending students or all students
if (pendingStudentsButton) {
    pendingStudentsButton.addEventListener('click', () => {
        showPendingOnly = !showPendingOnly;
        pendingStudentsButton.classList.toggle('active', showPendingOnly);
        pendingStudentsButton.setAttribute('aria-pressed', showPendingOnly ? 'true' : 'false');
        updateStudentVisibility();
    });
}

// Sorting Logic
studentSortButtons.forEach((button) => {
    button.addEventListener('click', () => {
        const table = button.closest('table');
        const tbody = table?.querySelector('tbody');
        const columnIndex = button.closest('th')?.cellIndex;
        const currentDirection = button.dataset.sortDirection || 'asc';
        const directionMultiplier = currentDirection === 'asc' ? 1 : -1;

        if (!tbody || columnIndex === undefined) {
            return;
        }

        const rows = Array.from(tbody.querySelectorAll('.student-row'));
        const sortColumn = button.dataset.sortColumn;

        rows.sort((firstRow, secondRow) => {
            const firstValue = firstRow.cells[columnIndex]?.dataset.sortValue ?? firstRow.cells[columnIndex]?.textContent ?? '';
            const secondValue = secondRow.cells[columnIndex]?.dataset.sortValue ?? secondRow.cells[columnIndex]?.textContent ?? '';

            //Sort as numbers
            if (['vocal', 'dance', 'visual'].includes(sortColumn)) {
                return (Number(firstValue) - Number(secondValue)) * directionMultiplier;
            }

            // Sort as text
            return firstValue.trim().localeCompare(secondValue.trim(), undefined, {
                numeric: true,
                sensitivity: 'base'
            }) * directionMultiplier;
        });

        // Changes the visible order in the table
        rows.forEach((row) => tbody.appendChild(row));

        const nextDirection = currentDirection === 'asc' ? 'desc' : 'asc';
        const icon = button.querySelector('i');

        button.dataset.sortDirection = nextDirection;
        button.setAttribute('aria-label', `Sort ${sortColumn} ${nextDirection === 'asc' ? 'ascending' : 'descending'}`);

        // Change the sort icon
        if (icon) {
            const isNumeric = ['vocal', 'dance', 'visual'].includes(sortColumn);
            icon.className = `bi ${isNumeric
                ? (nextDirection === 'asc' ? 'bi-sort-numeric-down' : 'bi-sort-numeric-up')
                : (nextDirection === 'asc' ? 'bi-sort-alpha-down' : 'bi-sort-alpha-up')}`;
        }
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>
