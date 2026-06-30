<?php
require_once '../includes/auth.php';
require_role('student');

require_once '../config/database.php';
require_once '../includes/theme_settings_helpers.php';

//Helper for safe output
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}


// Get current student profile
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
        su.theme_primary_color,
        su.theme_secondary_color,
        pu.username AS producer_name
     FROM students s
     INNER JOIN users su ON su.id = s.user_id
     LEFT JOIN users pu ON pu.id = s.producer_id
     WHERE s.user_id = ?
     LIMIT 1'
);

$stmt->execute([$_SESSION['id']]);
$student = $stmt->fetch();

//Redirect to the error page
if (!$student) {
    redirect_to_account_issue(
        'Student profile not found',
        'Your login is active, but no student profile is linked to this account yet. Please log out and ask an administrator to check your student account setup.',
        404
    );
}

$_SESSION['student_name'] = $student['name'];
$_SESSION['theme_primary_color'] = $student['theme_primary_color'] ?: DEFAULT_THEME_PRIMARY;
$_SESSION['theme_secondary_color'] = $student['theme_secondary_color'] ?: DEFAULT_THEME_SECONDARY;

// Load weekly stat chart
require_once '../includes/stat_chart_helpers.php';

$chart = get_student_weekly_stat_chart($pdo, $student);

//Separates the result
$chart_labels = $chart['labels'];
$chart_data = $chart['data'];
$has_chart_data = $chart['has_data'];
$previous_snapshot = $chart['previous_snapshot'];

// Get the student's 10 most recent individual stat changes.
$history_stmt = $pdo->prepare(
    'SELECT id, stat_type, old_value, new_value, reason, recorded_at
     FROM stat_history
     WHERE student_id = ?
     ORDER BY recorded_at DESC, id DESC
     LIMIT 10'
);
$history_stmt->execute([(int) $student['id']]);
$stat_history = $history_stmt->fetchAll();


$page_title = 'My Stats';
$page_styles = ['/gakumas-sms/assets/css/pages/stats.css'];
require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

<main class="dashboard-main stats-main" data-student-performance data-vocal="<?= (int) ($student['vocal'] ?? 0) ?>"
    data-dance="<?= (int) ($student['dance'] ?? 0) ?>" data-visual="<?= (int) ($student['visual'] ?? 0) ?>">

    <section class="dashboard-hero stats-overview-card" aria-labelledby="stats-overview-title">
        <div class="student-info">
            <header class="stats-heading">
                <p class="dashboard-eyebrow">Performance Summary</p>
                <h2 id="stats-overview-title">Current Stats</h2>
                <p>See your core abilities and overall standing at a glance.</p>
            </header>

            <div class="stat-summary-grid">
                <div class="stat-row vocal">
                    <span class="stat-label">
                        <i class="bi bi-mic-fill" aria-hidden="true"></i>
                        Vocal
                    </span>
                    <strong><?= (int) ($student['vocal'] ?? 0) ?></strong>
                </div>

                <div class="stat-row dance">
                    <span class="stat-label">
                        <i class="bi bi-music-note-beamed" aria-hidden="true"></i>
                        Dance
                    </span>
                    <strong><?= (int) ($student['dance'] ?? 0) ?></strong>
                </div>

                <div class="stat-row visual">
                    <span class="stat-label">
                        <i class="bi bi-stars" aria-hidden="true"></i>
                        Visual
                    </span>
                    <strong><?= (int) ($student['visual'] ?? 0) ?></strong>
                </div>
            </div>
        </div>
    </section>

    <div class="rank-summary-grid">
        <aside class="current-rank" aria-labelledby="current-rank-title">
            <div class="current-rank-heading">
                <span class="current-rank-icon" aria-hidden="true">
                    <i class="bi bi-trophy-fill"></i>
                </span>
                <div>
                    <p class="dashboard-eyebrow">Standing</p>
                    <h2 id="current-rank-title">Current Rank</h2>
                </div>
            </div>

            <div class="current-rank-metrics">
                <div class="stat-row rating">
                    <span>Rating</span>
                    <strong data-current-rating>&mdash;</strong>
                </div>

                <div class="stat-row rank">
                    <span>Rank</span>
                    <strong class="rank-badge" data-rank="<?= e($student['rank'] ?? 'Debut') ?>">
                        <?= e($student['rank'] ?? 'Debut') ?>
                    </strong>
                </div>
            </div>
        </aside>

        <section class="rank-progress-card" aria-labelledby="rank-progress-title">
            <header class="rank-progress-heading">
                <div>
                    <p class="dashboard-eyebrow">Rank Progress</p>
                    <h2 id="rank-progress-title">Your next milestone</h2>
                </div>

                <p class="rank-progress-rating">
                    <span>Current Rating</span>
                    <strong data-current-rating>&mdash;</strong>
                </p>
            </header>

            <div class="rank-progress-labels" aria-hidden="true">
                <div>
                    <small>Current</small>
                    <span class="rank-badge" data-current-rank>&mdash;</span>
                </div>
                <div data-next-rank-group>
                    <small>Next</small>
                    <span class="rank-badge" data-next-rank>&mdash;</span>
                </div>
            </div>

            <div class="rank-progress-track" data-rank-progress-track role="progressbar" aria-label="Rank progress"
                aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
                <span class="rank-progress-fill" data-rank-progress-fill></span>
            </div>

            <p class="rank-progress-remaining" data-rank-progress-remaining>Calculating progress&hellip;</p>
        </section>
    </div>

    <div class="stats-detail-grid">
    <section class="stats-preview">
        <div class="section-heading stat-chart-heading">
            <div>
                <p class="dashboard-eyebrow">Weekly Progress</p>
                <h2>Stat Growth</h2>
                <p class="stat-chart-description">Compare your Vocal, Dance, and Visual performance over the latest seven days.</p>
            </div>
            <span class="stat-chart-period">Last 7 days</span>
        </div>

        <?php if ($has_chart_data): ?>
        <fieldset class="stat-chart-controls" aria-label="Choose stats to display">
            <legend class="visually-hidden">Choose stats to display</legend>

            <label class="stat-chart-toggle vocal">
                <input type="checkbox" data-stat-toggle="vocal" checked>
                <span aria-hidden="true"></span>
                Vocal
            </label>

            <label class="stat-chart-toggle dance">
                <input type="checkbox" data-stat-toggle="dance" checked>
                <span aria-hidden="true"></span>
                Dance
            </label>

            <label class="stat-chart-toggle visual">
                <input type="checkbox" data-stat-toggle="visual" checked>
                <span aria-hidden="true"></span>
                Visual
            </label>
        </fieldset>

        <div class="stats-chart-wrap">
            <canvas id="statProgressChart" aria-label="Seven-day Vocal, Dance, and Visual stat chart" role="img"></canvas>
        </div>
        <?php else: ?>
        <div class="empty-dashboard-state compact">
            <strong>No stat history yet</strong>
            <p>Your progress chart will appear after stats are recorded.</p>
        </div>
        <?php endif; ?>
    </section>

    <section class="stat-history-card" aria-labelledby="stat-history-title">
        <div class="section-heading stat-history-heading">
            <div>
                <p class="dashboard-eyebrow">Recent Activity</p>
                <h2 id="stat-history-title">Latest Stat History</h2>
                <p>Review the latest changes made to your performance stats.</p>
            </div>
            <span>Latest 10</span>
        </div>

        <?php if (empty($stat_history)): ?>
        <div class="empty-dashboard-state compact">
            <strong>No stat changes yet</strong>
            <p>Your latest Vocal, Dance, and Visual changes will appear here.</p>
        </div>
        <?php else: ?>
        <div class="stat-history-table-wrap">
            <table class="stat-history-table">
                <thead>
                    <tr>
                        <th scope="col">Date</th>
                        <th scope="col">Stat</th>
                        <th scope="col">Value change</th>
                        <th scope="col">Change</th>
                        <th scope="col">Reason</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($stat_history as $history): ?>
                    <?php
                    $stat_type = strtolower((string) $history['stat_type']);
                    $old_value = (int) $history['old_value'];
                    $new_value = (int) $history['new_value'];
                    $value_change = $new_value - $old_value;
                    $change_class = $value_change > 0 ? 'positive' : ($value_change < 0 ? 'negative' : 'neutral');
                    $change_text = ($value_change > 0 ? '+' : '') . number_format($value_change);
                    $reason = trim((string) ($history['reason'] ?? ''));
                    $recorded_timestamp = strtotime((string) $history['recorded_at']);
                    $stat_icon = match ($stat_type) {
                        'vocal' => 'bi-mic-fill',
                        'dance' => 'bi-music-note-beamed',
                        'visual' => 'bi-stars',
                        default => 'bi-graph-up',
                    };
                    ?>
                    <tr>
                        <td>
                            <time datetime="<?= e((string) $history['recorded_at']) ?>">
                                <strong><?= e(date('M j, Y', $recorded_timestamp)) ?></strong>
                                <span><?= e(date('g:i A', $recorded_timestamp)) ?></span>
                            </time>
                        </td>
                        <td>
                            <span class="history-stat-badge <?= e($stat_type) ?>">
                                <i class="bi <?= e($stat_icon) ?>" aria-hidden="true"></i>
                                <?= e(ucfirst($stat_type)) ?>
                            </span>
                        </td>
                        <td>
                            <span class="history-values">
                                <span><?= number_format($old_value) ?></span>
                                <i class="bi bi-arrow-right" aria-hidden="true"></i>
                                <strong><?= number_format($new_value) ?></strong>
                            </span>
                        </td>
                        <td>
                            <span class="history-change <?= e($change_class) ?>"><?= e($change_text) ?></span>
                        </td>
                        <td>
                            <span class="history-reason"><?= e($reason !== '' ? $reason : 'No reason provided') ?></span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </section>
    </div>

</main>
<script src="/gakumas-sms/assets/js/student-rank.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
//Pass PHP chart data to JavaScript
const statLabels = <?= json_encode($chart_labels, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
const statData = <?= json_encode($chart_data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;

const chartCanvas = document.getElementById('statProgressChart');

if (chartCanvas && typeof Chart !== 'undefined') {
    const styles = getComputedStyle(document.body);
    const statColors = {
        vocal: styles.getPropertyValue('--vocal-color').trim(),
        dance: styles.getPropertyValue('--dance-color').trim(),
        visual: styles.getPropertyValue('--visual-color').trim()
    };

    const hexToRgba = (hex, opacity) => {
        const normalized = hex.replace('#', '');
        const value = Number.parseInt(normalized, 16);
        const red = (value >> 16) & 255;
        const green = (value >> 8) & 255;
        const blue = value & 255;

        return `rgba(${red}, ${green}, ${blue}, ${opacity})`;
    };

    const makeDataset = (key, label) => ({
        statKey: key,
        label,
        data: statData[key] ?? [],
        borderColor: statColors[key],
        backgroundColor: hexToRgba(statColors[key], 0.12),
        pointBackgroundColor: statColors[key],
        pointBorderColor: '#ffffff',
        pointBorderWidth: 2,
        pointRadius: 4,
        pointHoverRadius: 6,
        borderWidth: 3,
        fill: true,
        tension: 0.35,
        spanGaps: true
    });

    const statChart = new Chart(chartCanvas, {
        type: 'line',
        data: {
            labels: statLabels,
            datasets: [
                makeDataset('vocal', 'Vocal'),
                makeDataset('dance', 'Dance'),
                makeDataset('visual', 'Visual')
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    displayColors: true,
                    padding: 12,
                    callbacks: {
                        label: context => `${context.dataset.label}: ${Number(context.parsed.y).toLocaleString()}`
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: styles.getPropertyValue('--text-secondary').trim()
                    }
                },
                y: {
                    beginAtZero: false,
                    grace: '8%',
                    grid: {
                        color: 'rgba(100, 116, 139, 0.12)'
                    },
                    ticks: {
                        precision: 0,
                        color: styles.getPropertyValue('--text-secondary').trim(),
                        callback: value => Number(value).toLocaleString()
                    }
                }
            }
        }
    });

    document.querySelectorAll('[data-stat-toggle]').forEach((toggle) => {
        toggle.addEventListener('change', () => {
            const datasetIndex = statChart.data.datasets.findIndex(
                dataset => dataset.statKey === toggle.dataset.statToggle
            );

            if (datasetIndex !== -1) {
                statChart.setDatasetVisibility(datasetIndex, toggle.checked);
                statChart.update();
            }
        });
    });
}
</script>
<?php require_once '../includes/footer.php'; ?>
