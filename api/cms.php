<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_once dirname(__DIR__) . '/includes/CmsRepository.php';

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

$scope = trim((string) ($_GET['scope'] ?? 'all'));

try {
    switch ($scope) {
        case 'settings':
            kam_json(['ok' => true, 'settings' => CmsRepository::settingsAll()]);
            break;
        case 'offices':
            kam_json(['ok' => true, 'offices' => CmsRepository::officesPublic()]);
            break;
        case 'insights':
            $type = $_GET['type'] ?? null;
            kam_json(['ok' => true, 'insights' => CmsRepository::insightsPublic($type)]);
            break;
        case 'insight':
            $slug = trim((string) ($_GET['slug'] ?? ''));
            if ($slug === '') {
                kam_json(['ok' => false, 'error' => 'slug required'], 422);
            }
            $item = CmsRepository::insightBySlug($slug);
            if (!$item) {
                kam_json(['ok' => false, 'error' => 'Not found'], 404);
            }
            kam_json(['ok' => true, 'insight' => $item]);
            break;
        case 'cases':
            kam_json(['ok' => true, 'case_studies' => CmsRepository::casesPublic()]);
            break;
        case 'testimonials':
            kam_json(['ok' => true, 'testimonials' => CmsRepository::testimonialsPublic()]);
            break;
        default:
            kam_json(['ok' => true] + CmsRepository::publicBundle());
    }
} catch (Throwable $e) {
    kam_json(['ok' => false, 'error' => 'CMS unavailable'], 503);
}
