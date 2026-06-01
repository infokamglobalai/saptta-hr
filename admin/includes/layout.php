<?php
declare(strict_types=1);

/** @var string $pageTitle */
/** @var string $activeNav */
/** @var string $content */
/** @var string|null $pageSubtitle */

$user = Auth::user();
$initials = 'KA';
if (!empty($user['name'])) {
    $parts = preg_split('/\s+/', trim($user['name']));
    $initials = strtoupper(substr($parts[0] ?? 'K', 0, 1) . substr($parts[1] ?? 'A', 0, 1));
}

$navItems = [
    'dashboard' => ['href' => 'index.php', 'label' => 'Dashboard', 'icon' => 'dashboard'],
    'leads' => ['href' => 'leads.php', 'label' => 'Leads', 'icon' => 'group'],
    'subscribers' => ['href' => 'subscribers.php', 'label' => 'Newsletter', 'icon' => 'mail'],
];
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
            <a href="index.php" class="admin-sidebar__logo">
                <span class="admin-sidebar__logo-mark" aria-hidden="true">
                    <span class="material-symbols-outlined">corporate_fare</span>
                </span>
                <span>
                    <strong>KAM Global HR</strong>
                    <span>Admin CRM</span>
                </span>
            </a>
        </div>
        <ul class="admin-nav">
            <?php foreach ($navItems as $key => $item): ?>
                <li>
                    <a href="<?= kam_h($item['href']) ?>"
                       class="admin-nav__link <?= ($activeNav ?? '') === $key ? 'is-active' : '' ?>">
                        <span class="material-symbols-outlined"><?= kam_h($item['icon']) ?></span>
                        <span><?= kam_h($item['label']) ?></span>
                    </a>
                </li>
            <?php endforeach; ?>
            <li class="admin-nav__divider" aria-hidden="true"></li>
            <li>
                <a href="../index.html" target="_blank" rel="noopener" class="admin-nav__link">
                    <span class="material-symbols-outlined">open_in_new</span>
                    <span>View website</span>
                </a>
            </li>
            <li>
                <a href="logout.php" class="admin-nav__link">
                    <span class="material-symbols-outlined">logout</span>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
        <div class="admin-sidebar__user">
            <span class="admin-sidebar__avatar"><?= kam_h($initials) ?></span>
            <div class="admin-sidebar__user-info">
                <strong><?= kam_h($user['name'] ?? '') ?></strong>
                <small><?= kam_h($user['email'] ?? '') ?></small>
            </div>
        </div>
    </aside>
    <div class="admin-main">
        <header class="admin-topbar">
            <div class="admin-topbar__title">
                <h1><?= kam_h($pageTitle) ?></h1>
                <p><?= kam_h($pageSubtitle ?? date('l, F j, Y')) ?></p>
            </div>
            <div class="admin-topbar__actions">
                <?php if (($activeNav ?? '') === 'dashboard'): ?>
                    <a href="leads.php" class="admin-btn admin-btn--primary admin-btn--sm">
                        <span class="material-symbols-outlined">add</span>
                        View leads
                    </a>
                <?php elseif (($activeNav ?? '') === 'leads'): ?>
                    <a href="leads.php?status=new" class="admin-btn admin-btn--ghost admin-btn--sm">
                        <span class="material-symbols-outlined">fiber_new</span>
                        New only
                    </a>
                <?php endif; ?>
            </div>
        </header>
        <main class="admin-content">
            <?= $content ?>
        </main>
    </div>
</div>
</body>
</html>
