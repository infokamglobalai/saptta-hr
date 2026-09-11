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
        JobRepository::applicationDelete((int) $_POST['delete_id']);
        $message = 'Candidate application deleted.';
    }
}

$page = max(1, (int) ($_GET['page'] ?? 1));
$filters = [
    'job_id' => $_GET['job_id'] ?? '',
    'status' => $_GET['status'] ?? '',
    'qualification' => $_GET['qualification'] ?? '',
    'q' => trim((string) ($_GET['q'] ?? '')),
];

$result = JobRepository::applicationsList($filters, $page, 20);
$allJobs = JobRepository::jobsAll();
$csrf = Auth::csrfToken();

kam_admin_render(function () use ($result, $filters, $allJobs, $message, $error, $csrf, $page): void {
    ob_start();
    ?>
    <?php if ($message): ?><div class="admin-alert admin-alert--success"><?= kam_h($message) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="admin-alert admin-alert--error"><?= kam_h($error) ?></div><?php endif; ?>

    <div class="admin-card">
        <div class="admin-card__head">
            <div>
                <h2><span class="material-symbols-outlined">assignment_ind</span> Candidate Applications</h2>
                <p class="text-xs text-slate-500 mt-1">Review candidate registration forms and uploaded documents.</p>
            </div>
            <span class="admin-badge admin-badge--qualified"><?= (int) $result['total'] ?> Total Applications</span>
        </div>

        <!-- Filters Form -->
        <form method="get" class="admin-filters-bar" style="display:flex;gap:12px;padding:16px 24px;border-bottom:1px solid #f1f5f9;background:#fafbfc;flex-wrap:wrap;align-items:center;">
            <input type="text" name="q" placeholder="Search candidate, email, mobile, reg no..." value="<?= kam_h($filters['q']) ?>" style="padding:8px 14px;border:1px solid #cbd5e1;border-radius:8px;font-size:13px;min-width:240px;"/>
            
            <select name="job_id" style="padding:8px 14px;border:1px solid #cbd5e1;border-radius:8px;font-size:13px;max-width:220px;">
                <option value="">All Applied Jobs</option>
                <?php foreach ($allJobs as $j): ?>
                    <option value="<?= (int) $j['id'] ?>" <?= ($filters['job_id'] == $j['id']) ? 'selected' : '' ?>><?= kam_h($j['title']) ?></option>
                <?php endforeach; ?>
            </select>

            <select name="qualification" style="padding:8px 14px;border:1px solid #cbd5e1;border-radius:8px;font-size:13px;">
                <option value="">All Qualifications</option>
                <option value="ITI" <?= $filters['qualification'] === 'ITI' ? 'selected' : '' ?>>ITI</option>
                <option value="Diploma" <?= $filters['qualification'] === 'Diploma' ? 'selected' : '' ?>>Diploma</option>
                <option value="B.E. / B.Tech" <?= $filters['qualification'] === 'B.E. / B.Tech' ? 'selected' : '' ?>>B.E. / B.Tech</option>
                <option value="Other" <?= $filters['qualification'] === 'Other' ? 'selected' : '' ?>>Other</option>
            </select>

            <select name="status" style="padding:8px 14px;border:1px solid #cbd5e1;border-radius:8px;font-size:13px;">
                <option value="">All Statuses</option>
                <option value="new" <?= $filters['status'] === 'new' ? 'selected' : '' ?>>New</option>
                <option value="reviewing" <?= $filters['status'] === 'reviewing' ? 'selected' : '' ?>>Reviewing</option>
                <option value="shortlisted" <?= $filters['status'] === 'shortlisted' ? 'selected' : '' ?>>Shortlisted</option>
                <option value="interview" <?= $filters['status'] === 'interview' ? 'selected' : '' ?>>Interview</option>
                <option value="selected" <?= $filters['status'] === 'selected' ? 'selected' : '' ?>>Selected</option>
                <option value="rejected" <?= $filters['status'] === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                <option value="hold" <?= $filters['status'] === 'hold' ? 'selected' : '' ?>>On Hold</option>
            </select>

            <button type="submit" class="admin-btn admin-btn--ghost admin-btn--sm">Filter</button>
            <?php if (!empty($filters['q']) || !empty($filters['job_id']) || !empty($filters['status']) || !empty($filters['qualification'])): ?>
                <a href="applications.php" class="admin-btn admin-btn--ghost admin-btn--sm" style="color:#ef4444;">Clear</a>
            <?php endif; ?>
        </form>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Candidate / Reg No.</th>
                        <th>Applied Position</th>
                        <th>Qualification &amp; Trade</th>
                        <th>Experience</th>
                        <th>Documents</th>
                        <th>Status</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($result['items'])): ?>
                        <tr>
                            <td colspan="7" class="admin-table__empty">
                                <span class="material-symbols-outlined">folder_open</span>
                                No candidate applications found matching the selected filters.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($result['items'] as $app): ?>
                            <?php
                            $statusClasses = [
                                'new' => 'admin-badge--new',
                                'reviewing' => 'admin-badge--contacted',
                                'shortlisted' => 'admin-badge--qualified',
                                'interview' => 'admin-badge--proposal',
                                'selected' => 'admin-badge--won',
                                'rejected' => 'admin-badge--lost',
                                'hold' => 'admin-badge--neutral',
                            ];
                            $badgeClass = $statusClasses[$app['status']] ?? 'admin-badge--neutral';
                            $docsCount = 0;
                            foreach (['resume_path', 'photo_path', 'aadhaar_path', 'qualification_cert_path', 'experience_cert_path', 'passport_copy_path', 'skill_cert_path', 'driving_license_path', 'other_docs_path'] as $dk) {
                                if (!empty($app[$dk])) $docsCount++;
                            }
                            ?>
                            <tr>
                                <td>
                                    <div class="admin-table__contact">
                                        <span class="admin-table__avatar"><?= kam_h(kam_initials($app['full_name'])) ?></span>
                                        <span>
                                            <strong><a href="application-detail.php?id=<?= (int) $app['id'] ?>"><?= kam_h($app['full_name']) ?></a></strong>
                                            <br/>
                                            <small style="color:#0284c7;font-weight:600;"><?= kam_h($app['reg_no'] ?? '') ?></small> · 
                                            <small style="color:#64748b;"><?= kam_h($app['mobile_no']) ?></small>
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <?= kam_h($app['job_title'] ?: 'General Application') ?>
                                    <br/>
                                    <small style="color:#64748b;"><?= kam_h($app['city_district'] ? $app['city_district'] . ', ' . $app['state'] : $app['nationality']) ?></small>
                                </td>
                                <td>
                                    <strong><?= kam_h($app['qualification_level']) ?></strong>
                                    <?php if (!empty($app['trade_branch'])): ?>
                                        <br/><small style="color:#475569;"><?= kam_h($app['trade_branch']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($app['employment_status'] === 'fresher'): ?>
                                        <span class="admin-badge admin-badge--neutral">Fresher</span>
                                    <?php else: ?>
                                        <strong><?= (int) $app['experience_years'] ?>y <?= (int) $app['experience_months'] ?>m</strong>
                                        <?php if ($app['gcc_experience'] === 'Yes'): ?>
                                            <span class="admin-badge admin-badge--qualified" style="font-size:10px;padding:2px 6px;">GCC Exp</span>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($app['resume_path']): ?>
                                        <a href="../<?= kam_h($app['resume_path']) ?>" target="_blank" class="admin-btn admin-btn--ghost admin-btn--sm" style="display:inline-flex;align-items:center;gap:4px;font-size:11px;padding:3px 8px;">
                                            <span class="material-symbols-outlined text-[14px]">description</span> CV (<?= $docsCount ?>)
                                        </a>
                                    <?php else: ?>
                                        <span style="color:#94a3b8;font-size:12px;"><?= $docsCount ?> file(s)</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="admin-badge <?= $badgeClass ?>">
                                        <?= kam_h(ucfirst($app['status'])) ?>
                                    </span>
                                </td>
                                <td style="text-align:right;white-space:nowrap;">
                                    <a href="application-detail.php?id=<?= (int) $app['id'] ?>" class="admin-btn admin-btn--primary admin-btn--sm">View Form</a>
                                    <form method="post" style="display:inline;" onsubmit="return confirm('Delete this candidate application permanently?');">
                                        <input type="hidden" name="csrf" value="<?= kam_h($csrf) ?>"/>
                                        <input type="hidden" name="delete_id" value="<?= (int) $app['id'] ?>"/>
                                        <button type="submit" class="admin-btn admin-btn--ghost admin-btn--sm" style="color:#ef4444;">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($result['pages'] > 1): ?>
            <div class="admin-pagination" style="padding:16px 24px;display:flex;align-items:center;justify-content:space-between;border-top:1px solid #f1f5f9;">
                <span class="text-xs text-slate-500">Page <?= $result['page'] ?> of <?= $result['pages'] ?> (<?= $result['total'] ?> applications)</span>
                <div style="display:flex;gap:6px;">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?= $page - 1 ?>&<?= http_build_query(array_filter($filters)) ?>" class="admin-btn admin-btn--ghost admin-btn--sm">Previous</a>
                    <?php endif; ?>
                    <?php if ($page < $result['pages']): ?>
                        <a href="?page=<?= $page + 1 ?>&<?= http_build_query(array_filter($filters)) ?>" class="admin-btn admin-btn--ghost admin-btn--sm">Next</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <?php
    $content = ob_get_clean();
    $pageTitle = 'Candidate Applications';
    $activeNav = 'applications';
    require __DIR__ . '/includes/layout.php';
});
