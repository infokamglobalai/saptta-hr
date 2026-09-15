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

$firstName = trim(explode(' ', $user['name'] ?? 'Admin')[0]);

$navItems = [
    'dashboard' => ['href' => 'index.php', 'label' => 'Dashboard', 'icon' => 'dashboard'],
    'leads' => ['href' => 'leads.php', 'label' => 'Leads', 'icon' => 'group'],
    'jobs' => ['href' => 'jobs.php', 'label' => 'Job Openings', 'icon' => 'work'],
    'applications' => ['href' => 'applications.php', 'label' => 'Applications', 'icon' => 'assignment_ind'],
    'subscribers' => ['href' => 'subscribers.php', 'label' => 'Newsletter', 'icon' => 'mail'],
    'insights' => ['href' => 'insights.php', 'label' => 'Insights', 'icon' => 'article'],
    'cases' => ['href' => 'case-studies.php', 'label' => 'Case studies', 'icon' => 'folder_special'],
    'testimonials' => ['href' => 'testimonials.php', 'label' => 'Testimonials', 'icon' => 'format_quote'],
    'offices' => ['href' => 'offices.php', 'label' => 'Offices', 'icon' => 'location_on'],
    'settings' => ['href' => 'settings.php', 'label' => 'Site settings', 'icon' => 'settings'],
];
?>
<!DOCTYPE html>
<html lang="en" class="admin-app">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <meta name="robots" content="noindex, nofollow"/>
    <title><?= kam_h($pageTitle) ?> — KAM CRM</title>
    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
    <link rel="stylesheet" href="assets/css/admin.css?v=<?= file_exists(__DIR__ . '/../assets/css/admin.css') ? filemtime(__DIR__ . '/../assets/css/admin.css') : '3' ?>"/>
</head>
<body class="admin-body" data-page="<?= kam_h($activeNav ?? '') ?>">
<div class="admin-layout">
    <div class="admin-sidebar-backdrop" id="adminSidebarBackdrop" hidden></div>
    <aside class="admin-sidebar" id="adminSidebar" aria-label="Main navigation">
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
        <div class="admin-sidebar__nav-scroll">
            <p class="admin-nav__section">Menu</p>
            <ul class="admin-nav">
                <?php foreach ($navItems as $key => $item): ?>
                    <li>
                        <a href="<?= kam_h($item['href']) ?>"
                           class="admin-nav__link <?= ($activeNav ?? '') === $key ? 'is-active' : '' ?>">
                            <span class="admin-nav__icon-wrap">
                                <span class="material-symbols-outlined"><?= kam_h($item['icon']) ?></span>
                            </span>
                            <span><?= kam_h($item['label']) ?></span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
            <p class="admin-nav__section">System</p>
            <ul class="admin-nav admin-nav--secondary">
                <li>
                    <a href="../index.html" target="_blank" rel="noopener" class="admin-nav__link">
                        <span class="admin-nav__icon-wrap">
                            <span class="material-symbols-outlined">open_in_new</span>
                        </span>
                        <span>View website</span>
                    </a>
                </li>
                <li>
                    <a href="check.php" class="admin-nav__link">
                        <span class="admin-nav__icon-wrap">
                            <span class="material-symbols-outlined">health_and_safety</span>
                        </span>
                        <span>System check</span>
                    </a>
                </li>
                <li>
                    <a href="logout.php" class="admin-nav__link admin-nav__link--danger">
                        <span class="admin-nav__icon-wrap">
                            <span class="material-symbols-outlined">logout</span>
                        </span>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
        </div>
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
            <div class="admin-topbar__left">
                <button type="button" class="admin-menu-toggle" id="adminMenuToggle" aria-label="Open menu" aria-expanded="false" aria-controls="adminSidebar">
                    <span class="material-symbols-outlined">menu</span>
                </button>
                <div class="admin-topbar__title">
                    <h1><?= kam_h($pageTitle) ?></h1>
                    <p><?= kam_h($pageSubtitle ?? date('l, F j, Y')) ?></p>
                </div>
            </div>
            <div class="admin-topbar__actions">
                <form class="admin-search" method="get" action="leads.php" role="search">
                    <span class="material-symbols-outlined" aria-hidden="true">search</span>
                    <input type="search" name="q" placeholder="Search leads…" aria-label="Search leads"/>
                </form>
                <?php if (($activeNav ?? '') === 'dashboard'): ?>
                    <a href="leads.php?status=new" class="admin-btn admin-btn--primary admin-btn--sm">
                        <span class="material-symbols-outlined">inbox</span>
                        New leads
                    </a>
                <?php elseif (($activeNav ?? '') === 'leads'): ?>
                    <a href="leads.php?status=new" class="admin-btn admin-btn--ghost admin-btn--sm">
                        <span class="material-symbols-outlined">fiber_new</span>
                        New only
                    </a>
                    <a href="leads.php" class="admin-btn admin-btn--ghost admin-btn--sm">
                        <span class="material-symbols-outlined">list</span>
                        All leads
                    </a>
                <?php endif; ?>
                <div class="admin-topbar__user" title="<?= kam_h($user['email'] ?? '') ?>">
                    <span class="admin-topbar__avatar"><?= kam_h($initials) ?></span>
                </div>
            </div>
        </header>
        <main class="admin-content admin-content--animate">
            <?= $content ?>
        </main>
    </div>
</div>
<script>
(function () {
    var sidebar = document.getElementById('adminSidebar');
    var backdrop = document.getElementById('adminSidebarBackdrop');
    var toggle = document.getElementById('adminMenuToggle');
    if (!sidebar || !backdrop || !toggle) return;

    function setOpen(open) {
        sidebar.classList.toggle('is-open', open);
        backdrop.hidden = !open;
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        toggle.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
        document.body.classList.toggle('admin-nav-open', open);
    }

    toggle.addEventListener('click', function () {
        setOpen(!sidebar.classList.contains('is-open'));
    });
    backdrop.addEventListener('click', function () { setOpen(false); });
    sidebar.querySelectorAll('.admin-nav__link').forEach(function (link) {
        link.addEventListener('click', function () {
            if (window.matchMedia('(max-width: 900px)').matches) setOpen(false);
        });
    });
})();

document.querySelectorAll('.admin-table__row--clickable').forEach(function (row) {
    row.addEventListener('click', function (e) {
        if (e.target.closest('a, button')) return;
        var href = row.getAttribute('data-href');
        if (href) window.location.href = href;
    });
});
</script>
</body>
</html>
