<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_once dirname(__DIR__) . '/includes/admin_app.php';
require_once dirname(__DIR__) . '/includes/JobRepository.php';

kam_admin_app_boot();

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    if (Auth::verifyCsrf($_POST['csrf'] ?? null)) {
        JobRepository::jobDelete((int) $_POST['delete_id']);
        $message = 'Job opening deleted.';
    } else {
        $error = 'Invalid session token.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_id'])) {
    if (Auth::verifyCsrf($_POST['csrf'] ?? null)) {
        $job = JobRepository::jobFind((int) $_POST['toggle_id']);
        if ($job) {
            $newStatus = $job['status'] === 'active' ? 'closed' : 'active';
            JobRepository::jobSave((int) $job['id'], array_merge($job, ['status' => $newStatus]));
            $message = 'Job status updated to ' . ucfirst($newStatus) . '.';
        }
    }
}

$filters = [
    'status' => $_GET['status'] ?? '',
    'category' => $_GET['category'] ?? '',
    'q' => trim((string) ($_GET['q'] ?? '')),
];

$jobs = JobRepository::jobsAll($filters);
$csrf = Auth::csrfToken();
$categories = JobRepository::categories();

kam_admin_render(function () use ($jobs, $filters, $message, $error, $csrf, $categories): void {
    ob_start();
    ?>
    <?php if ($message): ?><div class="admin-alert admin-alert--success"><?= kam_h($message) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="admin-alert admin-alert--error"><?= kam_h($error) ?></div><?php endif; ?>

    <div class="admin-card">
        <div class="admin-card__head">
            <div>
                <h2><span class="material-symbols-outlined">work</span> Job Openings</h2>
                <p class="text-xs text-slate-500 mt-1">Manage active vacancies published on the Find a Job portal.</p>
            </div>
            <a href="job-edit.php" class="admin-btn admin-btn--primary">
                <span class="material-symbols-outlined">add</span> Post new job
            </a>
        </div>

        <!-- Filters Form -->
        <form method="get" class="admin-filters-bar" style="display:flex;gap:12px;padding:16px 24px;border-bottom:1px solid #f1f5f9;background:#fafbfc;flex-wrap:wrap;align-items:center;">
            <input type="text" name="q" placeholder="Search by title, location..." value="<?= kam_h($filters['q']) ?>" style="padding:8px 14px;border:1px solid #cbd5e1;border-radius:8px;font-size:13px;min-width:220px;"/>
            <select name="category" style="padding:8px 14px;border:1px solid #cbd5e1;border-radius:8px;font-size:13px;">
                <option value="">All Categories</option>
                <?php foreach ($categories as $k => $c): ?>
                    <option value="<?= kam_h($k) ?>" <?= $filters['category'] === $k ? 'selected' : '' ?>><?= kam_h($c) ?></option>
                <?php endforeach; ?>
            </select>
            <select name="status" style="padding:8px 14px;border:1px solid #cbd5e1;border-radius:8px;font-size:13px;">
                <option value="">All Statuses</option>
                <option value="active" <?= $filters['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                <option value="closed" <?= $filters['status'] === 'closed' ? 'selected' : '' ?>>Closed</option>
                <option value="draft" <?= $filters['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
            </select>
            <button type="submit" class="admin-btn admin-btn--ghost admin-btn--sm">Filter</button>
            <?php if (!empty($filters['q']) || !empty($filters['category']) || !empty($filters['status'])): ?>
                <a href="jobs.php" class="admin-btn admin-btn--ghost admin-btn--sm" style="color:#ef4444;">Clear</a>
            <?php endif; ?>
        </form>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Job Title &amp; Category</th>
                        <th>Location &amp; Type</th>
                        <th>Experience</th>
                        <th>Applications</th>
                        <th>Status</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($jobs)): ?>
                        <tr>
                            <td colspan="6" class="admin-table__empty">
                                <span class="material-symbols-outlined">work_off</span>
                                No job openings found. Click "Post new job" to create your first vacancy.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($jobs as $j): ?>
                            <tr>
                                <td>
                                    <strong><a href="job-edit.php?id=<?= (int) $j['id'] ?>"><?= kam_h($j['title']) ?></a></strong>
                                    <br/>
                                    <span class="admin-badge admin-badge--neutral" style="font-size:11px;"><?= kam_h($j['category']) ?></span>
                                </td>
                                <td>
                                    <?= kam_h($j['location']) ?>
                                    <br/>
                                    <small style="color:#64748b;"><?= kam_h($j['job_type']) ?></small>
                                </td>
                                <td><?= kam_h($j['experience_required'] ?: 'Not specified') ?></td>
                                <td>
                                    <a href="applications.php?job_id=<?= (int) $j['id'] ?>" style="font-weight:700;color:#0284c7;">
                                        <?= (int) $j['applications_count'] ?> candidates
                                    </a>
                                </td>
                                <td>
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="csrf" value="<?= kam_h($csrf) ?>"/>
                                        <input type="hidden" name="toggle_id" value="<?= (int) $j['id'] ?>"/>
                                        <button type="submit" class="admin-badge <?= $j['status'] === 'active' ? 'admin-badge--won' : 'admin-badge--lost' ?>" style="cursor:pointer;border:none;" title="Click to toggle status">
                                            <?= kam_h(ucfirst($j['status'])) ?>
                                        </button>
                                    </form>
                                </td>
                                <td style="text-align:right;white-space:nowrap;">
                                    <a href="job-edit.php?id=<?= (int) $j['id'] ?>" class="admin-btn admin-btn--ghost admin-btn--sm">Edit</a>
                                    <form method="post" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this job opening?');">
                                        <input type="hidden" name="csrf" value="<?= kam_h($csrf) ?>"/>
                                        <input type="hidden" name="delete_id" value="<?= (int) $j['id'] ?>"/>
                                        <button type="submit" class="admin-btn admin-btn--ghost admin-btn--sm" style="color:#ef4444;">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
    $content = ob_get_clean();
    $pageTitle = 'Job Openings';
    $activeNav = 'jobs';
    require __DIR__ . '/includes/layout.php';
});
