<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_once dirname(__DIR__) . '/includes/admin_app.php';
require_once dirname(__DIR__) . '/includes/LeadRepository.php';
require_once dirname(__DIR__) . '/includes/JobRepository.php';

kam_admin_app_boot();

kam_admin_render(function (): void {
    $stats = LeadRepository::stats();
    $jobStats = JobRepository::stats();
    $recent = LeadRepository::list([], 1, 8);
    $recentApplications = JobRepository::applicationsList([], 1, 5);

    ob_start();
    include __DIR__ . '/views/dashboard.php';
    $content = ob_get_clean();
    $pageTitle = 'Dashboard';
    $pageSubtitle = 'Overview of leads and activity';
    $activeNav = 'dashboard';
    require __DIR__ . '/includes/layout.php';
});
