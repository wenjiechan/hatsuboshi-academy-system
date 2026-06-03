<?php
require_once '../includes/auth.php';
require_role('student');

require_once '../config/database.php';

//Get current student profile
$stmt = $pdo->prepare(
    'SELECT
        s.id,
        s.name,
        s.birthday,
        s.rank,
        s.school_year,
        s.vocal,
        s.dance,
        s.visual,
        s.theme_primary_color,
        s.theme_secondary_color,
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

//Save student theme into session
$_SESSION['student_name'] = $student['name'];
$_SESSION['theme_primary_color'] = $student['theme_primary_color'] ?: '#FF6B9D';
$_SESSION['theme_secondary_color'] = $student['theme_secondary_color'] ?: '#FFB3D1';

$weekday = (int) date('N');

//Get today's schedule
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

// Load weekly stat chart
require_once '../includes/stat_chart_helpers.php';

$chart = get_student_weekly_stat_chart($pdo, $student);

//Separates the result
$chart_labels = $chart['labels'];
$chart_data = $chart['data'];
$has_chart_data = $chart['has_data'];
$previous_snapshot = $chart['previous_snapshot'];

//Load producer message and birthday helpers
require_once '../includes/producer_message_helpers.php';
require_once '../includes/birthday_banner_helpers.php';

//Generate producer message
$producer_message = get_producer_message(
    $pdo,
    $student,
    $today_schedules,
    $previous_snapshot ?: null
);

//Check birthday
$is_birthday = is_student_birthday_today($student);
$birthday_students = get_dashboard_birthday_students($pdo);

//Load header and sidebar
$page_title = 'Student Dashboard';
require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

<main class="dashboard-main">
    <!--Show birthday banner-->
    <?php require '../includes/birthday_banner.php'; ?>

    <!--Producer message card-->
    <section class="producer-message-card <?= $is_birthday ? 'birthday-message-card' : '' ?>">
        <p class="dashboard-eyebrow">
            <?= $is_birthday ? 'Birthday Producer Message' : 'Producer Message' ?>
        </p>
        <p class="producer-message-text">
            <?= htmlspecialchars($producer_message, ENT_QUOTES, 'UTF-8') ?>
        </p>
    </section>

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
            <div class="empty-dashboard-state">
                <strong>No schedule today</strong>
                <p>There are no fixed lessons or activities planned for this weekday.</p>
            </div>
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
            <section class="stats-preview">
                <div class="section-heading">
                    <div>
                        <p class="dashboard-eyebrow">Progress</p>
                        <h2>Stat Growth</h2>
                    </div>
                    <a href="/gakumas-sms/student/stats.php" class="panel-link">View all</a>
                </div>

                <?php if ($has_chart_data): ?>
                    <div class="stats-chart-wrap">
                        <canvas id="statProgressChart"></canvas>
                    </div>
                <?php else: ?>
                    <div class="empty-dashboard-state compact">
                        <strong>No stat history yet</strong>
                        <p>Your progress chart will appear after stats are recorded.</p>
                    </div>
                <?php endif; ?>
            </section>

            <section class="messages-preview">
                <div class="section-heading">
                    <div>
                        <p class="dashboard-eyebrow">Inbox</p>
                        <h2>Messages</h2>
                    </div>
                    <a href="/gakumas-sms/messages/inbox.php" class="panel-link">View all</a>
                </div>

                <?php if (empty($recent_messages)): ?>
                <div class="empty-dashboard-state compact">
                    <strong>No recent messages</strong>
                    <p>New producer or teacher messages will appear here.</p>
                </div>
                <?php else: ?>
                <div class="message-preview-list">
                    <?php foreach ($recent_messages as $message): ?>
                    <article class="message-preview-item <?= $message['is_read'] ? '' : 'unread' ?>">
                        <div>
                            <h3>
                                <?= htmlspecialchars($message['subject'] ?: 'No subject', ENT_QUOTES, 'UTF-8') ?>
                            </h3>
                            <p>
                                <?= htmlspecialchars($message['body'], ENT_QUOTES, 'UTF-8') ?>
                            </p>
                        </div>

                        <footer>
                            <span><?= htmlspecialchars($message['sender_name'] ?? 'Unknown sender', ENT_QUOTES, 'UTF-8') ?></span>
                            <time datetime="<?= htmlspecialchars($message['sent_at'], ENT_QUOTES, 'UTF-8') ?>">
                                <?= htmlspecialchars(date('M j', strtotime($message['sent_at'])), ENT_QUOTES, 'UTF-8') ?>
                            </time>
                        </footer>
                    </article>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </section>
        </aside>
    </div>
</main>

//Load Chart.js
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
//Pass PHP chart data to JavaScript
const statLabels = <?= json_encode($chart_labels, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
const statData = <?= json_encode($chart_data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;

//Find the highest stat value
const allStats = [
    ...(statData.vocal ?? []),
    ...(statData.dance ?? []),
    ...(statData.visual ?? [])
].filter(value => value !== null && value !== undefined);

const maxStat = allStats.length ? Math.max(...allStats) : 100;
const yMax = Math.ceil((maxStat + 10) / 10) * 10;

//Create the line chart
new Chart(document.getElementById('statProgressChart'), {
    type: 'line',
    data: {
        labels: statLabels,
        datasets: [
            {
                label: 'Vocal',
                data: statData.vocal,
                borderColor: getComputedStyle(document.body).getPropertyValue('--vocal-color').trim(),
                tension: 0.35,
                spanGaps: true
            },
            {
                label: 'Dance',
                data: statData.dance,
                borderColor: getComputedStyle(document.body).getPropertyValue('--dance-color').trim(),
                tension: 0.35,
                spanGaps: true
            },
            {
                label: 'Visual',
                data: statData.visual,
                borderColor: getComputedStyle(document.body).getPropertyValue('--visual-color').trim(),
                tension: 0.35,
                spanGaps: true
            }
        ]
    },
    //Chart option
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        },
        scales: {
            y: {
                min: 40,
                max: yMax,
                ticks: {
                    stepSize: 10
                }
            }
        }
    }
});
</script>

//Load Footer
<?php require_once '../includes/footer.php'; ?>
