<?php
require_once '../includes/auth.php';
require_role('admin');

require_once '../config/database.php';
require_once '../includes/theme_settings_helpers.php';
require_once '../includes/admin_request_helpers.php';

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

apply_admin_request_theme();

$request_id = max(0, (int) ($_GET['id'] ?? 0));
$request = $request_id > 0 ? load_admin_request_detail($pdo, $request_id) : null;

if (!$request) {
    redirect_to_account_issue(
        'Request not found',
        'This request could not be found, or it is not assigned to an admin account.',
        404,
        '/gakumas-sms/admin/request.php',
        'Back to Request Inbox',
        false
    );
}

$type_label = producer_request_type_label($request['request_type'] ?? '');
$source_label = admin_request_source_label($request);
$is_forwarded_request = !empty($request['forwarded_by']);
$status = (string) ($request['status'] ?? 'pending');
$current_data = producer_request_visible_data(producer_request_decode_json($request['current_data'] ?? null));
$requested_data = producer_request_visible_data(producer_request_decode_json($request['requested_data'] ?? null));
$student_avatar_path = producer_request_avatar_path($request['student_avatar'] ?? '', $request['student_name'] ?? '');
$producer_avatar_path = producer_request_user_avatar_path($request['forwarder_avatar'] ?? '', $request['forwarder_role'] ?? 'producer');

$page_title = 'Admin Request Details';
$page_styles = ['/gakumas-sms/assets/css/pages/producer-request.css'];
require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

<main class="dashboard-main producer-request-main">
    <section class="producer-request-detail-heading">
        <a href="/gakumas-sms/admin/request.php" class="producer-request-back" aria-label="Back to request inbox">
            <i class="bi bi-arrow-left" aria-hidden="true"></i>
        </a>

        <div>
            <p class="dashboard-eyebrow"><?= e($type_label) ?></p>
            <h2><?= e($request['subject'] ?: $type_label) ?></h2>
            <p>
                <?= e($request['student_name']) ?>
                &middot;
                <?= e($request['school_year'] ?: 'No class') ?>
                &middot;
                Rank <?= e($request['rank'] ?: 'Debut') ?>
                &middot;
                <?= e($source_label) ?>
                <?php if (!empty($request['forwarder_name'])): ?>
                    &middot;
                    From <?= e($request['forwarder_name']) ?>
                <?php endif; ?>
            </p>
        </div>
    </section>

    <section class="producer-request-detail-grid">
        <div class="producer-request-main-column">
            <section class="producer-request-panel">
                <h3>Request Message</h3>
                <p class="producer-request-message">
                    <?= e($request['details'] ?: 'No message was included with this request.') ?>
                </p>
            </section>

            <section class="producer-request-panel">
                <h3>Requested Data</h3>

                <?php if (empty($requested_data)): ?>
                    <p class="producer-request-message">No structured requested data is attached yet.</p>
                <?php else: ?>
                    <dl class="producer-request-data">
                        <?php foreach ($requested_data as $key => $value): ?>
                            <div>
                                <dt><?= e(producer_request_field_label((string) $key)) ?></dt>
                                <dd><?= e(producer_request_display_value((string) $key, $value)) ?></dd>
                            </div>
                        <?php endforeach; ?>
                    </dl>
                <?php endif; ?>
            </section>

            <section class="producer-request-panel">
                <h3>Current Data</h3>

                <?php if (empty($current_data)): ?>
                    <p class="producer-request-message">No current-data snapshot is attached yet.</p>
                <?php else: ?>
                    <dl class="producer-request-data">
                        <?php foreach ($current_data as $key => $value): ?>
                            <div>
                                <dt><?= e(producer_request_field_label((string) $key)) ?></dt>
                                <dd><?= e(producer_request_display_value((string) $key, $value)) ?></dd>
                            </div>
                        <?php endforeach; ?>
                    </dl>
                <?php endif; ?>
            </section>
        </div>

        <aside class="producer-request-side-column">
            <section class="producer-request-panel">
                <h3>Student</h3>
                <div class="producer-request-profile">
                    <img src="<?= e($student_avatar_path) ?>" alt="<?= e($request['student_name']) ?>" class="producer-request-avatar">
                    <div>
                        <strong><?= e($request['student_name']) ?></strong>
                        <?php if (!empty($request['student_name_jp'])): ?>
                            <small lang="ja"><?= e($request['student_name_jp']) ?></small>
                        <?php endif; ?>
                        <small><?= e($request['school_year'] ?: 'No class') ?> &middot; Rank <?= e($request['rank'] ?: 'Debut') ?></small>
                    </div>
                </div>
            </section>

            <section class="producer-request-panel">
                <h3><?= $is_forwarded_request ? 'Producer' : 'Route' ?></h3>
                <?php if ($is_forwarded_request): ?>
                    <div class="producer-request-profile">
                        <img src="<?= e($producer_avatar_path) ?>" alt="<?= e($request['forwarder_name'] ?: 'Producer') ?>" class="producer-request-avatar">
                        <div>
                            <strong><?= e($request['forwarder_name'] ?: 'Producer') ?></strong>
                            <small>Forwarded this request to admin</small>
                            <small><?= e(!empty($request['forwarded_at']) ? producer_request_time_label($request['forwarded_at']) : 'Forward time not listed') ?></small>
                        </div>
                    </div>
                <?php else: ?>
                    <p class="producer-request-message">This request was sent directly from the student to admin.</p>
                <?php endif; ?>
            </section>

            <section class="producer-request-panel">
                <h3>Status</h3>
                <dl class="producer-request-data">
                    <div>
                        <dt>Status</dt>
                        <dd><?= e(producer_request_status_label($status)) ?></dd>
                    </div>
                    <div>
                        <dt>Created</dt>
                        <dd><?= e(producer_request_time_label($request['created_at'] ?? '')) ?></dd>
                    </div>
                    <div>
                        <dt>Type</dt>
                        <dd><?= e($type_label) ?></dd>
                    </div>
                    <div>
                        <dt>Assigned Admin</dt>
                        <dd><?= e($request['recipient_name'] ?: 'Admin') ?></dd>
                    </div>
                    <div>
                        <dt>Handled By</dt>
                        <dd><?= e($request['handler_name'] ?: 'Not handled yet') ?></dd>
                    </div>
                    <div>
                        <dt>Mode</dt>
                        <dd><?= e($request['apply_mode'] ?: 'Not selected') ?></dd>
                    </div>
                </dl>
            </section>

            <section class="producer-request-actions" aria-label="Request actions">
                <button type="button" class="btn btn-primary" disabled>
                    <i class="bi bi-magic" aria-hidden="true"></i>
                    Auto Edit
                </button>

                <button type="button" class="btn btn-outline-secondary" disabled>
                    <i class="bi bi-pencil-square" aria-hidden="true"></i>
                    Manual Edit
                </button>

                <button type="button" class="btn btn-outline-secondary" disabled>
                    <i class="bi bi-check2-circle" aria-hidden="true"></i>
                    Mark Completed
                </button>

                <button type="button" class="btn btn-outline-danger" disabled>
                    <i class="bi bi-x-circle" aria-hidden="true"></i>
                    Reject
                </button>

                <p class="producer-request-actions-note">Interface preview only. These actions do not submit yet.</p>
            </section>
        </aside>
    </section>
</main>

<?php require_once '../includes/footer.php'; ?>
