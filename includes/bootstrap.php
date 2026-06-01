<?php
declare(strict_types=1);

/**
 * Shared bootstrap for API and admin.
 */

define('KAM_ROOT', dirname(__DIR__));

function kam_load_env(string $path): void
{
    if (!is_readable($path)) {
        return;
    }
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return;
    }
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        if (!str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value, " \t\"'");
        if ($key !== '' && getenv($key) === false) {
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
}

kam_load_env(KAM_ROOT . '/config/.env');
kam_load_env(KAM_ROOT . '/config/.env.local');

function kam_env(string $key, ?string $default = null): ?string
{
    $v = $_ENV[$key] ?? getenv($key);
    if ($v === false || $v === '') {
        return $default;
    }
    return (string) $v;
}

function kam_config(): array
{
    return [
        'db' => [
            'host' => kam_env('DB_HOST', '127.0.0.1'),
            'port' => (int) kam_env('DB_PORT', '3306'),
            'name' => kam_env('DB_NAME', 'kam_hr_crm'),
            'user' => kam_env('DB_USER', 'root'),
            'pass' => kam_env('DB_PASS', ''),
        ],
        'app_url' => rtrim(kam_env('APP_URL', ''), '/'),
        'session_name' => kam_env('SESSION_NAME', 'kam_admin_session'),
    ];
}

if (str_contains(str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? ''), '/admin/')) {
    require_once KAM_ROOT . '/includes/admin_security.php';
}

require_once KAM_ROOT . '/includes/Database.php';
require_once KAM_ROOT . '/includes/Auth.php';
require_once KAM_ROOT . '/includes/helpers.php';
