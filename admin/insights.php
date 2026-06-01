<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_once dirname(__DIR__) . '/includes/admin_app.php';
require_once dirname(__DIR__) . '/includes/CmsRepository.php';

kam_admin_app_boot();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id']) && Auth::verifyCsrf($_POST['csrf'] ?? null)) {
    CmsRepository::insightDelete((int) $_POST['delete_id']);
}

$items = CmsRepository::insightsAll();
$csrf = Auth::csrfToken();

kam_admin_render(function () use ($items, $csrf): void {
    ob_start();
    ?>
    <div class="admin-card">
        <div class="admin-card__head">
            <h2><span class="material-symbols-outlined">article</span> Insights &amp; articles</h2>
            <a href="insight-edit.php" class="admin-btn admin-btn--primary admin-btn--sm">
                <span class="material-symbols-outlined">add</span> New article
            </a>
        </div>
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead><tr><th>Title</th><th>Type</th><th>Status</th><th>Featured</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($items as $row): ?>
                    <tr>
                        <td>
                            <strong><?= kam_h($row['title']) ?></strong><br/>
                            <small>/insight.php?slug=<?= kam_h($row['slug']) ?></small>
                        </td>
                        <td><?= kam_h($row['content_type']) ?></td>
                        <td><span class="admin-badge admin-badge--<?= $row['status'] === 'published' ? 'won' : 'new' ?>"><?= kam_h($row['status']) ?></span></td>
                        <td><?= (int) $row['is_featured'] ? 'Yes' : '—' ?></td>
                        <td>
                            <a href="insight-edit.php?id=<?= (int) $row['id'] ?>" class="admin-btn admin-btn--ghost admin-btn--sm">Edit</a>
                            <form method="post" style="display:inline" onsubmit="return confirm('Delete?')">
                                <input type="hidden" name="csrf" value="<?= kam_h($csrf) ?>"/>
                                <button type="submit" name="delete_id" value="<?= (int) $row['id'] ?>" class="admin-btn admin-btn--ghost admin-btn--sm">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
    $content = ob_get_clean();
    $pageTitle = 'Insights';
    $pageSubtitle = count($items) . ' item(s)';
    $activeNav = 'insights';
    require __DIR__ . '/includes/layout.php';
});
