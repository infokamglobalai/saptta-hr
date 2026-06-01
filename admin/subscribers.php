<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';

Auth::requireLogin();

$pdo = Database::connection();
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 30;
$offset = ($page - 1) * $perPage;

$total = (int) $pdo->query("SELECT COUNT(*) FROM newsletter_subscribers WHERE status = 'active'")->fetchColumn();
$stmt = $pdo->prepare(
    "SELECT * FROM newsletter_subscribers WHERE status = 'active' ORDER BY created_at DESC LIMIT $perPage OFFSET $offset"
);
$stmt->execute();
$items = $stmt->fetchAll();
$pages = (int) max(1, ceil($total / $perPage));

ob_start();
?>
<div class="admin-card">
    <div class="admin-card__head">
        <h2><span class="material-symbols-outlined">mail</span> Newsletter subscribers</h2>
        <span class="admin-badge admin-badge--qualified"><?= (int) $total ?> active</span>
    </div>
    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr><th>Subscriber</th><th>Source</th><th>Subscribed</th></tr>
            </thead>
            <tbody>
                <?php if (empty($items)): ?>
                    <tr>
                        <td colspan="3" class="admin-table__empty">
                            <span class="material-symbols-outlined">mail</span>
                            No subscribers yet. Sign-ups from the Insights page will appear here.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($items as $row): ?>
                        <tr>
                            <td>
                                <div class="admin-table__contact">
                                    <span class="admin-table__avatar"><?= kam_h(strtoupper(substr($row['email'], 0, 1))) ?></span>
                                    <span>
                                        <strong><?= kam_h($row['email']) ?></strong>
                                    </span>
                                </div>
                            </td>
                            <td><?= kam_h($row['source']) ?></td>
                            <td><?= kam_h(date('M j, Y', strtotime($row['created_at']))) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php
$content = ob_get_clean();
$pageTitle = 'Newsletter';
$pageSubtitle = (string) $total . ' active subscribers';
$activeNav = 'subscribers';
require __DIR__ . '/includes/layout.php';
