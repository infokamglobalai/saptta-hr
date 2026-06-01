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
        <h2>Newsletter subscribers (<?= $total ?>)</h2>
    </div>
    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr><th>Email</th><th>Source</th><th>Subscribed</th></tr>
            </thead>
            <tbody>
                <?php if (empty($items)): ?>
                    <tr><td colspan="3">No subscribers yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($items as $row): ?>
                        <tr>
                            <td><?= kam_h($row['email']) ?></td>
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
$activeNav = 'subscribers';
require __DIR__ . '/includes/layout.php';
