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

$name = trim((string) ($input['name'] ?? ''));
$email = trim((string) ($input['email'] ?? ''));
$message = trim((string) ($input['message'] ?? ''));
$phone = trim((string) ($input['phone'] ?? ''));
$company = trim((string) ($input['company'] ?? ''));
$inquiry = trim((string) ($input['inquiry_type'] ?? $input['inquiry'] ?? 'general'));

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
        'source' => trim((string) ($input['source'] ?? 'website')),
        'ip_address' => kam_client_ip(),
        'user_agent' => substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 500) ?: null,
    ]);

    kam_log_activity(null, 'lead', $id, 'created', ['email' => $email]);

    kam_json(['ok' => true, 'id' => $id, 'message' => 'Thank you. Our team will respond shortly.']);
} catch (Throwable $e) {
    kam_json(['ok' => false, 'error' => 'Could not save inquiry. Please try again later.'], 500);
}
