<?php
require_once '../includes/auth.php';
require_role('student');

require_once '../config/database.php';

$stmt = $pdo->prepare(
    'SELECT
        s.id,
        s.name,
        s.rank,
        s.school_year,
        s.vocal,
        s.dance,
        s.visual,
        u.username AS producer_name
     FROM students s
     LEFT JOIN users u ON u.id = s.producer_id
     WHERE s.user_id = ?
     LIMIT 1'
);

$stmt->execute([$_SESSION['id']]);
$student = $stmt->fetch();

if (!$student) {
    http_response_code(404);
    exit('Student profile not found');
}

$weekday = (int) date('N');

$schedule_stmt = $pdo->prepare(
    'SELECT activity_type, title, description, start_time, end_time, location
     FROM recurring_schedules
     WHERE student_id = ?
       AND weekday = ?
       AND is_active = 1
     ORDER BY start_time'
);

$schedule_stmt->execute([$student['id'], $weekday]);
$today_schedules = $schedule_stmt->fetchAll();

$page_title = 'Student Dashboard';
require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

<main class="dashboard-main">
    <section class="dashboard-hero">
        <div class="student-info">
            <p class="dashboard-eyebrow">Student Overview</p>

            <h2>
                Welcome back,
                <?= htmlspecialchars($student['name'] ?? 'Student', ENT_QUOTES, 'UTF-8') ?>
            </h2>

            <dl class="student-details">
                <div>
                    <dt>Rank</dt>
                    <dd><?= htmlspecialchars($student['rank'] ?? 'Unranked', ENT_QUOTES, 'UTF-8') ?></dd>
                </div>

                <div>
                    <dt>Class</dt>
                    <dd><?= htmlspecialchars($student['school_year'] ?? 'Not assigned', ENT_QUOTES, 'UTF-8') ?></dd>
                </div>

                <div>
                    <dt>Producer</dt>
                    <dd><?= htmlspecialchars($student['producer_name'] ?? 'Not assigned', ENT_QUOTES, 'UTF-8') ?></dd>
                </div>
            </dl>
        </div>

        <aside class="current-stats">
            <h2>Current Stats</h2>

            <div class="stat-row vocal">
                <span>Vocal</span>
                <strong><?= (int) ($student['vocal'] ?? 0) ?></strong>
            </div>

            <div class="stat-row dance">
                <span>Dance</span>
                <strong><?= (int) ($student['dance'] ?? 0) ?></strong>
            </div>

            <div class="stat-row visual">
                <span>Visual</span>
                <strong><?= (int) ($student['visual'] ?? 0) ?></strong>
            </div>
        </aside>
    </section>

    <div class="dashboard-grid">
        <section class="today-schedule">
            <div class="section-heading">
                <div>
                    <p class="dashboard-eyebrow">Fixed Weekly Plan</p>
                    <h2>Today's Schedule</h2>
                </div>
                <span><?= htmlspecialchars(date('l'), ENT_QUOTES, 'UTF-8') ?></span>
            </div>

            <?php if (empty($today_schedules)): ?>
            <p class="empty-dashboard-text">No fixed schedule for today.</p>
            <?php else: ?>
            <div class="schedule-list">
                <?php $current_time = date('H:i:s'); ?>

                <?php foreach ($today_schedules as $schedule): ?>
                <?php
                if ($schedule['end_time'] < $current_time) {
                    $schedule_status = 'Passed';
                } elseif ($schedule['start_time'] <= $current_time && $schedule['end_time'] >= $current_time) {
                    $schedule_status = 'Now';
                } else {
                    $schedule_status = 'Upcoming';
                }
                ?>

                <article class="schedule-item">
                    <time class="schedule-time">
                        <span><?= htmlspecialchars(substr($schedule['start_time'], 0, 5), ENT_QUOTES, 'UTF-8') ?></span>
                        <span><?= htmlspecialchars(substr($schedule['end_time'], 0, 5), ENT_QUOTES, 'UTF-8') ?></span>
                    </time>

                    <div class="schedule-content">
                        <div class="schedule-title-row">
                            <h3>
                                <?= htmlspecialchars($schedule['title'], ENT_QUOTES, 'UTF-8') ?>
                            </h3>

                            <span
                                class="schedule-status <?= htmlspecialchars(strtolower($schedule_status), ENT_QUOTES, 'UTF-8') ?>">
                                <?= htmlspecialchars($schedule_status, ENT_QUOTES, 'UTF-8') ?>
                            </span>
                        </div>

                        <p>
                            <?= htmlspecialchars($schedule['location'] ?? 'No location', ENT_QUOTES, 'UTF-8') ?>
                        </p>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </section>

        <aside class="dashboard-side-panel">
            <section class="messages-preview">
                <div class="section-heading">
                    <div>
                        <p class="dashboard-eyebrow">Inbox</p>
                        <h2>Messages</h2>
                    </div>
                    <a href="/gakumas-sms/messages/inbox.php" class="panel-link">View all</a>
                </div>

                <p class="empty-dashboard-text">
                    Message preview coming soon.
                </p>
            </section>
        </aside>
    </div>
</main>

<?php require_once '../includes/footer.php'; ?>