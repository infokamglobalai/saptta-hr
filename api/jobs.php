<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_once dirname(__DIR__) . '/includes/JobRepository.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Accept');
header('Cache-Control: public, max-age=60');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    kam_json(['ok' => false, 'error' => 'Method not allowed'], 405);
}

$id = (int) ($_GET['id'] ?? 0);
$slug = trim((string) ($_GET['slug'] ?? ''));
$category = trim((string) ($_GET['category'] ?? 'all'));
$q = trim((string) ($_GET['q'] ?? ''));

try {
    if ($id > 0) {
        $job = JobRepository::jobFind($id);
        if (!$job || $job['status'] !== 'active') {
            kam_json(['ok' => false, 'error' => 'Job not found'], 404);
        }
        kam_json(['ok' => true, 'job' => $job]);
    }

    if ($slug !== '') {
        $job = JobRepository::jobBySlug($slug);
        if (!$job) {
            kam_json(['ok' => false, 'error' => 'Job not found'], 404);
        }
        kam_json(['ok' => true, 'job' => $job]);
    }

    $jobs = JobRepository::jobsPublic($category, $q);
    $categories = array_values(JobRepository::categories());

    kam_json([
        'ok' => true,
        'jobs' => $jobs,
        'categories' => $categories,
        'total' => count($jobs),
    ]);
} catch (Throwable $e) {
    // If DB is temporarily offline or unconfigured, return default active jobs gracefully
    $fallbackJobs = JobRepository::defaultSeedJobs();
    if (!empty($category) && $category !== 'all') {
        $fallbackJobs = array_values(array_filter($fallbackJobs, fn($j) => ($j['category'] ?? '') === $category));
    }
    if (!empty($q)) {
        $kw = strtolower($q);
        $fallbackJobs = array_values(array_filter($fallbackJobs, fn($j) => 
            str_contains(strtolower($j['title'] ?? ''), $kw) ||
            str_contains(strtolower($j['location'] ?? ''), $kw) ||
            str_contains(strtolower($j['summary'] ?? ''), $kw)
        ));
    }

    kam_json([
        'ok' => true,
        'jobs' => $fallbackJobs,
        'categories' => ['Engineering', 'Technical & ITI', 'Manufacturing', 'Construction & MEP'],
        'total' => count($fallbackJobs),
        'notice' => 'Loaded from standard roster (Database reconnecting)',
    ]);
}
