<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_once dirname(__DIR__) . '/includes/admin_guard.php';

kam_admin_bootstrap();

Auth::requireLogin();

$dbError = kam_admin_test_database();
if ($dbError !== null) {
    kam_admin_setup_page(
        'Database not ready',
        $dbError,
        [
            ['label' => 'System check', 'href' => 'check.php'],
            ['label' => 'Run install', 'href' => '../install.php'],
        ]
    );
}

require_once dirname(__DIR__) . '/includes/LeadRepository.php';

kam_admin_run_safe(function (): void {
    $stats = LeadRepository::stats();
    $recent = LeadRepository::list([], 1, 8);

    ob_start();
    include __DIR__ . '/views/dashboard.php';
    $content = ob_get_clean();
    $pageTitle = 'Dashboard';
    $pageSubtitle = 'Overview of leads and activity';
    $activeNav = 'dashboard';
    require __DIR__ . '/includes/layout.php';
});
