<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_once dirname(__DIR__) . '/includes/admin_app.php';
require_once dirname(__DIR__) . '/includes/JobRepository.php';

kam_admin_app_boot();

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    kam_redirect('applications.php');
}

$app = JobRepository::applicationFind($id);
if (!$app) {
    kam_redirect('applications.php');
}

$notes = JobRepository::applicationNotes($id);

$docs = [
    'Updated Resume / CV' => ['path' => $app['resume_path'], 'key' => 'Resume'],
    'Passport-size Photo' => ['path' => $app['photo_path'], 'key' => 'Photo'],
    'Aadhaar / National ID Proof' => ['path' => $app['aadhaar_path'], 'key' => 'Aadhaar / ID'],
    'Qualification Certificates' => ['path' => $app['qualification_cert_path'], 'key' => 'Education Cert'],
    'Experience Certificates' => ['path' => $app['experience_cert_path'], 'key' => 'Experience Cert'],
    'Passport Copy (Front & Back)' => ['path' => $app['passport_copy_path'], 'key' => 'Passport Copy'],
    'Trade / Skill Certificate' => ['path' => $app['skill_cert_path'], 'key' => 'Trade / Skill'],
    'Driving Licence' => ['path' => $app['driving_license_path'], 'key' => 'Driving Licence'],
    'Other Supporting Docs' => ['path' => $app['other_docs_path'], 'key' => 'Other Documents'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>Application Dossier — <?= kam_h($app['full_name']) ?> (<?= kam_h($app['reg_no'] ?? '') ?>)</title>
    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            background: #f1f5f9;
            color: #0f172a;
            font-size: 10pt;
            line-height: 1.45;
            padding: 24px;
        }

        .print-container {
            max-width: 850px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            padding: 36px 40px;
        }

        .top-action-bar {
            max-width: 850px;
            margin: 0 auto 16px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            border: 1px solid transparent;
            font-family: inherit;
        }

        .btn-primary {
            background: #005eb2;
            color: #ffffff;
        }

        .btn-primary:hover {
            background: #004a8f;
        }

        .btn-ghost {
            background: #ffffff;
            border-color: #cbd5e1;
            color: #334155;
        }

        .btn-ghost:hover {
            background: #f8fafc;
        }

        /* Dossier Header */
        .dossier-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2.5px solid #001f3f;
            padding-bottom: 16px;
            margin-bottom: 20px;
        }

        .brand-name {
            font-size: 20pt;
            font-weight: 900;
            color: #001f3f;
            letter-spacing: -0.02em;
            line-height: 1.1;
        }

        .brand-sub {
            font-size: 9.5pt;
            font-weight: 700;
            color: #005eb2;
            margin-top: 3px;
        }

        .brand-meta {
            font-size: 8pt;
            color: #64748b;
            margin-top: 2px;
        }

        .reg-box {
            text-align: right;
        }

        .reg-badge {
            border: 1.5px solid #001f3f;
            border-radius: 6px;
            padding: 6px 12px;
            display: inline-block;
            background: #f8fafc;
            text-align: center;
        }

        .reg-badge small {
            display: block;
            font-size: 7.5pt;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .reg-badge strong {
            font-size: 12pt;
            font-weight: 800;
            color: #001f3f;
        }

        /* Hero Candidate Card */
        .candidate-hero {
            border: 1px solid #cbd5e1;
            border-left: 5px solid #005eb2;
            border-radius: 8px;
            padding: 16px 20px;
            margin-bottom: 18px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #f8fafc;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .hero-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .candidate-photo {
            width: 78px;
            height: 78px;
            border-radius: 8px;
            object-fit: cover;
            border: 2px solid #cbd5e1;
            flex-shrink: 0;
            background: #ffffff;
        }

        .candidate-avatar {
            width: 78px;
            height: 78px;
            border-radius: 8px;
            background: #e0f2fe;
            color: #005eb2;
            font-size: 22pt;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #bfdbfe;
            flex-shrink: 0;
        }

        .candidate-title {
            font-size: 16pt;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.2;
            margin-bottom: 4px;
        }

        .candidate-post {
            font-size: 10.5pt;
            color: #334155;
            margin-bottom: 4px;
        }

        .candidate-post strong {
            color: #001f3f;
        }

        .candidate-contacts {
            font-size: 8.5pt;
            color: #64748b;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 999px;
            font-size: 8.5pt;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            background: #dbeafe;
            color: #1e40af;
            border: 1px solid #bfdbfe;
        }

        /* Section Cards */
        .section-card {
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 14px 18px;
            margin-bottom: 14px;
            background: #ffffff;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .section-title {
            font-size: 10.5pt;
            font-weight: 800;
            color: #001f3f;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            border-bottom: 1.5px solid #005eb2;
            padding-bottom: 4px;
            margin-bottom: 12px;
        }

        .grid-3 {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px 14px;
        }

        .grid-2 {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px 14px;
        }

        .field {
            font-size: 9pt;
        }

        .field-label {
            display: block;
            font-size: 7.5pt;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            margin-bottom: 2px;
        }

        .field-value {
            font-size: 9pt;
            font-weight: 600;
            color: #0f172a;
        }

        .col-span-3 {
            grid-column: span 3;
        }

        .col-span-2 {
            grid-column: span 2;
        }

        /* Documents Table */
        .doc-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5pt;
            margin-top: 4px;
        }

        .doc-table th {
            background: #f1f5f9;
            color: #334155;
            font-weight: 700;
            text-align: left;
            padding: 6px 10px;
            border: 1px solid #cbd5e1;
            font-size: 8pt;
            text-transform: uppercase;
        }

        .doc-table td {
            padding: 5px 10px;
            border: 1px solid #e2e8f0;
            vertical-align: middle;
        }

        .doc-table tr:nth-child(even) td {
            background: #f8fafc;
        }

        .doc-attached {
            color: #059669;
            font-weight: 700;
        }

        .doc-none {
            color: #94a3b8;
        }

        /* Declaration & Footer */
        .dossier-footer {
            margin-top: 24px;
            border-top: 1.5px dashed #94a3b8;
            padding-top: 14px;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .declaration-text {
            font-size: 8pt;
            color: #475569;
            line-height: 1.45;
            margin-bottom: 30px;
        }

        .sig-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-top: 36px;
        }

        .sig-block {
            text-align: center;
            min-width: 190px;
        }

        .sig-line {
            border-top: 1.5px solid #0f172a;
            padding-top: 6px;
            font-size: 8.5pt;
            font-weight: 700;
            color: #0f172a;
        }

        .sig-sub {
            font-size: 7.5pt;
            color: #64748b;
            margin-top: 2px;
        }

        .seal-box {
            border: 1.5px dashed #94a3b8;
            width: 120px;
            height: 55px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 7.5pt;
            color: #94a3b8;
            margin-bottom: 2px;
        }

        /* PRINT SPECIFIC OVERRIDES */
        @media print {
            @page {
                size: A4 portrait;
                margin: 10mm 10mm 12mm 10mm;
            }

            body {
                background: #ffffff !important;
                padding: 0 !important;
                margin: 0 !important;
                font-size: 9.5pt !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .top-action-bar {
                display: none !important;
            }

            .print-container {
                max-width: 100% !important;
                width: 100% !important;
                box-shadow: none !important;
                padding: 0 !important;
                border-radius: 0 !important;
            }

            .section-card {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
                border-color: #cbd5e1 !important;
                margin-bottom: 10px !important;
                padding: 10px 14px !important;
            }

            .candidate-hero {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
                margin-bottom: 12px !important;
                padding: 12px 16px !important;
            }

            .dossier-footer {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
        }
    </style>
</head>
<body>

    <!-- Screen Action Buttons (Hidden when printing) -->
    <div class="top-action-bar">
        <a href="application-detail.php?id=<?= (int)$app['id'] ?>" class="btn btn-ghost">
            ← Back to Candidate Detail
        </a>
        <div style="display:flex;gap:8px;">
            <a href="applications-export.php?ids=<?= (int)$app['id'] ?>&format=excel" class="btn btn-ghost">
                Export to Excel
            </a>
            <button onclick="window.print()" class="btn btn-primary">
                🖨️ Print Application Form
            </button>
        </div>
    </div>

    <!-- Printable Application Dossier -->
    <div class="print-container">
        
        <!-- Header -->
        <header class="dossier-header">
            <div>
                <div class="brand-name">KAM GLOBAL HR SERVICES</div>
                <div class="brand-sub">International Recruitment &amp; Manpower Solutions</div>
                <div class="brand-meta">Website: www.kamglobalhr.com | Candidate Application Dossier</div>
            </div>
            <div class="reg-box">
                <div class="reg-badge">
                    <small>Registration Number</small>
                    <strong><?= kam_h($app['reg_no'] ?? 'N/A') ?></strong>
                </div>
                <div style="font-size:7.5pt;color:#64748b;margin-top:4px;">
                    Date: <?= date('d M Y, h:i A') ?>
                </div>
            </div>
        </header>

        <!-- Candidate Hero Banner -->
        <div class="candidate-hero">
            <div class="hero-left">
                <?php if (!empty($app['photo_path'])): ?>
                    <img src="../<?= kam_h($app['photo_path']) ?>" alt="Candidate Photo" class="candidate-photo"/>
                <?php else: ?>
                    <div class="candidate-avatar">
                        <?= kam_h(kam_initials($app['full_name'])) ?>
                    </div>
                <?php endif; ?>
                <div>
                    <div class="candidate-title"><?= kam_h($app['full_name']) ?></div>
                    <div class="candidate-post">Applied Position: <strong><?= kam_h($app['job_title'] ?: 'General Candidate Database') ?></strong></div>
                    <div class="candidate-contacts">
                        <span>✉ <?= kam_h($app['email']) ?></span>
                        <span>•</span>
                        <span>📞 <?= kam_h($app['mobile_no']) ?></span>
                        <?php if ($app['whatsapp_no']): ?>
                            <span>•</span>
                            <span>💬 WA: <?= kam_h($app['whatsapp_no']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div>
                <span class="status-badge">Status: <?= kam_h(ucfirst($app['status'])) ?></span>
            </div>
        </div>

        <!-- 1. Applicant Personal Information -->
        <div class="section-card">
            <div class="section-title">1. Applicant Personal Information</div>
            <div class="grid-3">
                <div class="field">
                    <span class="field-label">Date of Birth &amp; Age</span>
                    <span class="field-value"><?= kam_h($app['dob'] ?: 'Not provided') ?> <?= $app['age'] ? "({$app['age']} yrs)" : '' ?></span>
                </div>
                <div class="field">
                    <span class="field-label">Gender</span>
                    <span class="field-value"><?= kam_h($app['gender'] ?: 'Not specified') ?></span>
                </div>
                <div class="field">
                    <span class="field-label">Nationality</span>
                    <span class="field-value"><?= kam_h($app['nationality'] ?: 'Indian') ?></span>
                </div>
                <div class="field">
                    <span class="field-label">Aadhaar / National ID No.</span>
                    <span class="field-value"><?= kam_h($app['aadhaar_no'] ?: 'N/A') ?></span>
                </div>
                <div class="field">
                    <span class="field-label">City / District &amp; State</span>
                    <span class="field-value"><?= kam_h($app['city_district'] ?: '-') ?>, <?= kam_h($app['state'] ?: '-') ?></span>
                </div>
                <div class="field">
                    <span class="field-label">PIN / Postal Code</span>
                    <span class="field-value"><?= kam_h($app['pin_code'] ?: 'N/A') ?></span>
                </div>
                <div class="field col-span-3">
                    <span class="field-label">Current Residential Address</span>
                    <span class="field-value"><?= kam_h($app['current_address'] ?: 'Not provided') ?></span>
                </div>
            </div>
        </div>

        <!-- 2. Educational & Technical Qualifications -->
        <div class="section-card">
            <div class="section-title">2. Educational &amp; Technical Qualification</div>
            <div class="grid-3">
                <div class="field">
                    <span class="field-label">Highest Qualification Level</span>
                    <span class="field-value" style="color:#005eb2;font-size:10pt;font-weight:700;"><?= kam_h($app['qualification_level']) ?></span>
                </div>
                <div class="field">
                    <span class="field-label">Trade / Branch / Specialization</span>
                    <span class="field-value"><?= kam_h($app['trade_branch'] ?: 'General') ?></span>
                </div>
                <div class="field">
                    <span class="field-label">Institute / University / College</span>
                    <span class="field-value"><?= kam_h($app['institute_college'] ?: 'Not specified') ?></span>
                </div>
                <div class="field">
                    <span class="field-label">Year Passed</span>
                    <span class="field-value"><?= kam_h($app['year_passed'] ?: '-') ?></span>
                </div>
                <div class="field">
                    <span class="field-label">Percentage / CGPA / Grade</span>
                    <span class="field-value"><?= kam_h($app['percentage_cgpa'] ?: '-') ?></span>
                </div>
                <div class="field">
                    <span class="field-label">Employment Status</span>
                    <span class="field-value"><?= kam_h(ucfirst((string)$app['employment_status'])) ?></span>
                </div>
                <div class="field col-span-3">
                    <span class="field-label">Additional Certifications / Trade Licences</span>
                    <span class="field-value"><?= kam_h($app['additional_certifications'] ?: 'None specified') ?></span>
                </div>
            </div>
        </div>

        <!-- 3. Employment & Work Experience Details -->
        <div class="section-card">
            <div class="section-title">3. Employment &amp; Work Experience Details</div>
            <div class="grid-3">
                <div class="field">
                    <span class="field-label">Total Work Experience</span>
                    <span class="field-value" style="font-weight:700;"><?= (int) $app['experience_years'] ?> Years, <?= (int) $app['experience_months'] ?> Months</span>
                </div>
                <div class="field">
                    <span class="field-label">Notice Period</span>
                    <span class="field-value"><?= kam_h($app['notice_period'] ?: 'Immediate') ?></span>
                </div>
                <div class="field">
                    <span class="field-label">Current Work Location</span>
                    <span class="field-value"><?= kam_h($app['current_location'] ?: '-') ?></span>
                </div>
                <div class="field">
                    <span class="field-label">Current / Last Designation</span>
                    <span class="field-value"><?= kam_h($app['current_designation'] ?: '-') ?></span>
                </div>
                <div class="field">
                    <span class="field-label">Current / Last Employer</span>
                    <span class="field-value"><?= kam_h($app['current_company'] ?: '-') ?></span>
                </div>
                <div class="field">
                    <span class="field-label">Current &amp; Expected Salary</span>
                    <span class="field-value">Curr: <?= kam_h($app['current_salary'] ?: '-') ?> | Exp: <?= kam_h($app['expected_salary'] ?: '-') ?></span>
                </div>
                <div class="field col-span-3">
                    <span class="field-label">Key Technical / Primary &amp; Secondary Skills</span>
                    <span class="field-value">
                        <strong>Primary:</strong> <?= kam_h($app['primary_skills'] ?: 'N/A') ?><br/>
                        <?php if ($app['secondary_skills']): ?>
                            <strong>Secondary:</strong> <?= kam_h($app['secondary_skills']) ?>
                        <?php endif; ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- 4. Passport & Overseas / GCC Employment Details -->
        <div class="section-card">
            <div class="section-title">4. Passport &amp; Overseas / GCC Employment Details</div>
            <div class="grid-3">
                <div class="field">
                    <span class="field-label">Passport Number</span>
                    <span class="field-value" style="font-weight:700;"><?= kam_h($app['passport_no'] ?: 'Not provided') ?></span>
                </div>
                <div class="field">
                    <span class="field-label">Passport Expiry Date</span>
                    <span class="field-value"><?= kam_h($app['passport_expiry'] ?: '-') ?></span>
                </div>
                <div class="field">
                    <span class="field-label">ECNR / ECR Status</span>
                    <span class="field-value"><?= kam_h($app['ecnr_status'] ?: 'NA') ?></span>
                </div>
                <div class="field">
                    <span class="field-label">Previous GCC Experience</span>
                    <span class="field-value" style="font-weight:700;"><?= kam_h($app['gcc_experience'] ?: 'No') ?></span>
                </div>
                <div class="field">
                    <span class="field-label">GCC Driving License / ID</span>
                    <span class="field-value"><?= kam_h($app['gcc_license_id'] ?: 'N/A') ?></span>
                </div>
                <div class="field">
                    <span class="field-label">Current Visa Status</span>
                    <span class="field-value"><?= kam_h($app['visa_status'] ?: 'N/A') ?></span>
                </div>
                <?php if ($app['gcc_country_employer']): ?>
                    <div class="field col-span-3">
                        <span class="field-label">Previous GCC Employer / Country Details</span>
                        <span class="field-value"><?= kam_h($app['gcc_country_employer']) ?></span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- 5. Uploaded Documents Verification Checklist -->
        <div class="section-card">
            <div class="section-title">5. Uploaded Candidate Documents (Verification Audit)</div>
            <table class="doc-table">
                <thead>
                    <tr>
                        <th style="width:30px;">#</th>
                        <th>Document Title</th>
                        <th style="width:120px;text-align:center;">Status</th>
                        <th>Attached File Record</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $dIdx = 1; foreach ($docs as $docTitle => $dItem): ?>
                        <tr>
                            <td style="color:#64748b;"><?= $dIdx++ ?></td>
                            <td style="font-weight:600;"><?= kam_h($docTitle) ?></td>
                            <td style="text-align:center;">
                                <?php if ($dItem['path']): ?>
                                    <span class="doc-attached">[✓ Uploaded]</span>
                                <?php else: ?>
                                    <span class="doc-none">[— Not Provided]</span>
                                <?php endif; ?>
                            </td>
                            <td style="color:#475569;font-size:8pt;">
                                <?= $dItem['path'] ? kam_h(basename($dItem['path'])) : '—' ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- 6. Emergency Contact & Internal Notes -->
        <div class="section-card">
            <div class="section-title">6. Emergency Contact Information</div>
            <div class="grid-3">
                <div class="field">
                    <span class="field-label">Contact Person Name</span>
                    <span class="field-value"><?= kam_h($app['emergency_name'] ?: 'Not provided') ?></span>
                </div>
                <div class="field">
                    <span class="field-label">Relationship to Candidate</span>
                    <span class="field-value"><?= kam_h($app['emergency_relationship'] ?: '-') ?></span>
                </div>
                <div class="field">
                    <span class="field-label">Emergency Mobile Number</span>
                    <span class="field-value"><?= kam_h($app['emergency_mobile'] ?: '-') ?></span>
                </div>
            </div>
        </div>

        <?php if (!empty($notes)): ?>
            <!-- 7. Recruiter Internal Notes -->
            <div class="section-card">
                <div class="section-title">7. Recruiter Assessment &amp; Interview Feedback</div>
                <?php foreach ($notes as $n): ?>
                    <div style="border-left:3px solid #005eb2;padding-left:10px;margin-bottom:8px;font-size:9pt;">
                        <div style="color:#0f172a;"><?= nl2br(kam_h($n['note'])) ?></div>
                        <div style="font-size:7.5pt;color:#64748b;margin-top:2px;">
                            By: <?= kam_h($n['admin_name'] ?: 'Recruiter') ?> | <?= kam_h(date('d M Y, h:i A', strtotime($n['created_at']))) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Footer / Declaration / Signatures -->
        <footer class="dossier-footer">
            <div class="declaration-text">
                <strong>Candidate Declaration:</strong> I hereby declare that all information furnished in this registration form and all document proofs attached are true, correct, and complete to the best of my knowledge and belief. I understand that any false declaration will result in immediate rejection or termination of overseas employment candidacy.
            </div>
            <div class="sig-row">
                <div class="sig-block">
                    <div class="sig-line">Candidate Signature</div>
                    <div class="sig-sub">Date: _______________</div>
                </div>
                <div class="sig-block">
                    <div class="sig-line">Verified By / HR Recruiter</div>
                    <div class="sig-sub">KAM Global HR Services</div>
                </div>
                <div class="seal-box">
                    Official Stamp / Seal
                </div>
            </div>
        </footer>

    </div>

</body>
</html>
