<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_once dirname(__DIR__) . '/includes/JobRepository.php';
require_once dirname(__DIR__) . '/includes/CandidateMedia.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Accept');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    kam_json(['ok' => false, 'error' => 'Method not allowed'], 405);
}

// Check if POST data was discarded by PHP due to post_max_size overflow
if (empty($_POST) && !empty($_SERVER['CONTENT_LENGTH']) && (int)$_SERVER['CONTENT_LENGTH'] > 0) {
    kam_json([
        'ok' => false,
        'error' => 'The total uploaded file size exceeds server upload limits. Please ensure each document is under 10 MB and try again.'
    ], 413);
}

// Check Honeypot for spam bots
$honeypot = trim((string) ($_POST['_gotcha'] ?? $_POST['website_hp'] ?? ''));
if ($honeypot !== '') {
    kam_json(['ok' => true, 'message' => 'Application received successfully.']);
}

// Validate mandatory applicant personal info (Section 1 - All Mandatory)
$fullName = mb_substr(trim((string) ($_POST['full_name'] ?? '')), 0, 190);
$dob = trim((string) ($_POST['dob'] ?? ''));
$age = trim((string) ($_POST['age'] ?? ''));
$gender = trim((string) ($_POST['gender'] ?? ''));
$mobileNo = mb_substr(trim((string) ($_POST['mobile_no'] ?? '')), 0, 40);
$whatsappNo = mb_substr(trim((string) ($_POST['whatsapp_no'] ?? '')), 0, 40);
$email = mb_substr(trim((string) ($_POST['email'] ?? '')), 0, 190);
$aadhaarNo = mb_substr(trim((string) ($_POST['aadhaar_no'] ?? '')), 0, 40);
$nationality = mb_substr(trim((string) ($_POST['nationality'] ?? '')), 0, 80);
$currentAddress = trim((string) ($_POST['current_address'] ?? ''));
$cityDistrict = mb_substr(trim((string) ($_POST['city_district'] ?? '')), 0, 120);
$state = mb_substr(trim((string) ($_POST['state'] ?? '')), 0, 120);
$pinCode = mb_substr(trim((string) ($_POST['pin_code'] ?? '')), 0, 20);

if ($fullName === '' || $dob === '' || $age === '' || $gender === '' || $mobileNo === '' || $whatsappNo === '' || $email === '' || $aadhaarNo === '' || $nationality === '' || $currentAddress === '' || $cityDistrict === '' || $state === '' || $pinCode === '') {
    kam_json(['ok' => false, 'error' => 'Please fill all mandatory fields in Applicant Personal Information (Section 1).'], 422);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    kam_json(['ok' => false, 'error' => 'Please provide a valid email address.'], 422);
}

// Validate Educational / Technical Qualification (Section 2 - All Mandatory)
$qualificationLevel = trim((string) ($_POST['qualification_level'] ?? ''));
$tradeBranch = trim((string) ($_POST['trade_branch'] ?? ''));
$instituteCollege = trim((string) ($_POST['institute_college'] ?? ''));
$yearPassed = trim((string) ($_POST['year_passed'] ?? ''));
$percentageCgpa = trim((string) ($_POST['percentage_cgpa'] ?? ''));
$additionalCert = trim((string) ($_POST['additional_certifications'] ?? ''));

if ($qualificationLevel === '' || $tradeBranch === '' || $instituteCollege === '' || $yearPassed === '' || $percentageCgpa === '' || $additionalCert === '') {
    kam_json(['ok' => false, 'error' => 'Please fill all mandatory fields in Educational / Technical Qualification (Section 2).'], 422);
}

// Validate Employment / Experience Details (Section 3 - Experience Status & Total Experience Mandatory)
$employmentStatus = trim((string) ($_POST['employment_status'] ?? ''));
$experienceYears = $_POST['experience_years'] ?? '';
$experienceMonths = $_POST['experience_months'] ?? '';

if ($employmentStatus === '' || $experienceYears === '' || $experienceMonths === '') {
    kam_json(['ok' => false, 'error' => 'Please provide Experience Status and Total Experience (Years & Months) in Section 3.'], 422);
}

// Validate Emergency Contact Information (Section 6 - All Mandatory)
$emergencyName = mb_substr(trim((string) ($_POST['emergency_name'] ?? '')), 0, 160);
$emergencyRelationship = mb_substr(trim((string) ($_POST['emergency_relationship'] ?? '')), 0, 80);
$emergencyMobile = mb_substr(trim((string) ($_POST['emergency_mobile'] ?? '')), 0, 40);
$emergencyAltMobile = mb_substr(trim((string) ($_POST['emergency_alt_mobile'] ?? '')), 0, 40);

if ($emergencyName === '' || $emergencyRelationship === '' || $emergencyMobile === '' || $emergencyAltMobile === '') {
    kam_json(['ok' => false, 'error' => 'Please fill all mandatory fields in Emergency Contact Information (Section 6).'], 422);
}

// Validate First 4 Uploaded Documents (Section 5 - First 4 Mandatory)
if (empty($_FILES['doc_resume']['name'])) {
    kam_json(['ok' => false, 'error' => 'Updated Resume / CV document is required (Section 5).'], 422);
}
if (empty($_FILES['doc_photo']['name'])) {
    kam_json(['ok' => false, 'error' => 'Passport-size Photograph is required (Section 5).'], 422);
}
if (empty($_FILES['doc_aadhaar']['name'])) {
    kam_json(['ok' => false, 'error' => 'Aadhaar Card copy / National ID is required (Section 5).'], 422);
}
if (empty($_FILES['doc_qualification']['name'])) {
    kam_json(['ok' => false, 'error' => 'Highest Degree / Diploma / ITI Certificate is required (Section 5).'], 422);
}

// Process Uploaded Documents
$uploadedDocs = [];
$docMap = [
    'doc_resume' => 'resume_path',
    'doc_photo' => 'photo_path',
    'doc_aadhaar' => 'aadhaar_path',
    'doc_qualification' => 'qualification_cert_path',
    'doc_experience' => 'experience_cert_path',
    'doc_passport' => 'passport_copy_path',
    'doc_skill' => 'skill_cert_path',
    'doc_license' => 'driving_license_path',
    'doc_other' => 'other_docs_path',
];

try {
    foreach ($docMap as $inputKey => $dbCol) {
        if (!empty($_FILES[$inputKey]['name'])) {
            $path = CandidateMedia::uploadDocument($_FILES[$inputKey], $inputKey);
            if ($path) {
                $uploadedDocs[$dbCol] = $path;
            }
        }
    }
} catch (Throwable $e) {
    // If upload fails, cleanup already uploaded files for this request
    foreach ($uploadedDocs as $p) {
        CandidateMedia::deleteFile($p);
    }
    kam_json(['ok' => false, 'error' => $e->getMessage()], 422);
}

// Assemble Data
$payload = array_merge($_POST, $uploadedDocs, [
    'full_name' => $fullName,
    'email' => $email,
    'mobile_no' => $mobileNo,
    'ip_address' => kam_client_ip(),
    'source' => 'careers_portal',
]);

try {
    $appId = JobRepository::apply($payload);

    kam_log_activity(null, 'job_application', $appId, 'submitted', [
        'name' => $fullName,
        'email' => $email,
        'job_id' => $_POST['job_id'] ?? null,
    ]);

    kam_json([
        'ok' => true,
        'id' => $appId,
        'message' => 'Candidate Registration & Application submitted successfully! Our recruitment team will review your profile and contact you soon.',
    ]);
} catch (Throwable $e) {
    // Cleanup on DB failure
    foreach ($uploadedDocs as $p) {
        CandidateMedia::deleteFile($p);
    }
    kam_json(['ok' => false, 'error' => 'Could not submit application. Please try again later.'], 500);
}
