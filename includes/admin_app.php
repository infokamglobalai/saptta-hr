<?php
declare(strict_types=1);

/**
 * Shared boot for authenticated admin pages (login excluded).
 */
function kam_admin_app_boot(): void
{
    require_once KAM_ROOT . '/includes/admin_guard.php';
    kam_admin_bootstrap();
    Auth::requireLogin();

    $dbError = kam_admin_test_database();
    if ($dbError !== null) {
        kam_admin_setup_page(
            'Database not ready',
            $dbError,
            [
                ['label' => 'System check', 'href' => 'check.php'],
            ]
        );
    }
}

function kam_admin_render(callable $fn): void
{
    kam_admin_run_safe($fn);
}
