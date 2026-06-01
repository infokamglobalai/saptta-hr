<?php
declare(strict_types=1);

/** @var string $pageTitle */
/** @var string $activeNav */
/** @var string $content */

$user = Auth::user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title><?= kam_h($pageTitle) ?> — KAM CRM</title>
    <link rel="stylesheet" href="assets/css/admin.css"/>
</head>
<body class="admin-body">
<div class="admin-layout">
    <aside class="admin-sidebar">
        <div class="admin-sidebar__brand">
            <strong>KAM Global HR</strong>
            <span>Admin CRM</span>
        </div>
        <ul class="admin-nav">
            <li><a href="index.php" class="<?= ($activeNav ?? '') === 'dashboard' ? 'is-active' : '' ?>">Dashboard</a></li>
            <li><a href="leads.php" class="<?= ($activeNav ?? '') === 'leads' ? 'is-active' : '' ?>">Leads</a></li>
            <li><a href="subscribers.php" class="<?= ($activeNav ?? '') === 'subscribers' ? 'is-active' : '' ?>">Newsletter</a></li>
            <li><a href="../index.html" target="_blank" rel="noopener">View website</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
        <div class="admin-sidebar__user">
            <?= kam_h($user['name'] ?? '') ?><br/>
            <small><?= kam_h($user['email'] ?? '') ?></small>
        </div>
    </aside>
    <div class="admin-main">
        <header class="admin-topbar">
            <h1><?= kam_h($pageTitle) ?></h1>
        </header>
        <main class="admin-content">
            <?= $content ?>
        </main>
    </div>
</div>
</body>
</html>
