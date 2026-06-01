<?php
declare(strict_types=1);

/**
 * Optional setup UI — safe to run anytime; only creates missing tables/admin.
 */

require_once __DIR__ . '/includes/bootstrap.php';

$messages = [];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $messages = kam_crm_ensure_installed();
        $messages[] = 'Setup complete. Login at /admin/login.php';
    } catch (Throwable $e) {
        $errors[] = $e->getMessage();
    }
} elseif (is_readable(KAM_ROOT . '/config/.env')) {
    try {
        if (kam_crm_tables_exist()) {
            $messages[] = 'Already installed — database tables exist.';
            $messages[] = 'You can push/deploy again without losing data. Running setup again is safe.';
        }
    } catch (Throwable $e) {
        $errors[] = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>KAM CRM — Install</title>
    <link rel="stylesheet" href="admin/assets/css/admin.css"/>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0"/>
</head>
<body class="admin-auth">
    <div class="admin-auth__card">
        <h1>KAM CRM Setup</h1>
        <p>Tables are created automatically on first visit. Use this page only if you need to run setup manually.</p>
        <?php foreach ($errors as $e): ?>
            <div class="admin-alert admin-alert--error"><?= kam_h($e) ?></div>
        <?php endforeach; ?>
        <?php foreach ($messages as $m): ?>
            <div class="admin-alert admin-alert--success"><?= kam_h($m) ?></div>
        <?php endforeach; ?>
        <form method="post">
            <button type="submit" class="admin-btn admin-btn--primary">Run setup (safe — keeps existing data)</button>
        </form>
        <p style="margin-top:1rem;font-size:0.8125rem;color:#64748b">
            <a href="admin/login.php">Go to admin login</a> ·
            <a href="admin/check.php">System check</a>
        </p>
    </div>
</body>
</html>
