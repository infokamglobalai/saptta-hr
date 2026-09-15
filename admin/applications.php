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
    $filterQuery = http_build_query(array_filter($filters));
    ob_start();
    ?>
    <?php if ($message): ?><div class="admin-alert admin-alert--success"><?= kam_h($message) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="admin-alert admin-alert--error"><?= kam_h($error) ?></div><?php endif; ?>

    <div class="admin-card">
        <div class="admin-card__head">
            <div>
                <h2><span class="material-symbols-outlined">assignment_ind</span> Candidate Applications</h2>
                <p class="text-xs text-slate-500 mt-1">Review candidate registration forms, export data, and access uploaded documents.</p>
            </div>
            
            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                <span class="admin-badge admin-badge--qualified"><?= (int) $result['total'] ?> Total Applications</span>
                
                <!-- Export Dropdown -->
                <div class="admin-export-dropdown" style="position:relative;display:inline-block;">
                    <button type="button" class="admin-btn admin-btn--primary admin-btn--sm" id="exportDropdownBtn" style="display:inline-flex;align-items:center;gap:6px;">
                        <span class="material-symbols-outlined text-[16px]">file_download</span>
                        <span>Bulk Export</span>
                        <span class="material-symbols-outlined text-[14px]">arrow_drop_down</span>
                    </button>
                    <div id="exportDropdownMenu" style="display:none;position:absolute;right:0;top:calc(100% + 6px);background:#ffffff;border:1px solid #e2e8f0;border-radius:10px;box-shadow:0 10px 25px rgba(0,0,0,0.12);min-width:230px;z-index:100;padding:6px 0;animation:fadeIn 0.15s ease-out;">
                        <div style="padding:6px 14px;font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;">Current Filter (<?= (int)$result['total'] ?>)</div>
                        <a href="applications-export.php?format=excel&<?= $filterQuery ?>" class="admin-export-item" style="display:flex;align-items:center;gap:8px;padding:8px 14px;font-size:13px;color:#1e293b;text-decoration:none;transition:background 0.2s;">
                            <span class="material-symbols-outlined" style="color:#059669;font-size:18px;">table_view</span> Export Filtered (Excel .xls)
                        </a>
                        <a href="applications-export.php?format=csv&<?= $filterQuery ?>" class="admin-export-item" style="display:flex;align-items:center;gap:8px;padding:8px 14px;font-size:13px;color:#1e293b;text-decoration:none;transition:background 0.2s;">
                            <span class="material-symbols-outlined" style="color:#0284c7;font-size:18px;">csv</span> Export Filtered (CSV)
                        </a>
                        <div style="height:1px;background:#f1f5f9;margin:6px 0;"></div>
                        <div style="padding:6px 14px;font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;">All Database Records</div>
                        <a href="applications-export.php?format=excel&all=1" class="admin-export-item" style="display:flex;align-items:center;gap:8px;padding:8px 14px;font-size:13px;color:#1e293b;text-decoration:none;transition:background 0.2s;">
                            <span class="material-symbols-outlined" style="color:#10b981;font-size:18px;">download</span> Export All to Excel
                        </a>
                        <a href="applications-export.php?format=csv&all=1" class="admin-export-item" style="display:flex;align-items:center;gap:8px;padding:8px 14px;font-size:13px;color:#1e293b;text-decoration:none;transition:background 0.2s;">
                            <span class="material-symbols-outlined" style="color:#3b82f6;font-size:18px;">download</span> Export All to CSV
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters Form -->
        <form method="get" class="admin-filters-bar" style="display:flex;gap:12px;padding:16px 24px;border-bottom:1px solid #f1f5f9;background:#fafbfc;flex-wrap:wrap;align-items:center;">
            <input type="text" name="q" placeholder="Search candidate, email, mobile, reg no, city..." value="<?= kam_h($filters['q']) ?>" style="padding:8px 14px;border:1px solid #cbd5e1;border-radius:8px;font-size:13px;min-width:240px;"/>
            
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
            <table class="admin-table" id="applicationsTable">
                <thead>
                    <tr>
                        <th style="width:40px;text-align:center;">
                            <input type="checkbox" id="selectAllCheckbox" title="Select all on this page" style="width:16px;height:16px;cursor:pointer;accent-color:#005eb2;"/>
                        </th>
                        <th>Candidate / Reg No.</th>
                        <th>Applied Position</th>
                        <th>Qualification &amp; Trade</th>
                        <th>Experience</th>
                        <th>Uploaded Documents</th>
                        <th>Status</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($result['items'])): ?>
                        <tr>
                            <td colspan="8" class="admin-table__empty">
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
                            <tr id="row-app-<?= (int) $app['id'] ?>">
                                <td style="text-align:center;">
                                    <input type="checkbox" class="app-row-cb" value="<?= (int) $app['id'] ?>" style="width:16px;height:16px;cursor:pointer;accent-color:#005eb2;"/>
                                </td>
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
                                        <a href="../<?= kam_h($app['resume_path']) ?>" target="_blank" class="admin-btn admin-btn--ghost admin-btn--sm" style="display:inline-flex;align-items:center;gap:4px;font-size:11px;padding:3px 8px;" title="View uploaded Resume">
                                            <span class="material-symbols-outlined text-[14px]">description</span> CV (<?= $docsCount ?> docs)
                                        </a>
                                    <?php else: ?>
                                        <span style="color:#94a3b8;font-size:12px;"><?= $docsCount ?> doc(s)</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="admin-badge <?= $badgeClass ?>">
                                        <?= kam_h(ucfirst($app['status'])) ?>
                                    </span>
                                </td>
                                <td style="text-align:right;white-space:nowrap;">
                                    <a href="application-detail.php?id=<?= (int) $app['id'] ?>" class="admin-btn admin-btn--primary admin-btn--sm">View Form</a>
                                    <a href="application-print.php?id=<?= (int) $app['id'] ?>" target="_blank" class="admin-btn admin-btn--ghost admin-btn--sm" title="Print candidate application dossier" style="padding:4px 6px;">
                                        <span class="material-symbols-outlined text-[15px]">print</span>
                                    </a>
                                    <a href="applications-export.php?ids=<?= (int) $app['id'] ?>&format=excel" class="admin-btn admin-btn--ghost admin-btn--sm" title="Export this candidate to Excel" style="padding:4px 6px;">
                                        <span class="material-symbols-outlined text-[15px]">table_view</span>
                                    </a>
                                    <form method="post" style="display:inline;" onsubmit="return confirm('Delete this candidate application permanently?');">
                                        <input type="hidden" name="csrf" value="<?= kam_h($csrf) ?>"/>
                                        <input type="hidden" name="delete_id" value="<?= (int) $app['id'] ?>"/>
                                        <button type="submit" class="admin-btn admin-btn--ghost admin-btn--sm" style="color:#ef4444;" title="Delete">Delete</button>
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
                        <a href="?page=<?= $page - 1 ?>&<?= $filterQuery ?>" class="admin-btn admin-btn--ghost admin-btn--sm">Previous</a>
                    <?php endif; ?>
                    <?php if ($page < $result['pages']): ?>
                        <a href="?page=<?= $page + 1 ?>&<?= $filterQuery ?>" class="admin-btn admin-btn--ghost admin-btn--sm">Next</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Floating Bulk Action Toolbar -->
    <div id="bulkActionBar" style="display:none;position:fixed;bottom:24px;left:50%;transform:translateX(-50%);background:linear-gradient(135deg, #001f3f 0%, #003366 100%);color:#ffffff;padding:12px 24px;border-radius:50px;box-shadow:0 12px 35px rgba(0,31,63,0.35);z-index:999;align-items:center;gap:16px;border:1px solid rgba(255,255,255,0.15);animation:slideUp 0.25s ease-out;">
        <div style="display:flex;align-items:center;gap:8px;font-size:13px;font-weight:600;">
            <span class="material-symbols-outlined" style="color:#38bdf8;font-size:20px;">check_circle</span>
            <span><span id="bulkSelectedCount">0</span> selected</span>
        </div>
        <div style="height:20px;width:1px;background:rgba(255,255,255,0.2);"></div>
        <div style="display:flex;align-items:center;gap:8px;">
            <button type="button" id="bulkExportExcelBtn" class="admin-btn admin-btn--sm" style="background:#059669;color:#ffffff;border:none;box-shadow:0 2px 8px rgba(5,150,105,0.4);">
                <span class="material-symbols-outlined text-[15px]">table_view</span> Export Excel (.xls)
            </button>
            <button type="button" id="bulkExportCsvBtn" class="admin-btn admin-btn--sm" style="background:#0284c7;color:#ffffff;border:none;box-shadow:0 2px 8px rgba(2,132,199,0.4);">
                <span class="material-symbols-outlined text-[15px]">download</span> Export CSV
            </button>
            <button type="button" id="bulkClearSelectionBtn" class="admin-btn admin-btn--sm" style="background:rgba(255,255,255,0.15);color:#ffffff;border:none;">
                Clear
            </button>
        </div>
    </div>

    <style>
        .admin-export-item:hover { background: #f8fafc; }
        @keyframes slideUp {
            from { opacity: 0; transform: translate(-50%, 20px); }
            to { opacity: 1; transform: translate(-50%, 0); }
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(4px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Export Dropdown toggle
        const exportBtn = document.getElementById('exportDropdownBtn');
        const exportMenu = document.getElementById('exportDropdownMenu');
        
        if (exportBtn && exportMenu) {
            exportBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                exportMenu.style.display = exportMenu.style.display === 'none' ? 'block' : 'none';
            });
            document.addEventListener('click', function(e) {
                if (!exportMenu.contains(e.target) && e.target !== exportBtn) {
                    exportMenu.style.display = 'none';
                }
            });
        }

        // Bulk Selection system
        const selectAllCb = document.getElementById('selectAllCheckbox');
        const rowCheckboxes = document.querySelectorAll('.app-row-cb');
        const bulkBar = document.getElementById('bulkActionBar');
        const countSpan = document.getElementById('bulkSelectedCount');
        const exportExcelBtn = document.getElementById('bulkExportExcelBtn');
        const exportCsvBtn = document.getElementById('bulkExportCsvBtn');
        const clearBtn = document.getElementById('bulkClearSelectionBtn');

        function updateBulkBar() {
            const selected = Array.from(rowCheckboxes).filter(cb => cb.checked);
            const count = selected.length;
            
            if (count > 0) {
                countSpan.textContent = count;
                bulkBar.style.display = 'flex';
            } else {
                bulkBar.style.display = 'none';
            }

            if (selectAllCb) {
                selectAllCb.checked = (rowCheckboxes.length > 0 && selected.length === rowCheckboxes.length);
                selectAllCb.indeterminate = (count > 0 && count < rowCheckboxes.length);
            }
        }

        if (selectAllCb) {
            selectAllCb.addEventListener('change', function() {
                rowCheckboxes.forEach(cb => {
                    cb.checked = selectAllCb.checked;
                    const tr = cb.closest('tr');
                    if (tr) {
                        tr.style.backgroundColor = cb.checked ? 'rgba(59, 158, 255, 0.08)' : '';
                    }
                });
                updateBulkBar();
            });
        }

        rowCheckboxes.forEach(cb => {
            cb.addEventListener('change', function() {
                const tr = cb.closest('tr');
                if (tr) {
                    tr.style.backgroundColor = cb.checked ? 'rgba(59, 158, 255, 0.08)' : '';
                }
                updateBulkBar();
            });
        });

        if (clearBtn) {
            clearBtn.addEventListener('click', function() {
                rowCheckboxes.forEach(cb => {
                    cb.checked = false;
                    const tr = cb.closest('tr');
                    if (tr) tr.style.backgroundColor = '';
                });
                if (selectAllCb) {
                    selectAllCb.checked = false;
                    selectAllCb.indeterminate = false;
                }
                updateBulkBar();
            });
        }

        function triggerBulkExport(format) {
            const selectedIds = Array.from(rowCheckboxes)
                .filter(cb => cb.checked)
                .map(cb => cb.value);
            
            if (selectedIds.length === 0) {
                alert('Please select at least one application to export.');
                return;
            }

            const url = 'applications-export.php?format=' + encodeURIComponent(format) + '&ids=' + encodeURIComponent(selectedIds.join(','));
            window.location.href = url;
        }

        if (exportExcelBtn) {
            exportExcelBtn.addEventListener('click', function() {
                triggerBulkExport('excel');
            });
        }

        if (exportCsvBtn) {
            exportCsvBtn.addEventListener('click', function() {
                triggerBulkExport('csv');
            });
        }
    });
    </script>
    <?php
    $content = ob_get_clean();
    $pageTitle = 'Candidate Applications';
    $activeNav = 'applications';
    require __DIR__ . '/includes/layout.php';
});

