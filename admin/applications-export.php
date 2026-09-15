<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_once dirname(__DIR__) . '/includes/admin_app.php';
require_once dirname(__DIR__) . '/includes/JobRepository.php';

kam_admin_app_boot();

$user = Auth::user();

$format = strtolower(trim((string) ($_REQUEST['format'] ?? 'csv')));
if (!in_array($format, ['csv', 'excel', 'xls'], true)) {
    $format = 'csv';
}

// Check if specific IDs are provided (via GET/POST comma separated or array)
$ids = [];
if (!empty($_REQUEST['ids'])) {
    if (is_array($_REQUEST['ids'])) {
        $rawIds = $_REQUEST['ids'];
    } else {
        $rawIds = explode(',', (string) $_REQUEST['ids']);
    }
    foreach ($rawIds as $idVal) {
        $idInt = (int) trim((string) $idVal);
        if ($idInt > 0) {
            $ids[] = $idInt;
        }
    }
    $ids = array_unique($ids);
}

// Build filter options if no specific IDs are passed
$filters = [];
if (empty($ids)) {
    $all = (int) ($_REQUEST['all'] ?? 0);
    if ($all !== 1) {
        $filters = [
            'job_id' => $_REQUEST['job_id'] ?? '',
            'status' => $_REQUEST['status'] ?? '',
            'qualification' => $_REQUEST['qualification'] ?? '',
            'q' => trim((string) ($_REQUEST['q'] ?? '')),
            'date_from' => trim((string) ($_REQUEST['date_from'] ?? '')),
            'date_to' => trim((string) ($_REQUEST['date_to'] ?? '')),
        ];
    }
}

$rows = JobRepository::applicationsExport($filters, $ids);

// Determine Base URL for document links
$baseUrl = kam_config()['app_url'] ?? '';
if (empty($baseUrl)) {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443);
    $scheme = $isHttps ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scriptPath = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $rootPath = preg_replace('#/admin/[^/]+$#', '', $scriptPath);
    $baseUrl = $scheme . '://' . $host . rtrim($rootPath, '/');
}

$getDocUrl = static function (?string $path) use ($baseUrl): string {
    if (empty($path)) {
        return '';
    }
    if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
        return $path;
    }
    return rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
};

// Activity logging
kam_log_activity($user['id'] ?? null, 'job_application', null, 'bulk_export', [
    'count' => count($rows),
    'format' => $format,
    'by_ids' => !empty($ids),
]);

$timestamp = date('Y-m-d_His');
$filenameBase = 'kam_job_applications_' . $timestamp;

// Define columns
$columns = [
    'Reg No' => static fn($r) => $r['reg_no'] ?? '',
    'Applied Date' => static fn($r) => !empty($r['created_at']) ? date('Y-m-d H:i', strtotime($r['created_at'])) : '',
    'Full Name' => static fn($r) => $r['full_name'] ?? '',
    'Applied Job' => static fn($r) => $r['job_title'] ?: 'General Application',
    'Job Category' => static fn($r) => $r['job_category'] ?? '',
    'Email Address' => static fn($r) => $r['email'] ?? '',
    'Mobile Number' => static fn($r) => $r['mobile_no'] ?? '',
    'WhatsApp Number' => static fn($r) => $r['whatsapp_no'] ?? '',
    'Date of Birth' => static fn($r) => $r['dob'] ?? '',
    'Age' => static fn($r) => !empty($r['age']) ? (string) $r['age'] : '',
    'Gender' => static fn($r) => $r['gender'] ?? '',
    'Aadhaar / ID No' => static fn($r) => $r['aadhaar_no'] ?? '',
    'Current Address' => static fn($r) => $r['current_address'] ?? '',
    'City / District' => static fn($r) => $r['city_district'] ?? '',
    'State' => static fn($r) => $r['state'] ?? '',
    'PIN Code' => static fn($r) => $r['pin_code'] ?? '',
    'Nationality' => static fn($r) => $r['nationality'] ?? 'Indian',
    'Qualification Level' => static fn($r) => $r['qualification_level'] ?? '',
    'Trade / Branch' => static fn($r) => $r['trade_branch'] ?? '',
    'Institute / College' => static fn($r) => $r['institute_college'] ?? '',
    'Year Passed' => static fn($r) => $r['year_passed'] ?? '',
    'Percentage / CGPA' => static fn($r) => $r['percentage_cgpa'] ?? '',
    'Additional Certifications' => static fn($r) => $r['additional_certifications'] ?? '',
    'Employment Status' => static fn($r) => ucfirst((string) ($r['employment_status'] ?? 'fresher')),
    'Experience (Years)' => static fn($r) => (string) ($r['experience_years'] ?? 0),
    'Experience (Months)' => static fn($r) => (string) ($r['experience_months'] ?? 0),
    'Total Experience' => static function($r) {
        if (($r['employment_status'] ?? '') === 'fresher') return 'Fresher';
        $y = (int) ($r['experience_years'] ?? 0);
        $m = (int) ($r['experience_months'] ?? 0);
        return "{$y}y {$m}m";
    },
    'Current Designation' => static fn($r) => $r['current_designation'] ?? '',
    'Current Company' => static fn($r) => $r['current_company'] ?? '',
    'Current Location' => static fn($r) => $r['current_location'] ?? '',
    'Current Salary' => static fn($r) => $r['current_salary'] ?? '',
    'Expected Salary' => static fn($r) => $r['expected_salary'] ?? '',
    'Notice Period' => static fn($r) => $r['notice_period'] ?? '',
    'Primary Skills' => static fn($r) => $r['primary_skills'] ?? '',
    'Secondary Skills' => static fn($r) => $r['secondary_skills'] ?? '',
    'Passport Number' => static fn($r) => $r['passport_no'] ?? '',
    'Passport Expiry' => static fn($r) => $r['passport_expiry'] ?? '',
    'Place of Issue' => static fn($r) => $r['place_of_issue'] ?? '',
    'ECNR / ECR Status' => static fn($r) => $r['ecnr_status'] ?? '',
    'GCC Experience' => static fn($r) => $r['gcc_experience'] ?? 'No',
    'GCC Employer / Country Details' => static fn($r) => $r['gcc_country_employer'] ?? '',
    'GCC License / ID' => static fn($r) => $r['gcc_license_id'] ?? '',
    'Visa Status' => static fn($r) => $r['visa_status'] ?? '',
    'Emergency Contact Name' => static fn($r) => $r['emergency_name'] ?? '',
    'Emergency Relationship' => static fn($r) => $r['emergency_relationship'] ?? '',
    'Emergency Mobile' => static fn($r) => $r['emergency_mobile'] ?? '',
    'Emergency Alt Mobile' => static fn($r) => $r['emergency_alt_mobile'] ?? '',
    'Application Status' => static fn($r) => ucfirst((string) ($r['status'] ?? 'new')),
    'Assigned To' => static fn($r) => $r['assigned_name'] ?? '',
    'Source' => static fn($r) => $r['source'] ?? 'website',
    'Resume / CV Link' => static fn($r) => $getDocUrl($r['resume_path'] ?? null),
    'Photo Link' => static fn($r) => $getDocUrl($r['photo_path'] ?? null),
    'Aadhaar / ID Proof Link' => static fn($r) => $getDocUrl($r['aadhaar_path'] ?? null),
    'Qualification Cert Link' => static fn($r) => $getDocUrl($r['qualification_cert_path'] ?? null),
    'Experience Cert Link' => static fn($r) => $getDocUrl($r['experience_cert_path'] ?? null),
    'Passport Copy Link' => static fn($r) => $getDocUrl($r['passport_copy_path'] ?? null),
    'Skill Cert Link' => static fn($r) => $getDocUrl($r['skill_cert_path'] ?? null),
    'Driving Licence Link' => static fn($r) => $getDocUrl($r['driving_license_path'] ?? null),
    'Other Docs Link' => static fn($r) => $getDocUrl($r['other_docs_path'] ?? null),
];

if ($format === 'excel' || $format === 'xls') {
    $filename = $filenameBase . '.xls';
    header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    header('Pragma: no-cache');

    echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
    echo '<head>';
    echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>';
    echo '<!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>Job Applications</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]-->';
    echo '<style>
        table { border-collapse: collapse; width: 100%; font-family: Calibri, Arial, sans-serif; font-size: 11pt; }
        th { background-color: #001f3f; color: #ffffff; font-weight: bold; border: 1px solid #cbd5e1; padding: 10px 14px; text-align: left; white-space: nowrap; }
        td { border: 1px solid #e2e8f0; padding: 8px 12px; vertical-align: top; mso-number-format:"\@"; }
        tr:nth-child(even) td { background-color: #f8fafc; }
        .doc-link { color: #0284c7; text-decoration: underline; font-weight: 600; }
    </style>';
    echo '</head>';
    echo '<body>';
    echo '<table border="1">';
    echo '<thead><tr>';
    foreach (array_keys($columns) as $header) {
        echo '<th>' . htmlspecialchars((string) $header, ENT_QUOTES, 'UTF-8') . '</th>';
    }
    echo '</tr></thead>';
    echo '<tbody>';

    foreach ($rows as $row) {
        echo '<tr>';
        foreach ($columns as $header => $extractor) {
            $val = (string) $extractor($row);
            $isLink = str_contains($header, 'Link') && !empty($val);

            if ($isLink) {
                echo '<td><a href="' . htmlspecialchars($val, ENT_QUOTES, 'UTF-8') . '" class="doc-link" target="_blank">' . htmlspecialchars($val, ENT_QUOTES, 'UTF-8') . '</a></td>';
            } else {
                echo '<td>' . htmlspecialchars($val, ENT_QUOTES, 'UTF-8') . '</td>';
            }
        }
        echo '</tr>';
    }

    echo '</tbody></table>';
    echo '</body></html>';
    exit;
}

// Default: CSV format
$filename = $filenameBase . '.csv';
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');
header('Pragma: no-cache');

$out = fopen('php://output', 'w');

// Output UTF-8 BOM for Microsoft Excel UTF-8 compatibility
fwrite($out, "\xEF\xBB\xBF");

// Write CSV Header
fputcsv($out, array_keys($columns));

// Write Rows
foreach ($rows as $row) {
    $csvRow = [];
    foreach ($columns as $header => $extractor) {
        $csvRow[] = (string) $extractor($row);
    }
    fputcsv($out, $csvRow);
}

fclose($out);
exit;
