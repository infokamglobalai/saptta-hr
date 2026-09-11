<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_once dirname(__DIR__) . '/includes/LeadRepository.php';

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

$input = $_POST;
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
if (str_contains($contentType, 'application/json')) {
    $raw = file_get_contents('php://input');
    $decoded = json_decode($raw ?: '', true);
    if (is_array($decoded)) {
        $input = $decoded;
    }
}

$honeypot = trim((string) ($input['_gotcha'] ?? $input['website_hp'] ?? $input['honeypot'] ?? ''));
if ($honeypot !== '') {
    // Silently succeed for bots without storing spam in DB
    kam_json(['ok' => true, 'message' => 'Thank you. Our team will respond shortly.']);
}

$name = mb_substr(trim((string) ($input['name'] ?? '')), 0, 150);
$email = mb_substr(trim((string) ($input['email'] ?? '')), 0, 190);
$message = mb_substr(trim((string) ($input['message'] ?? '')), 0, 10000);
$phone = mb_substr(trim((string) ($input['phone'] ?? '')), 0, 40);
$company = mb_substr(trim((string) ($input['company'] ?? '')), 0, 190);
$inquiry = mb_substr(trim((string) ($input['inquiry_type'] ?? $input['inquiry'] ?? 'general')), 0, 80);

if ($name === '' || $email === '' || $message === '') {
    kam_json(['ok' => false, 'error' => 'Name, email, and message are required.'], 422);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    kam_json(['ok' => false, 'error' => 'Invalid email address.'], 422);
}

try {
    $id = LeadRepository::create([
        'name' => $name,
        'email' => $email,
        'phone' => $phone ?: null,
        'company' => $company ?: null,
        'inquiry_type' => $inquiry ?: 'general',
        'message' => $message,
        'source' => mb_substr(trim((string) ($input['source'] ?? 'website')), 0, 60),
        'ip_address' => kam_client_ip(),
        'user_agent' => substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 500) ?: null,
    ]);

    kam_log_activity(null, 'lead', $id, 'created', ['email' => $email]);

    kam_json(['ok' => true, 'id' => $id, 'message' => 'Thank you. Our team will respond shortly.']);
} catch (Throwable $e) {
    kam_json(['ok' => false, 'error' => 'Could not save inquiry. Please try again later.'], 500);
}

