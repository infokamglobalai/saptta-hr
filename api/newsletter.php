<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';

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
if (str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'application/json')) {
    $decoded = json_decode(file_get_contents('php://input') ?: '', true);
    if (is_array($decoded)) {
        $input = $decoded;
    }
}

$honeypot = trim((string) ($input['_gotcha'] ?? $input['website_hp'] ?? $input['honeypot'] ?? ''));
if ($honeypot !== '') {
    kam_json(['ok' => true, 'message' => 'Subscribed successfully.']);
}

$email = mb_substr(trim((string) ($input['email'] ?? '')), 0, 190);
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    kam_json(['ok' => false, 'error' => 'Valid email required.'], 422);
}

try {
    $pdo = Database::connection();
    $stmt = $pdo->prepare(
        'INSERT INTO newsletter_subscribers (email, source) VALUES (?, ?)
         ON DUPLICATE KEY UPDATE status = "active", source = VALUES(source)'
    );
    $stmt->execute([$email, mb_substr(trim((string) ($input['source'] ?? 'insights')), 0, 60)]);
    kam_json(['ok' => true, 'message' => 'Subscribed successfully.']);
} catch (Throwable) {
    kam_json(['ok' => false, 'error' => 'Subscription failed.'], 500);
}

