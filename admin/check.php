<?php
declare(strict_types=1);

/**
 * Server diagnostics for admin CRM (no login required).
 * Remove or protect this file after setup on production.
 */
require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_once dirname(__DIR__) . '/includes/admin_guard.php';

header('Content-Type: text/html; charset=utf-8');

$checks = [];

$checks[] = [
    'label' => 'PHP version',
    'ok' => version_compare(PHP_VERSION, '7.4.0', '>='),
    'detail' => PHP_VERSION . ' (need 7.4+)',
];

$checks[] = [
    'label' => 'PDO extension',
    'ok' => extension_loaded('pdo'),
    'detail' => extension_loaded('pdo') ? 'loaded' : 'missing',
];

$checks[] = [
    'label' => 'PDO MySQL',
    'ok' => extension_loaded('pdo_mysql'),
    'detail' => extension_loaded('pdo_mysql') ? 'loaded' : 'missing — enable in cPanel PHP extensions',
];

$envPath = kam_env_file_path();
$checks[] = [
    'label' => 'config/.env',
    'ok' => is_readable($envPath),
    'detail' => is_readable($envPath) ? $envPath : 'File not found — copy config/.env.example to config/.env',
];

$dbOk = false;
$dbDetail = 'Not tested';
if (is_readable($envPath)) {
    try {
        $pdo = Database::connection();
        $pdo->query('SELECT 1');
        $count = (int) $pdo->query('SELECT COUNT(*) FROM admins')->fetchColumn();
        $dbOk = true;
        $dbDetail = 'Connected · ' . $count . ' admin user(s)';
    } catch (Throwable $e) {
        $dbDetail = $e->getMessage();
    }
}
$checks[] = [
    'label' => 'MySQL database',
    'ok' => $dbOk,
    'detail' => $dbDetail,
];

$allOk = true;
foreach ($checks as $c) {
    if (!$c['ok']) {
        $allOk = false;
        break;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>KAM CRM — System check</title>
    <link rel="stylesheet" href="assets/css/admin.css"/>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0"/>
    <style>
        .check-list { list-style: none; margin: 0; padding: 0; }
        .check-list li {
            display: flex; align-items: flex-start; gap: 0.75rem;
            padding: 0.85rem 0; border-bottom: 1px solid #e2e8f0;
        }
        .check-list li:last-child { border-bottom: none; }
        .check-ok { color: #059669; }
        .check-fail { color: #dc2626; }
        .setup-actions { display: flex; flex-wrap: wrap; gap: 0.5rem; margin-top: 1.25rem; }
        .setup-btn {
            display: inline-flex; padding: 0.55rem 1rem; border-radius: 0.5rem;
            background: #001f3f; color: #fff; text-decoration: none; font-weight: 600; font-size: 0.875rem;
        }
    </style>
</head>
<body class="admin-auth">
    <div class="admin-auth__main">
        <div class="admin-auth__card" style="max-width:32rem">
            <h1>System check</h1>
            <p><?= $allOk ? 'All checks passed. You can sign in to the admin panel.' : 'Fix the failed items below, then refresh this page.' ?></p>
            <ul class="check-list">
                <?php foreach ($checks as $c): ?>
                    <li>
                        <span class="material-symbols-outlined <?= $c['ok'] ? 'check-ok' : 'check-fail' ?>">
                            <?= $c['ok'] ? 'check_circle' : 'error' ?>
                        </span>
                        <span>
                            <strong><?= kam_h($c['label']) ?></strong><br/>
                            <small style="color:#64748b"><?= kam_h($c['detail']) ?></small>
                        </span>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div class="setup-actions">
                <a class="setup-btn" href="login.php">Go to login</a>
            </div>
        </div>
    </div>
</body>
</html>
