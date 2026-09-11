<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_once dirname(__DIR__) . '/includes/admin_app.php';
require_once dirname(__DIR__) . '/includes/JobRepository.php';
require_once dirname(__DIR__) . '/includes/LeadRepository.php';

kam_admin_app_boot();

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    kam_redirect('applications.php');
}

$user = Auth::user();
$app = JobRepository::applicationFind($id);

if (!$app) {
    kam_redirect('applications.php');
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Auth::verifyCsrf($_POST['csrf'] ?? null)) {
        $error = 'Invalid session token.';
    } elseif (($_POST['action'] ?? '') === 'delete') {
        JobRepository::applicationDelete($id);
        kam_redirect('applications.php?deleted=1');
    } elseif (($_POST['action'] ?? '') === 'note') {
        $note = trim((string) ($_POST['note'] ?? ''));
        if ($note !== '') {
            JobRepository::applicationAddNote($id, $user['id'], $note);
            $message = 'Recruiter note added.';
        }
    } else {
        $status = $_POST['status'] ?? $app['status'];
        $assigned = $_POST['assigned_to'] ?? null;
        $assignedTo = ($assigned === '' || $assigned === null) ? null : (int) $assigned;
        JobRepository::applicationUpdateStatus($id, (string) $status, $assignedTo);
        $message = 'Application status updated.';
        $app = JobRepository::applicationFind($id);
    }
}

$notes = JobRepository::applicationNotes($id);
$admins = LeadRepository::adminsForAssign();
$csrf = Auth::csrfToken();

kam_admin_render(function () use ($app, $notes, $admins, $message, $error, $csrf, $id): void {
    ob_start();
    ?>
    <?php if ($message): ?><div class="admin-alert admin-alert--success"><?= kam_h($message) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="admin-alert admin-alert--error"><?= kam_h($error) ?></div><?php endif; ?>

    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
        <div style="display:flex;align-items:center;gap:12px;">
            <a href="applications.php" class="admin-btn admin-btn--ghost admin-btn--sm">
                <span class="material-symbols-outlined text-[16px]">arrow_back</span> Back to Applications
            </a>
            <span class="admin-badge admin-badge--neutral">Registration No: <strong><?= kam_h($app['reg_no'] ?? 'N/A') ?></strong></span>
        </div>
        <div style="display:flex;gap:8px;">
            <button onclick="window.print()" class="admin-btn admin-btn--ghost admin-btn--sm">
                <span class="material-symbols-outlined text-[16px]">print</span> Print Form
            </button>
            <form method="post" style="display:inline;" onsubmit="return confirm('Delete this candidate registration permanently?');">
                <input type="hidden" name="csrf" value="<?= kam_h($csrf) ?>"/>
                <input type="hidden" name="action" value="delete"/>
                <button type="submit" class="admin-btn admin-btn--ghost admin-btn--sm" style="color:#ef4444;">Delete</button>
            </form>
        </div>
    </div>

    <div class="admin-grid-12" style="display:grid;grid-template-columns:repeat(12,1fr);gap:24px;">
        
        <!-- Left 8 Columns: Full Registration Form Details -->
        <div style="grid-column:span 8;" class="space-y-6">
            
            <!-- Hero Candidate Banner -->
            <div class="admin-card" style="padding:24px;border-left:4px solid #0284c7;">
                <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:16px;">
                    <div style="display:flex;gap:16px;align-items:center;">
                        <?php if (!empty($app['photo_path'])): ?>
                            <img src="../<?= kam_h($app['photo_path']) ?>" alt="Candidate Photo" style="width:72px;height:72px;border-radius:12px;object-fit:cover;border:2px solid #e2e8f0;"/>
                        <?php else: ?>
                            <div style="width:72px;height:72px;border-radius:12px;background:#e0f2fe;color:#0284c7;font-size:24px;font-weight:700;display:flex;align-items:center;justify-content:center;">
                                <?= kam_h(kam_initials($app['full_name'])) ?>
                            </div>
                        <?php endif; ?>
                        <div>
                            <h1 style="font-size:22px;font-weight:800;color:#0f172a;margin:0 0 4px 0;"><?= kam_h($app['full_name']) ?></h1>
                            <p style="color:#475569;font-size:14px;margin:0 0 6px 0;">
                                Applied for: <strong><?= kam_h($app['job_title'] ?: 'General Candidate Database') ?></strong>
                            </p>
                            <div style="display:flex;gap:8px;font-size:12px;color:#64748b;flex-wrap:wrap;">
                                <span><span class="material-symbols-outlined text-[14px]">mail</span> <?= kam_h($app['email']) ?></span>
                                <span>•</span>
                                <span><span class="material-symbols-outlined text-[14px]">phone</span> <?= kam_h($app['mobile_no']) ?></span>
                                <?php if ($app['whatsapp_no']): ?>
                                    <span>•</span>
                                    <span><span class="material-symbols-outlined text-[14px]">chat</span> WA: <?= kam_h($app['whatsapp_no']) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div>
                        <span class="admin-badge admin-badge--qualified" style="font-size:13px;padding:6px 14px;">
                            Status: <?= kam_h(ucfirst($app['status'])) ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- 1. Applicant Information -->
            <div class="admin-card" style="padding:24px;">
                <h3 style="font-size:15px;font-weight:700;color:#0f172a;border-bottom:1px solid #f1f5f9;padding-bottom:10px;margin-bottom:16px;">
                    1. Applicant Personal Information
                </h3>
                <div class="admin-grid-3" style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;font-size:13px;">
                    <div>
                        <label style="color:#64748b;display:block;font-size:11px;text-transform:uppercase;">Date of Birth &amp; Age</label>
                        <strong><?= kam_h($app['dob'] ?: 'Not provided') ?> <?= $app['age'] ? "({$app['age']} yrs)" : '' ?></strong>
                    </div>
                    <div>
                        <label style="color:#64748b;display:block;font-size:11px;text-transform:uppercase;">Gender</label>
                        <strong><?= kam_h($app['gender'] ?: 'Not specified') ?></strong>
                    </div>
                    <div>
                        <label style="color:#64748b;display:block;font-size:11px;text-transform:uppercase;">Nationality</label>
                        <strong><?= kam_h($app['nationality'] ?: 'Indian') ?></strong>
                    </div>
                    <div>
                        <label style="color:#64748b;display:block;font-size:11px;text-transform:uppercase;">Aadhaar / ID No.</label>
                        <strong><?= kam_h($app['aadhaar_no'] ?: 'N/A') ?></strong>
                    </div>
                    <div>
                        <label style="color:#64748b;display:block;font-size:11px;text-transform:uppercase;">City / District &amp; State</label>
                        <strong><?= kam_h($app['city_district'] ?: '-') ?>, <?= kam_h($app['state'] ?: '-') ?></strong>
                    </div>
                    <div>
                        <label style="color:#64748b;display:block;font-size:11px;text-transform:uppercase;">PIN Code</label>
                        <strong><?= kam_h($app['pin_code'] ?: 'N/A') ?></strong>
                    </div>
                    <div style="grid-column:span 3;">
                        <label style="color:#64748b;display:block;font-size:11px;text-transform:uppercase;">Current Address</label>
                        <p style="margin:4px 0 0 0;color:#1e293b;"><?= kam_h($app['current_address'] ?: 'Not provided') ?></p>
                    </div>
                </div>
            </div>

            <!-- 2. Educational / Technical Qualification -->
            <div class="admin-card" style="padding:24px;">
                <h3 style="font-size:15px;font-weight:700;color:#0f172a;border-bottom:1px solid #f1f5f9;padding-bottom:10px;margin-bottom:16px;">
                    2. Educational / Technical Qualification
                </h3>
                <div class="admin-grid-3" style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;font-size:13px;">
                    <div>
                        <label style="color:#64748b;display:block;font-size:11px;text-transform:uppercase;">Qualification Level</label>
                        <strong style="color:#0284c7;font-size:14px;"><?= kam_h($app['qualification_level']) ?></strong>
                    </div>
                    <div>
                        <label style="color:#64748b;display:block;font-size:11px;text-transform:uppercase;">Trade / Branch</label>
                        <strong><?= kam_h($app['trade_branch'] ?: 'General') ?></strong>
                    </div>
                    <div>
                        <label style="color:#64748b;display:block;font-size:11px;text-transform:uppercase;">Institute / College</label>
                        <strong><?= kam_h($app['institute_college'] ?: 'Not provided') ?></strong>
                    </div>
                    <div>
                        <label style="color:#64748b;display:block;font-size:11px;text-transform:uppercase;">Year Passed</label>
                        <strong><?= kam_h($app['year_passed'] ?: '-') ?></strong>
                    </div>
                    <div>
                        <label style="color:#64748b;display:block;font-size:11px;text-transform:uppercase;">Percentage / CGPA</label>
                        <strong><?= kam_h($app['percentage_cgpa'] ?: '-') ?></strong>
                    </div>
                    <div style="grid-column:span 3;">
                        <label style="color:#64748b;display:block;font-size:11px;text-transform:uppercase;">Additional Certifications / Licences</label>
                        <p style="margin:4px 0 0 0;color:#1e293b;"><?= kam_h($app['additional_certifications'] ?: 'None specified') ?></p>
                    </div>
                </div>
            </div>

            <!-- 3. Employment / Experience Details -->
            <div class="admin-card" style="padding:24px;">
                <h3 style="font-size:15px;font-weight:700;color:#0f172a;border-bottom:1px solid #f1f5f9;padding-bottom:10px;margin-bottom:16px;">
                    3. Employment &amp; Experience Details
                </h3>
                <div class="admin-grid-3" style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;font-size:13px;">
                    <div>
                        <label style="color:#64748b;display:block;font-size:11px;text-transform:uppercase;">Status</label>
                        <strong><?= kam_h(ucfirst($app['employment_status'])) ?></strong>
                    </div>
                    <div>
                        <label style="color:#64748b;display:block;font-size:11px;text-transform:uppercase;">Total Experience</label>
                        <strong><?= (int) $app['experience_years'] ?> Years, <?= (int) $app['experience_months'] ?> Months</strong>
                    </div>
                    <div>
                        <label style="color:#64748b;display:block;font-size:11px;text-transform:uppercase;">Notice Period</label>
                        <strong><?= kam_h($app['notice_period'] ?: 'Immediate') ?></strong>
                    </div>
                    <div>
                        <label style="color:#64748b;display:block;font-size:11px;text-transform:uppercase;">Current / Last Designation</label>
                        <strong><?= kam_h($app['current_designation'] ?: '-') ?></strong>
                    </div>
                    <div>
                        <label style="color:#64748b;display:block;font-size:11px;text-transform:uppercase;">Current / Last Company</label>
                        <strong><?= kam_h($app['current_company'] ?: '-') ?></strong>
                    </div>
                    <div>
                        <label style="color:#64748b;display:block;font-size:11px;text-transform:uppercase;">Current Location</label>
                        <strong><?= kam_h($app['current_location'] ?: '-') ?></strong>
                    </div>
                    <div>
                        <label style="color:#64748b;display:block;font-size:11px;text-transform:uppercase;">Current Salary</label>
                        <strong><?= kam_h($app['current_salary'] ?: '-') ?></strong>
                    </div>
                    <div>
                        <label style="color:#64748b;display:block;font-size:11px;text-transform:uppercase;">Expected Salary</label>
                        <strong><?= kam_h($app['expected_salary'] ?: '-') ?></strong>
                    </div>
                    <div style="grid-column:span 3;">
                        <label style="color:#64748b;display:block;font-size:11px;text-transform:uppercase;">Primary &amp; Secondary Skills</label>
                        <p style="margin:4px 0 0 0;color:#1e293b;">
                            <strong>Primary:</strong> <?= kam_h($app['primary_skills'] ?: 'N/A') ?><br/>
                            <?php if ($app['secondary_skills']): ?>
                                <strong>Secondary:</strong> <?= kam_h($app['secondary_skills']) ?>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- 4. Passport / Overseas Employment Details -->
            <div class="admin-card" style="padding:24px;">
                <h3 style="font-size:15px;font-weight:700;color:#0f172a;border-bottom:1px solid #f1f5f9;padding-bottom:10px;margin-bottom:16px;">
                    4. Passport &amp; Overseas Employment Details
                </h3>
                <div class="admin-grid-3" style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;font-size:13px;">
                    <div>
                        <label style="color:#64748b;display:block;font-size:11px;text-transform:uppercase;">Passport No.</label>
                        <strong><?= kam_h($app['passport_no'] ?: 'Not provided') ?></strong>
                    </div>
                    <div>
                        <label style="color:#64748b;display:block;font-size:11px;text-transform:uppercase;">Passport Expiry</label>
                        <strong><?= kam_h($app['passport_expiry'] ?: '-') ?></strong>
                    </div>
                    <div>
                        <label style="color:#64748b;display:block;font-size:11px;text-transform:uppercase;">ECNR / ECR</label>
                        <strong><?= kam_h($app['ecnr_status'] ?: 'NA') ?></strong>
                    </div>
                    <div>
                        <label style="color:#64748b;display:block;font-size:11px;text-transform:uppercase;">Previous GCC Experience</label>
                        <strong><?= kam_h($app['gcc_experience'] ?: 'No') ?></strong>
                    </div>
                    <div>
                        <label style="color:#64748b;display:block;font-size:11px;text-transform:uppercase;">GCC License / ID</label>
                        <strong><?= kam_h($app['gcc_license_id'] ?: 'N/A') ?></strong>
                    </div>
                    <div>
                        <label style="color:#64748b;display:block;font-size:11px;text-transform:uppercase;">Visa Status</label>
                        <strong><?= kam_h($app['visa_status'] ?: 'N/A') ?></strong>
                    </div>
                    <?php if ($app['gcc_country_employer']): ?>
                        <div style="grid-column:span 3;">
                            <label style="color:#64748b;display:block;font-size:11px;text-transform:uppercase;">Previous GCC Employer Details</label>
                            <p style="margin:4px 0 0 0;"><?= kam_h($app['gcc_country_employer']) ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- 5. Uploaded Documents (Replaces Checklist) -->
            <div class="admin-card" style="padding:24px;">
                <h3 style="font-size:15px;font-weight:700;color:#0f172a;border-bottom:1px solid #f1f5f9;padding-bottom:10px;margin-bottom:16px;">
                    5. Uploaded Candidate Documents
                </h3>
                
                <?php
                $docs = [
                    'Updated Resume / CV' => ['path' => $app['resume_path'], 'icon' => 'description', 'color' => '#0284c7'],
                    'Passport-size Photo' => ['path' => $app['photo_path'], 'icon' => 'account_circle', 'color' => '#8b5cf6'],
                    'Aadhaar / ID Proof' => ['path' => $app['aadhaar_path'], 'icon' => 'badge', 'color' => '#10b981'],
                    'Qualification Certificates' => ['path' => $app['qualification_cert_path'], 'icon' => 'school', 'color' => '#f59e0b'],
                    'Experience Certificates' => ['path' => $app['experience_cert_path'], 'icon' => 'work_history', 'color' => '#3b82f6'],
                    'Passport Copy (Front & Back)' => ['path' => $app['passport_copy_path'], 'icon' => 'flight_takeoff', 'color' => '#ec4899'],
                    'Trade / Skill Certificate' => ['path' => $app['skill_cert_path'], 'icon' => 'military_tech', 'color' => '#14b8a6'],
                    'Driving Licence' => ['path' => $app['driving_license_path'], 'icon' => 'directions_car', 'color' => '#6366f1'],
                    'Other Supporting Docs' => ['path' => $app['other_docs_path'], 'icon' => 'folder_zip', 'color' => '#64748b'],
                ];
                ?>

                <div class="admin-grid-2" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:12px;">
                    <?php foreach ($docs as $title => $d): ?>
                        <div style="border:1px solid #e2e8f0;border-radius:12px;padding:12px 16px;display:flex;align-items:center;justify-content:space-between;background:<?= $d['path'] ? '#ffffff' : '#f8fafc' ?>;">
                            <div style="display:flex;align-items:center;gap:12px;">
                                <span class="material-symbols-outlined" style="color:<?= $d['path'] ? $d['color'] : '#94a3b8' ?>;font-size:24px;"><?= $d['icon'] ?></span>
                                <div>
                                    <div style="font-size:12px;font-weight:700;color:<?= $d['path'] ? '#0f172a' : '#94a3b8' ?>;"><?= kam_h($title) ?></div>
                                    <div style="font-size:11px;color:#64748b;"><?= $d['path'] ? 'Uploaded' : 'Not uploaded' ?></div>
                                </div>
                            </div>
                            <?php if ($d['path']): ?>
                                <a href="../<?= kam_h($d['path']) ?>" target="_blank" class="admin-btn admin-btn--primary admin-btn--sm" style="font-size:11px;padding:4px 10px;">
                                    <span class="material-symbols-outlined text-[14px]">download</span> View
                                </a>
                            <?php else: ?>
                                <span style="font-size:11px;color:#cbd5e1;">—</span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- 6. Emergency Contact -->
            <div class="admin-card" style="padding:24px;">
                <h3 style="font-size:15px;font-weight:700;color:#0f172a;border-bottom:1px solid #f1f5f9;padding-bottom:10px;margin-bottom:16px;">
                    6. Emergency Contact Information
                </h3>
                <div class="admin-grid-3" style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;font-size:13px;">
                    <div>
                        <label style="color:#64748b;display:block;font-size:11px;text-transform:uppercase;">Contact Name</label>
                        <strong><?= kam_h($app['emergency_name'] ?: 'Not provided') ?></strong>
                    </div>
                    <div>
                        <label style="color:#64748b;display:block;font-size:11px;text-transform:uppercase;">Relationship</label>
                        <strong><?= kam_h($app['emergency_relationship'] ?: '-') ?></strong>
                    </div>
                    <div>
                        <label style="color:#64748b;display:block;font-size:11px;text-transform:uppercase;">Mobile No.</label>
                        <strong><?= kam_h($app['emergency_mobile'] ?: '-') ?></strong>
                    </div>
                </div>
            </div>

        </div>

        <!-- Right 4 Columns: Actions, Status Workflow, Recruiter Notes -->
        <div style="grid-column:span 4;" class="space-y-6">
            
            <!-- Status Update Panel -->
            <div class="admin-card" style="padding:20px;">
                <h3 style="font-size:15px;font-weight:700;margin-bottom:14px;color:#0f172a;">Application Status</h3>
                <form method="post" class="space-y-4">
                    <input type="hidden" name="csrf" value="<?= kam_h($csrf) ?>"/>
                    
                    <div class="admin-form-group">
                        <label for="status">Recruitment Pipeline Stage</label>
                        <select id="status" name="status" style="width:100%;padding:10px;border-radius:8px;border:1px solid #cbd5e1;font-size:13px;font-weight:600;">
                            <option value="new" <?= $app['status'] === 'new' ? 'selected' : '' ?>>New Registration</option>
                            <option value="reviewing" <?= $app['status'] === 'reviewing' ? 'selected' : '' ?>>Under Review</option>
                            <option value="shortlisted" <?= $app['status'] === 'shortlisted' ? 'selected' : '' ?>>Shortlisted for Client</option>
                            <option value="interview" <?= $app['status'] === 'interview' ? 'selected' : '' ?>>Interview / Trade Test</option>
                            <option value="selected" <?= $app['status'] === 'selected' ? 'selected' : '' ?>>Selected / Hired</option>
                            <option value="rejected" <?= $app['status'] === 'rejected' ? 'selected' : '' ?>>Rejected / Ineligible</option>
                            <option value="hold" <?= $app['status'] === 'hold' ? 'selected' : '' ?>>On Hold</option>
                        </select>
                    </div>

                    <div class="admin-form-group">
                        <label for="assigned_to">Assigned Recruiter</label>
                        <select id="assigned_to" name="assigned_to" style="width:100%;padding:10px;border-radius:8px;border:1px solid #cbd5e1;font-size:13px;">
                            <option value="">Unassigned</option>
                            <?php foreach ($admins as $adm): ?>
                                <option value="<?= (int) $adm['id'] ?>" <?= ($app['assigned_to'] == $adm['id']) ? 'selected' : '' ?>>
                                    <?= kam_h($adm['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button type="submit" class="admin-btn admin-btn--primary" style="width:100%;justify-content:center;">
                        Update Candidate Status
                    </button>
                </form>
            </div>

            <!-- Recruiter Internal Notes -->
            <div class="admin-card" style="padding:20px;">
                <h3 style="font-size:15px;font-weight:700;margin-bottom:14px;color:#0f172a;">Recruiter Notes</h3>
                
                <form method="post" style="margin-bottom:16px;">
                    <input type="hidden" name="csrf" value="<?= kam_h($csrf) ?>"/>
                    <input type="hidden" name="action" value="note"/>
                    <textarea name="note" rows="3" required placeholder="Add interview feedback, client remarks, or trade test score..." style="width:100%;padding:10px;border-radius:8px;border:1px solid #cbd5e1;font-size:12px;margin-bottom:8px;"></textarea>
                    <button type="submit" class="admin-btn admin-btn--ghost admin-btn--sm" style="width:100%;justify-content:center;">
                        Add Note
                    </button>
                </form>

                <div class="space-y-3" style="max-height:300px;overflow-y:auto;">
                    <?php if (empty($notes)): ?>
                        <p style="font-size:12px;color:#94a3b8;text-align:center;padding:12px 0;">No internal recruiter notes added yet.</p>
                    <?php else: ?>
                        <?php foreach ($notes as $n): ?>
                            <div style="background:#f8fafc;border:1px solid #edf2f7;border-radius:10px;padding:10px;font-size:12px;">
                                <p style="margin:0 0 4px 0;color:#1e293b;"><?= nl2br(kam_h($n['note'])) ?></p>
                                <div style="display:flex;justify-content:space-between;color:#94a3b8;font-size:10px;">
                                    <span><?= kam_h($n['admin_name'] ?: 'Recruiter') ?></span>
                                    <span><?= kam_h(date('M j, Y g:i A', strtotime($n['created_at']))) ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Metadata Panel -->
            <div class="admin-card" style="padding:16px;font-size:12px;color:#64748b;">
                <div style="margin-bottom:6px;"><strong>Source:</strong> <?= kam_h($app['source']) ?></div>
                <div style="margin-bottom:6px;"><strong>Registered On:</strong> <?= kam_h(date('M j, Y · g:i A', strtotime($app['created_at']))) ?></div>
                <?php if ($app['ip_address']): ?>
                    <div><strong>IP Address:</strong> <?= kam_h($app['ip_address']) ?></div>
                <?php endif; ?>
            </div>

        </div>

    </div>
    <?php
    $content = ob_get_clean();
    $pageTitle = 'Candidate: ' . $app['full_name'];
    $activeNav = 'applications';
    require __DIR__ . '/includes/layout.php';
});
