<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_once dirname(__DIR__) . '/includes/LeadRepository.php';

Auth::requireLogin();

$status = trim((string) ($_GET['status'] ?? ''));
$q = trim((string) ($_GET['q'] ?? ''));
$page = max(1, (int) ($_GET['page'] ?? 1));

$filters = [];
if ($status !== '' && in_array($status, kam_lead_statuses(), true)) {
    $filters['status'] = $status;
}
if ($q !== '') {
    $filters['q'] = $q;
}

$result = LeadRepository::list($filters, $page, 25);

ob_start();
?>
<div class="admin-card">
    <div class="admin-card__head">
        <h2><span class="material-symbols-outlined">group</span> All leads</h2>
        <form class="admin-filters" method="get" action="leads.php">
            <div class="admin-form-group">
                <label for="q">Search</label>
                <input type="search" id="q" name="q" placeholder="Name, email, company" value="<?= kam_h($q) ?>"/>
            </div>
            <div class="admin-form-group">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="">All statuses</option>
                    <?php foreach (kam_lead_statuses() as $s): ?>
                        <option value="<?= kam_h($s) ?>" <?= $status === $s ? 'selected' : '' ?>><?= kam_h(kam_status_label($s)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="admin-btn admin-btn--primary admin-btn--sm">
                <span class="material-symbols-outlined">filter_list</span>
                Filter
            </button>
            <?php if ($q || $status): ?>
                <a href="leads.php" class="admin-btn admin-btn--ghost admin-btn--sm">Clear</a>
            <?php endif; ?>
        </form>
    </div>
    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Contact</th>
                    <th>Company</th>
                    <th>Inquiry</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($result['items'])): ?>
                    <tr>
                        <td colspan="6" class="admin-table__empty">
                            <span class="material-symbols-outlined">search_off</span>
                            No leads found. Try adjusting your filters.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($result['items'] as $lead): ?>
                        <tr>
                            <td>
                                <div class="admin-table__contact">
                                    <span class="admin-table__avatar"><?= kam_h(kam_initials($lead['name'])) ?></span>
                                    <span>
                                        <strong><?= kam_h($lead['name']) ?></strong>
                                        <small>#<?= (int) $lead['id'] ?> · <?= kam_h($lead['email']) ?></small>
                                    </span>
                                </div>
                            </td>
                            <td><?= kam_h($lead['company'] ?? '—') ?></td>
                            <td><?= kam_h(kam_inquiry_types()[$lead['inquiry_type']] ?? $lead['inquiry_type']) ?></td>
                            <td><span class="admin-badge admin-badge--<?= kam_h($lead['status']) ?>"><?= kam_h(kam_status_label($lead['status'])) ?></span></td>
                            <td><?= kam_h(date('M j, Y', strtotime($lead['created_at']))) ?></td>
                            <td>
                                <a href="lead.php?id=<?= (int) $lead['id'] ?>" class="admin-btn admin-btn--ghost admin-btn--sm">
                                    View
                                    <span class="material-symbols-outlined">chevron_right</span>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if ($result['pages'] > 1): ?>
        <div class="admin-pagination">
            <?php for ($p = 1; $p <= $result['pages']; $p++): ?>
                <?php $qs = http_build_query(array_filter(['status' => $status, 'q' => $q, 'page' => $p])); ?>
                <a href="leads.php?<?= kam_h($qs) ?>" class="admin-btn admin-btn--ghost admin-btn--sm <?= $p === $page ? 'is-active' : '' ?>"><?= $p ?></a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>
<?php
$content = ob_get_clean();
$pageTitle = 'Leads';
$pageSubtitle = (string) $result['total'] . ' total · Page ' . $page . ' of ' . $result['pages'];
$activeNav = 'leads';
require __DIR__ . '/includes/layout.php';
