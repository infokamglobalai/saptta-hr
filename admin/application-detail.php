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

    <!-- Top Action Bar (Hidden in Print) -->
    <div class="admin-no-print" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
        <div style="display:flex;align-items:center;gap:12px;">
            <a href="applications.php" class="admin-btn admin-btn--ghost admin-btn--sm">
                <span class="material-symbols-outlined text-[16px]">arrow_back</span> Back to Applications
            </a>
            <span class="admin-badge admin-badge--neutral">Registration No: <strong><?= kam_h($app['reg_no'] ?? 'N/A') ?></strong></span>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
            <a href="applications-export.php?ids=<?= (int)$app['id'] ?>&format=excel" class="admin-btn admin-btn--ghost admin-btn--sm" title="Export this candidate application with document links to Excel">
                <span class="material-symbols-outlined text-[16px]" style="color:#059669;">table_view</span> Export Excel
            </a>
            <a href="applications-export.php?ids=<?= (int)$app['id'] ?>&format=csv" class="admin-btn admin-btn--ghost admin-btn--sm" title="Export this candidate application to CSV">
                <span class="material-symbols-outlined text-[16px]" style="color:#0284c7;">download</span> Export CSV
            </a>
            <a href="application-print.php?id=<?= (int)$app['id'] ?>" target="_blank" class="admin-btn admin-btn--primary admin-btn--sm" title="Open complete printable application dossier">
                <span class="material-symbols-outlined text-[16px]">print</span> Print Application
            </a>
            <form method="post" style="display:inline;" onsubmit="return confirm('Delete this candidate registration permanently?');">
                <input type="hidden" name="csrf" value="<?= kam_h($csrf) ?>"/>
                <input type="hidden" name="action" value="delete"/>
                <button type="submit" class="admin-btn admin-btn--ghost admin-btn--sm" style="color:#ef4444;">Delete</button>
            </form>
        </div>
    </div>

    <!-- Official Print-Only Header (Appears only on printed document) -->
    <div class="admin-print-header" style="display:none;margin-bottom:18px;">
        <div style="display:flex;justify-content:space-between;align-items:center;border-bottom:2.5px solid #001f3f;padding-bottom:12px;">
            <div>
                <div style="font-size:20pt;font-weight:900;color:#001f3f;letter-spacing:-0.02em;line-height:1.1;">KAM GLOBAL HR SERVICES</div>
                <div style="font-size:9.5pt;font-weight:600;color:#005eb2;margin-top:2px;">International Recruitment & Manpower Solutions</div>
                <div style="font-size:8pt;color:#64748b;margin-top:1px;">Website: www.kamglobalhr.com | Candidate Application Dossier</div>
            </div>
            <div style="text-align:right;">
                <div style="border:1.5px solid #001f3f;border-radius:4px;padding:4px 10px;display:inline-block;background:#f8fafc;">
                    <div style="font-size:8pt;font-weight:700;color:#64748b;text-transform:uppercase;">Registration Number</div>
                    <div style="font-size:12pt;font-weight:800;color:#001f3f;"><?= kam_h($app['reg_no'] ?? 'N/A') ?></div>
                </div>
                <div style="font-size:8pt;color:#64748b;margin-top:4px;">Date: <?= date('d M Y, h:i A') ?></div>
            </div>
        </div>
    </div>

    <div class="admin-grid-12" style="display:grid;grid-template-columns:repeat(12,1fr);gap:24px;">
        
        <!-- Main Registration Form Details -->
        <div class="admin-print-main-col" style="grid-column:span 8;" class="space-y-6">
            
            <!-- Hero Candidate Banner -->
            <div class="admin-card print-avoid-break" style="padding:20px;border-left:4px solid #0284c7;">
                <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:16px;">
                    <div style="display:flex;gap:16px;align-items:center;">
                        <?php if (!empty($app['photo_path'])): ?>
                            <img src="../<?= kam_h($app['photo_path']) ?>" alt="Candidate Photo" style="width:76px;height:76px;border-radius:10px;object-fit:cover;border:2px solid #cbd5e1;flex-shrink:0;"/>
                        <?php else: ?>
                            <div style="width:76px;height:76px;border-radius:10px;background:#e0f2fe;color:#0284c7;font-size:24px;font-weight:800;display:flex;align-items:center;justify-content:center;border:1px solid #bfdbfe;flex-shrink:0;">
                                <?= kam_h(kam_initials($app['full_name'])) ?>
                            </div>
                        <?php endif; ?>
                        <div>
                            <h1 style="font-size:22px;font-weight:800;color:#0f172a;margin:0 0 4px 0;"><?= kam_h($app['full_name']) ?></h1>
                            <p style="color:#475569;font-size:13.5px;margin:0 0 6px 0;">
                                Applied Position: <strong style="color:#001f3f;"><?= kam_h($app['job_title'] ?: 'General Candidate Database') ?></strong>
                            </p>
                            <div style="display:flex;gap:10px;font-size:12px;color:#64748b;flex-wrap:wrap;">
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
                        <span class="admin-badge admin-badge--qualified" style="font-size:12px;padding:5px 12px;border:1px solid rgba(0,0,0,0.1);">
                            Status: <?= kam_h(ucfirst($app['status'])) ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- 1. Applicant Information -->
            <div class="admin-card print-avoid-break" style="padding:20px;">
                <h3 style="font-size:14px;font-weight:700;color:#0f172a;border-bottom:1px solid #f1f5f9;padding-bottom:8px;margin-bottom:14px;">
                    1. Applicant Personal Information
                </h3>
                <div class="admin-grid-3" style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;font-size:13px;">
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
                        <label style="color:#64748b;display:block;font-size:11px;text-transform:uppercase;">Aadhaar / National ID</label>
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
                        <label style="color:#64748b;display:block;font-size:11px;text-transform:uppercase;">Current Residential Address</label>
                        <p style="margin:3px 0 0 0;color:#1e293b;font-weight:500;"><?= kam_h($app['current_address'] ?: 'Not provided') ?></p>
                    </div>
                </div>
            </div>

            <!-- 2. Educational / Technical Qualification -->
            <div class="admin-card print-avoid-break" style="padding:20px;">
                <h3 style="font-size:14px;font-weight:700;color:#0f172a;border-bottom:1px solid #f1f5f9;padding-bottom:8px;margin-bottom:14px;">
                    2. Educational &amp; Technical Qualification
                </h3>
                <div class="admin-grid-3" style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;font-size:13px;">
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
                        <p style="margin:3px 0 0 0;color:#1e293b;font-weight:500;"><?= kam_h($app['additional_certifications'] ?: 'None specified') ?></p>
                    </div>
                </div>
            </div>

            <!-- 3. Employment / Experience Details -->
            <div class="admin-card print-avoid-break" style="padding:20px;">
                <h3 style="font-size:14px;font-weight:700;color:#0f172a;border-bottom:1px solid #f1f5f9;padding-bottom:8px;margin-bottom:14px;">
                    3. Employment &amp; Experience Details
                </h3>
                <div class="admin-grid-3" style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;font-size:13px;">
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
                        <p style="margin:3px 0 0 0;color:#1e293b;">
                            <strong>Primary:</strong> <?= kam_h($app['primary_skills'] ?: 'N/A') ?><br/>
                            <?php if ($app['secondary_skills']): ?>
                                <strong>Secondary:</strong> <?= kam_h($app['secondary_skills']) ?>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- 4. Passport / Overseas Employment Details -->
            <div class="admin-card print-avoid-break" style="padding:20px;">
                <h3 style="font-size:14px;font-weight:700;color:#0f172a;border-bottom:1px solid #f1f5f9;padding-bottom:8px;margin-bottom:14px;">
                    4. Passport &amp; Overseas Employment Details
                </h3>
                <div class="admin-grid-3" style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;font-size:13px;">
                    <div>
                        <label style="color:#64748b;display:block;font-size:11px;text-transform:uppercase;">Passport No.</label>
                        <strong><?= kam_h($app['passport_no'] ?: 'Not provided') ?></strong>
                    </div>
                    <div>
                        <label style="color:#64748b;display:block;font-size:11px;text-transform:uppercase;">Passport Expiry</label>
                        <strong><?= kam_h($app['passport_expiry'] ?: '-') ?></strong>
                    </div>
                    <div>
                        <label style="color:#64748b;display:block;font-size:11px;text-transform:uppercase;">ECNR / ECR Status</label>
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
                            <p style="margin:3px 0 0 0;"><?= kam_h($app['gcc_country_employer']) ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- 5. Uploaded Documents -->
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
            <div class="admin-card print-avoid-break" style="padding:20px;">
                <h3 style="font-size:14px;font-weight:700;color:#0f172a;border-bottom:1px solid #f1f5f9;padding-bottom:8px;margin-bottom:14px;">
                    5. Uploaded Candidate Documents
                </h3>
                
                <!-- Screen Interactive View -->
                <div class="admin-no-print admin-grid-2" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:12px;">
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

                <!-- Print Checklist Table View -->
                <div class="admin-print-only" style="display:none;">
                    <table style="width:100%;border-collapse:collapse;font-size:9pt;">
                        <thead>
                            <tr style="background:#f1f5f9;border-bottom:1.5px solid #cbd5e1;">
                                <th style="padding:6px 8px;text-align:left;width:30px;">#</th>
                                <th style="padding:6px 8px;text-align:left;">Document Name</th>
                                <th style="padding:6px 8px;text-align:center;width:110px;">Status</th>
                                <th style="padding:6px 8px;text-align:left;">File Record</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $docIdx = 1; foreach ($docs as $title => $d): ?>
                                <tr style="border-bottom:1px solid #e2e8f0;">
                                    <td style="padding:5px 8px;color:#64748b;"><?= $docIdx++ ?></td>
                                    <td style="padding:5px 8px;font-weight:600;"><?= kam_h($title) ?></td>
                                    <td style="padding:5px 8px;text-align:center;">
                                        <?php if ($d['path']): ?>
                                            <span style="color:#059669;font-weight:700;">[✓ Attached]</span>
                                        <?php else: ?>
                                            <span style="color:#94a3b8;">[— None]</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding:5px 8px;font-size:8pt;color:#475569;">
                                        <?= $d['path'] ? kam_h(basename($d['path'])) : 'Not submitted' ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 6. Emergency Contact -->
            <div class="admin-card print-avoid-break" style="padding:20px;">
                <h3 style="font-size:14px;font-weight:700;color:#0f172a;border-bottom:1px solid #f1f5f9;padding-bottom:8px;margin-bottom:14px;">
                    6. Emergency Contact Information
                </h3>
                <div class="admin-grid-3" style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;font-size:13px;">
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

            <!-- Print-Only Recruiter Notes Section -->
            <?php if (!empty($notes)): ?>
                <div class="admin-print-only print-avoid-break admin-card" style="display:none;padding:20px;">
                    <h3 style="font-size:14px;font-weight:700;color:#0f172a;border-bottom:1px solid #f1f5f9;padding-bottom:8px;margin-bottom:14px;">
                        7. Recruiter Assessment &amp; Interview Notes
                    </h3>
                    <div class="space-y-2">
                        <?php foreach ($notes as $n): ?>
                            <div style="border-left:3px solid #005eb2;padding-left:10px;margin-bottom:8px;font-size:9.5pt;">
                                <div style="color:#1e293b;"><?= nl2br(kam_h($n['note'])) ?></div>
                                <div style="font-size:8pt;color:#64748b;margin-top:2px;">
                                    By: <?= kam_h($n['admin_name'] ?: 'Recruiter') ?> | <?= kam_h(date('d M Y, h:i A', strtotime($n['created_at']))) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Official Print-Only Footer -->
            <div class="admin-print-footer print-avoid-break" style="display:none;margin-top:24px;border-top:1.5px dashed #94a3b8;padding-top:16px;">
                <div style="font-size:8pt;color:#475569;line-height:1.4;margin-bottom:30px;">
                    <strong>Candidate Declaration:</strong> I hereby declare that all information furnished in this registration form and documents attached are true, complete, and correct to the best of my knowledge. Any false statements will render this application invalid.
                </div>
                <div style="display:flex;justify-content:space-between;align-items:flex-end;margin-top:35px;">
                    <div style="text-align:center;min-width:180px;">
                        <div style="border-top:1.5px solid #0f172a;padding-top:6px;font-size:9pt;font-weight:700;color:#0f172a;">Candidate Signature</div>
                        <div style="font-size:8pt;color:#64748b;margin-top:2px;">Date: _______________</div>
                    </div>
                    <div style="text-align:center;min-width:180px;">
                        <div style="border-top:1.5px solid #0f172a;padding-top:6px;font-size:9pt;font-weight:700;color:#0f172a;">Verified By / HR Recruiter</div>
                        <div style="font-size:8pt;color:#64748b;margin-top:2px;">KAM Global HR Services</div>
                    </div>
                    <div style="text-align:center;min-width:130px;">
                        <div style="border:1.5px dashed #94a3b8;height:55px;display:flex;align-items:center;justify-content:center;font-size:8pt;color:#94a3b8;margin-bottom:4px;">
                            Official Seal
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Right 4 Columns: Actions, Status Workflow, Recruiter Notes (Screen Only) -->
        <div class="admin-no-print" style="grid-column:span 4;" class="space-y-6">
            
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

    <!-- Application Print Dedicated Styles -->
    <style>
    @media print {
        .admin-print-header,
        .admin-print-footer,
        .admin-print-only {
            display: block !important;
        }
        .admin-no-print {
            display: none !important;
        }
        .admin-grid-12 {
            display: block !important;
            width: 100% !important;
        }
        .admin-print-main-col {
            grid-column: span 12 !important;
            width: 100% !important;
            display: block !important;
        }
        .admin-card {
            border: 1px solid #cbd5e1 !important;
            box-shadow: none !important;
            margin-bottom: 12px !important;
            page-break-inside: avoid !important;
            break-inside: avoid !important;
            padding: 14px 18px !important;
            background: #ffffff !important;
        }
        .admin-card h3 {
            color: #001f3f !important;
            border-bottom: 1.5px solid #005eb2 !important;
            padding-bottom: 4px !important;
            margin-bottom: 10px !important;
            font-size: 11pt !important;
        }
        .admin-grid-3 {
            display: grid !important;
            grid-template-columns: repeat(3, 1fr) !important;
            gap: 10px !important;
        }
        .admin-grid-2 {
            display: grid !important;
            grid-template-columns: repeat(2, 1fr) !important;
            gap: 10px !important;
        }
        .print-avoid-break {
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }
    }
    </style>
    <?php
    $content = ob_get_clean();
    $pageTitle = 'Candidate: ' . $app['full_name'];
    $activeNav = 'applications';
    require __DIR__ . '/includes/layout.php';
});
