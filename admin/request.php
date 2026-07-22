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

$requests = load_admin_request_inbox($pdo);
$pending_count = count(array_filter($requests, static fn(array $request): bool => ($request['status'] ?? '') === 'pending'));
$in_progress_count = count(array_filter($requests, static fn(array $request): bool => ($request['status'] ?? '') === 'in_progress'));

$page_title = 'Admin Request Inbox';
$page_styles = ['/gakumas-sms/assets/css/pages/producer-request.css'];
require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

<main class="dashboard-main producer-request-main">
    <section class="producer-request-hero">
        <div>
            <p class="dashboard-eyebrow">Admin Request Desk</p>
            <h2>Student Requests</h2>
            <p>Review student profile and song requests that need admin approval or correction.</p>
        </div>

        <div class="producer-request-summary">
            <div>
                <span>Pending</span>
                <strong><?= (int) $pending_count ?></strong>
            </div>

            <div>
                <span>In Progress</span>
                <strong><?= (int) $in_progress_count ?></strong>
            </div>
        </div>
    </section>

    <section class="producer-request-toolbar">
        <label class="producer-request-search" for="adminRequestSearch">
            <i class="bi bi-search" aria-hidden="true"></i>
            <input type="search" id="adminRequestSearch" placeholder="Search student, class, rank, request type, or assignee">
        </label>

        <div class="producer-request-status-tabs" aria-label="Request status filter">
            <button type="button" class="is-active" data-request-filter="all">All</button>
            <button type="button" data-request-filter="pending">Pending</button>
            <button type="button" data-request-filter="in_progress">In Progress</button>
            <button type="button" data-request-filter="approved">Approved</button>
            <button type="button" data-request-filter="rejected">Rejected</button>
        </div>
    </section>

    <?php if (empty($requests)): ?>
        <section class="producer-request-list producer-request-empty">
            <i class="bi bi-inbox" aria-hidden="true"></i>
            <strong>No admin requests yet</strong>
            <p>Student requests assigned to admin will appear here.</p>
        </section>
    <?php else: ?>
        <section class="producer-request-list" id="adminRequestList">
            <?php foreach ($requests as $request): ?>
                <?php
                $type_label = producer_request_type_label($request['request_type'] ?? '');
                $source_label = admin_request_source_label($request);
                $route_class = admin_request_route_class($request);
                $status = (string) ($request['status'] ?? 'pending');
                $student_name = (string) ($request['student_name'] ?? 'Student');
                $avatar_path = producer_request_avatar_path($request['student_avatar'] ?? '', $student_name);

                $search_text = strtolower(implode(' ', [
                    $student_name,
                    $request['student_name_jp'] ?? '',
                    $request['school_year'] ?? '',
                    $request['rank'] ?? '',
                    $type_label,
                    $source_label,
                    $request['forwarder_name'] ?? '',
                    $request['recipient_name'] ?? '',
                    producer_request_status_label($status),
                    producer_request_preview($request),
                ]));
                ?>
                <a
                    href="/gakumas-sms/admin/request_view.php?id=<?= (int) $request['id'] ?>"
                    class="producer-request-row"
                    data-request-row
                    data-request-status="<?= e($status) ?>"
                    data-request-search="<?= e($search_text) ?>">
                    <img src="<?= e($avatar_path) ?>" alt="<?= e($student_name) ?>" class="producer-request-avatar">

                    <div class="producer-request-copy">
                        <div class="producer-request-heading">
                            <h3><?= e($student_name) ?></h3>
                            <time datetime="<?= e($request['created_at']) ?>"><?= e(producer_request_time_label($request['created_at'] ?? '')) ?></time>
                        </div>

                        <p class="producer-request-preview"><?= e(producer_request_preview($request)) ?></p>

                        <div class="producer-request-meta">
                            <span><?= e($request['school_year'] ?: 'No class') ?></span>
                            <span>Rank <?= e($request['rank'] ?: 'Debut') ?></span>
                            <span><?= e($type_label) ?></span>
                            <span class="producer-request-route producer-request-route-<?= e($route_class) ?>"><?= e($source_label) ?></span>
                            <?php if (!empty($request['forwarder_name'])): ?>
                                <span>From <?= e($request['forwarder_name']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <span class="producer-request-status producer-request-status-<?= e(str_replace('_', '-', $status)) ?>">
                        <?= e(producer_request_status_label($status)) ?>
                    </span>
                </a>
            <?php endforeach; ?>
        </section>

        <section class="producer-request-list producer-request-empty d-none" id="adminRequestEmpty">
            <i class="bi bi-search" aria-hidden="true"></i>
            <strong>No matching requests</strong>
            <p>Try another student, class, rank, request type, or status.</p>
        </section>
    <?php endif; ?>
</main>

<script>
const adminRequestSearch = document.getElementById('adminRequestSearch');
const adminRequestRows = Array.from(document.querySelectorAll('[data-request-row]'));
const adminRequestEmpty = document.getElementById('adminRequestEmpty');
const adminRequestFilterButtons = Array.from(document.querySelectorAll('[data-request-filter]'));
let adminRequestStatusFilter = 'all';

function filterAdminRequests() {
    const query = adminRequestSearch ? adminRequestSearch.value.trim().toLowerCase() : '';
    let visibleCount = 0;

    adminRequestRows.forEach((row) => {
        const matchesSearch = !query || row.dataset.requestSearch.includes(query);
        const matchesStatus = adminRequestStatusFilter === 'all' || row.dataset.requestStatus === adminRequestStatusFilter;
        const isVisible = matchesSearch && matchesStatus;

        row.classList.toggle('d-none', !isVisible);

        if (isVisible) {
            visibleCount += 1;
        }
    });

    if (adminRequestEmpty) {
        adminRequestEmpty.classList.toggle('d-none', visibleCount > 0);
    }
}

if (adminRequestSearch) {
    adminRequestSearch.addEventListener('input', filterAdminRequests);
}

adminRequestFilterButtons.forEach((button) => {
    button.addEventListener('click', () => {
        adminRequestStatusFilter = button.dataset.requestFilter || 'all';

        adminRequestFilterButtons.forEach((item) => {
            item.classList.toggle('is-active', item === button);
        });

        filterAdminRequests();
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>
