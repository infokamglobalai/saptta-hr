<?php
declare(strict_types=1);

/**
 * Pre-flight checks for admin — avoids blank 500s when .env or DB is missing.
 */
function kam_env_file_path(): string
{
    return KAM_ROOT . '/config/.env';
}

function kam_env_configured(): bool
{
    return is_readable(kam_env_file_path());
}

function kam_admin_require_env(): void
{
    if (kam_env_configured()) {
        return;
    }
    kam_admin_setup_page(
        'Configuration missing',
        'Create config/.env on the server (copy from config/.env.example) with your MySQL credentials, then run install.php once.',
        [
            ['label' => 'Run setup wizard', 'href' => '../install.php'],
            ['label' => 'System check', 'href' => 'check.php'],
        ]
    );
}

function kam_admin_require_php(): void
{
    if (version_compare(PHP_VERSION, '7.4.0', '>=')) {
        return;
    }
    kam_admin_setup_page(
        'Unsupported PHP version',
        'This CRM requires PHP 7.4 or newer. Your server is running PHP ' . PHP_VERSION . '.',
        [['label' => 'System check', 'href' => 'check.php']]
    );
}

function kam_admin_require_pdo(): void
{
    if (extension_loaded('pdo') && extension_loaded('pdo_mysql')) {
        return;
    }
    kam_admin_setup_page(
        'PDO MySQL extension required',
        'Enable the <code>pdo_mysql</code> PHP extension in your hosting control panel (cPanel → Select PHP Version → Extensions).',
        [['label' => 'System check', 'href' => 'check.php']]
    );
}

function kam_admin_test_database(): ?string
{
    try {
        $pdo = Database::connection();
        $pdo->query('SELECT 1');
        $pdo->query('SELECT COUNT(*) FROM admins LIMIT 1');
        return null;
    } catch (Throwable $e) {
        $msg = $e->getMessage();
        if (str_contains($msg, 'Base table') || str_contains($msg, "doesn't exist")) {
            return 'Database tables are missing. Run install.php to create them.';
        }
        if (str_contains($msg, 'Access denied') || str_contains($msg, 'Unknown database')) {
            return 'Cannot connect to MySQL. Check DB_HOST, DB_NAME, DB_USER, and DB_PASS in config/.env.';
        }
        return 'Database error: ' . $msg;
    }
}

function kam_admin_bootstrap(): void
{
    kam_admin_require_php();
    kam_admin_require_pdo();
    kam_admin_require_env();
}

function kam_admin_setup_page(string $title, string $message, array $actions = []): never
{
    if (!headers_sent()) {
        http_response_code(503);
        header('Content-Type: text/html; charset=utf-8');
    }
    $actionsHtml = '';
    foreach ($actions as $action) {
        $href = kam_h($action['href'] ?? '#');
        $label = kam_h($action['label'] ?? 'Continue');
        $actionsHtml .= '<a class="setup-btn" href="' . $href . '">' . $label . '</a>';
    }
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"/><meta name="viewport" content="width=device-width,initial-scale=1"/>';
    echo '<title>' . kam_h($title) . ' — KAM CRM</title>';
    echo '<link rel="stylesheet" href="assets/css/admin.css"/></head><body class="admin-auth"><div class="admin-auth__main">';
    echo '<div class="admin-auth__card"><div class="admin-auth__card-logo"><span class="material-symbols-outlined">settings</span></div>';
    echo '<h1>' . kam_h($title) . '</h1><p>' . kam_h($message) . '</p>';
    echo '<div class="setup-actions">' . $actionsHtml . '</div></div></div></body></html>';
    exit;
}

function kam_admin_run_safe(callable $fn): void
{
    try {
        $fn();
    } catch (Throwable $e) {
        $detail = kam_env('APP_ENV', 'production') === 'local'
            ? kam_h($e->getMessage())
            : 'Check config/.env, MySQL, and that install.php was run.';
        kam_admin_setup_page(
            'Something went wrong',
            $detail,
            [
                ['label' => 'System check', 'href' => 'check.php'],
                ['label' => 'Sign in', 'href' => 'login.php'],
            ]
        );
    }
}
