<?php
require_once '../includes/auth.php';
require_role('producer');

require_once '../config/database.php';
require_once '../includes/birthday_banner_helpers.php';
require_once '../includes/notifications_helpers.php';

generate_automatic_notifications($pdo);

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

$weekday = (int) date('N');
$producer_id = (int) $producer['id'];

// Producer overview totals
$student_count_stmt = $pdo->prepare('SELECT COUNT(*) FROM students WHERE producer_id = ?');
$student_count_stmt->execute([$producer_id]);

// Prepares the numbers shown at the top of the dashboard
$dashboard_counts = [
    'students' => (int) $student_count_stmt->fetchColumn(),
    'current_schedules' => 0,
    'upcoming_events' => (int) $pdo->query('SELECT COUNT(*) FROM events WHERE date >= NOW()')->fetchColumn(),
];

// Average stats for this producer's students
$average_stmt = $pdo->prepare(
    'SELECT
        COALESCE(ROUND(AVG(vocal)), 0) AS vocal,
        COALESCE(ROUND(AVG(dance)), 0) AS dance,
        COALESCE(ROUND(AVG(visual)), 0) AS visual
     FROM students
     WHERE producer_id = ?'
);
$average_stmt->execute([$producer_id]);
$average_stats = $average_stmt->fetch() ?: ['vocal' => 0, 'dance' => 0, 'visual' => 0];

// Current fixed weekly schedules for this producer's students
$schedule_stmt = $pdo->prepare(
    'SELECT
        rs.activity_type,
        rs.title,
        rs.start_time,
        rs.end_time,
        rs.location,
        s.name AS student_name
     FROM recurring_schedules rs
     INNER JOIN students s ON s.id = rs.student_id
     WHERE rs.weekday = ?
       AND rs.is_active = 1
       AND rs.start_time <= CURTIME()
       AND rs.end_time >= CURTIME()
       AND s.producer_id = ?
     ORDER BY rs.start_time, s.name'
);
$schedule_stmt->execute([$weekday, $producer_id]);
$current_schedules = $schedule_stmt->fetchAll();
$dashboard_counts['current_schedules'] = count($current_schedules);

// Highest student for each stat
$top_students = [];
$stat_labels = [
    'vocal' => 'Vocal',
    'dance' => 'Dance',
    'visual' => 'Visual',
];

foreach ($stat_labels as $stat_key => $stat_label) {
    $top_stat_stmt = $pdo->prepare(
        'SELECT
            s.name,
            s.' . $stat_key . ' AS stat_value,
            u.avatar,
            u.theme_primary_color,
            u.theme_secondary_color
         FROM students s
         INNER JOIN users u ON u.id = s.user_id
         WHERE s.producer_id = ?
         ORDER BY ' . $stat_key . ' DESC, name ASC
         LIMIT 1'
    );
    $top_stat_stmt->execute([$producer_id]);
    $top_stat_student = $top_stat_stmt->fetch();

    if ($top_stat_student) {
        $top_students[] = [
            'stat_key' => $stat_key,
            'stat_label' => $stat_label,
            'name' => $top_stat_student['name'],
            'stat_value' => (int) $top_stat_student['stat_value'],
            'avatar' => $top_stat_student['avatar'] ?? '',
            'theme_primary_color' => $top_stat_student['theme_primary_color'] ?: '#FF6B9D',
            'theme_secondary_color' => $top_stat_student['theme_secondary_color'] ?: '#FFB3D1',
        ];
    }
}

// Recent inbox messages for the producer
$messages_stmt = $pdo->prepare(
    'SELECT
        m.subject,
        m.body,
        m.is_read,
        m.sent_at,
        u.username AS sender_name
     FROM messages m
     LEFT JOIN users u ON u.id = m.sender_id
     WHERE m.receiver_id = ?
       AND m.is_deleted_by_receiver = 0
     ORDER BY m.sent_at DESC
     LIMIT 3'
);
$messages_stmt->execute([$_SESSION['id']]);
$recent_messages = $messages_stmt->fetchAll();

$birthday_students = get_dashboard_birthday_students($pdo);

$page_title = 'Producer Dashboard';
require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

<main class="dashboard-main">
    <?php require '../includes/birthday_banner.php'; ?>

    <section class="dashboard-hero">
        <div class="student-info">
            <p class="dashboard-eyebrow">Producer Overview</p>

            <h2>
                Welcome back,
                <?= htmlspecialchars($producer['username'] ?? 'Producer', ENT_QUOTES, 'UTF-8') ?>
            </h2>

            <dl class="student-details">
                <div>
                    <dt>Students</dt>
                    <dd><?= (int) $dashboard_counts['students'] ?></dd>
                </div>

                <div>
                    <dt>Current Schedules</dt>
                    <dd><?= (int) $dashboard_counts['current_schedules'] ?></dd>
                </div>

                <div>
                    <dt>Upcoming Events</dt>
                    <dd><?= (int) $dashboard_counts['upcoming_events'] ?></dd>
                </div>
            </dl>
        </div>

        <aside class="current-stats">
            <h2>Average Stats</h2>

            <div class="stat-row vocal">
                <span>Vocal</span>
                <strong><?= (int) $average_stats['vocal'] ?></strong>
            </div>

            <div class="stat-row dance">
                <span>Dance</span>
                <strong><?= (int) $average_stats['dance'] ?></strong>
            </div>

            <div class="stat-row visual">
                <span>Visual</span>
                <strong><?= (int) $average_stats['visual'] ?></strong>
            </div>
        </aside>
    </section>

    <div class="dashboard-grid">
        <section class="today-schedule">
            <div class="section-heading">
                <div>
                    <p class="dashboard-eyebrow">Producer Plan</p>
                    <h2>Current Schedules</h2>
                </div>
                <span><?= htmlspecialchars(date('l'), ENT_QUOTES, 'UTF-8') ?></span>
            </div>

            <?php if (empty($current_schedules)): ?>
            <div class="empty-dashboard-state">
                <strong>No current schedules</strong>
                <p>None of your students have fixed lessons or activities happening right now.</p>
            </div>
            <?php else: ?>
            <div class="schedule-list scrollable-schedule-list">
                <?php $current_time = date('H:i:s'); ?>

                <?php foreach ($current_schedules as $schedule): ?>
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

                            <span class="schedule-status <?= htmlspecialchars(strtolower($schedule_status), ENT_QUOTES, 'UTF-8') ?>">
                                <?= htmlspecialchars($schedule_status, ENT_QUOTES, 'UTF-8') ?>
                            </span>
                        </div>

                        <p>
                            <?= htmlspecialchars($schedule['student_name'], ENT_QUOTES, 'UTF-8') ?>
                            &middot;
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
                        <h2>Highest Stats</h2>
                    </div>
                    <a href="/gakumas-sms/producer/students.php" class="panel-link">View all</a>
                </div>

                <?php if (empty($top_students)): ?>
                <div class="empty-dashboard-state compact">
                    <strong>No students yet</strong>
                    <p>Add or assign students to start tracking their progress.</p>
                </div>
                <?php else: ?>
                <div class="schedule-list">
                    <?php foreach ($top_students as $student): ?>
                    <?php
                    $profile_avatar = trim((string) ($student['avatar'] ?? ''));

                    if ($profile_avatar !== '') {
                        $avatar_path = str_replace('\\', '/', $profile_avatar);

                        if (!str_starts_with($avatar_path, '/') && !preg_match('/^https?:\/\//i', $avatar_path)) {
                            $avatar_path = '/gakumas-sms/assets/images/avatars/idols/' . rawurlencode($avatar_path);
                        }
                    } else {
                        $avatar_path = '/gakumas-sms/assets/images/avatars/default.webp';
                    }
                    ?>
                    <article class="top-stat-card <?= htmlspecialchars($student['stat_key'], ENT_QUOTES, 'UTF-8') ?>"
                        style="--student-primary: <?= htmlspecialchars($student['theme_primary_color'], ENT_QUOTES, 'UTF-8') ?>; --student-secondary: <?= htmlspecialchars($student['theme_secondary_color'], ENT_QUOTES, 'UTF-8') ?>;">
                        <img src="<?= htmlspecialchars($avatar_path, ENT_QUOTES, 'UTF-8') ?>"
                            alt="<?= htmlspecialchars($student['name'], ENT_QUOTES, 'UTF-8') ?>" class="top-stat-avatar">

                        <div class="top-stat-content">
                            <span><?= htmlspecialchars($student['stat_label'], ENT_QUOTES, 'UTF-8') ?></span>
                            <h3><?= htmlspecialchars($student['name'], ENT_QUOTES, 'UTF-8') ?></h3>
                        </div>

                        <strong><?= (int) $student['stat_value'] ?></strong>
                    </article>
                    <?php endforeach; ?>
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
                    <p>New student or teacher messages will appear here.</p>
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

<?php require_once '../includes/footer.php'; ?>
