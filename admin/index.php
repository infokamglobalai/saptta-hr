<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_once dirname(__DIR__) . '/includes/LeadRepository.php';

Auth::requireLogin();

$stats = LeadRepository::stats();
$recent = LeadRepository::list([], 1, 8);

ob_start();
?>
<div class="admin-stats">
    <div class="admin-stat admin-stat--blue">
        <div class="admin-stat__icon"><span class="material-symbols-outlined">groups</span></div>
        <div class="admin-stat__body">
            <strong><?= (int) $stats['total_leads'] ?></strong>
            <span>Total leads</span>
        </div>
    </div>
    <div class="admin-stat admin-stat--cyan">
        <div class="admin-stat__icon"><span class="material-symbols-outlined">fiber_new</span></div>
        <div class="admin-stat__body">
            <strong><?= (int) $stats['new_leads'] ?></strong>
            <span>New (open)</span>
        </div>
    </div>
    <div class="admin-stat admin-stat--navy">
        <div class="admin-stat__icon"><span class="material-symbols-outlined">calendar_month</span></div>
        <div class="admin-stat__body">
            <strong><?= (int) $stats['leads_this_week'] ?></strong>
            <span>This week</span>
        </div>
    </div>
    <div class="admin-stat admin-stat--green">
        <div class="admin-stat__icon"><span class="material-symbols-outlined">emoji_events</span></div>
        <div class="admin-stat__body">
            <strong><?= (int) $stats['won_leads'] ?></strong>
            <span>Won deals</span>
        </div>
    </div>
    <div class="admin-stat admin-stat--amber">
        <div class="admin-stat__icon"><span class="material-symbols-outlined">mail</span></div>
        <div class="admin-stat__body">
            <strong><?= (int) $stats['subscribers'] ?></strong>
            <span>Newsletter subs</span>
        </div>
    </div>
</div>

<div class="admin-card">
    <div class="admin-card__head">
        <h2><span class="material-symbols-outlined">inbox</span> Recent inquiries</h2>
        <a href="leads.php" class="admin-btn admin-btn--ghost admin-btn--sm">
            View all
            <span class="material-symbols-outlined">arrow_forward</span>
        </a>
    </div>
    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Contact</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recent['items'])): ?>
                    <tr>
                        <td colspan="5" class="admin-table__empty">
                            <span class="material-symbols-outlined">inbox</span>
                            No leads yet. Submissions from the contact form will appear here.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($recent['items'] as $lead): ?>
                        <tr>
                            <td>
                                <div class="admin-table__contact">
                                    <span class="admin-table__avatar"><?= kam_h(kam_initials($lead['name'])) ?></span>
                                    <span>
                                        <strong><?= kam_h($lead['name']) ?></strong>
                                        <small><?= kam_h($lead['email']) ?></small>
                                    </span>
                                </div>
                            </td>
                            <td><?= kam_h(kam_inquiry_types()[$lead['inquiry_type']] ?? $lead['inquiry_type']) ?></td>
                            <td><span class="admin-badge admin-badge--<?= kam_h($lead['status']) ?>"><?= kam_h(kam_status_label($lead['status'])) ?></span></td>
                            <td><?= kam_h(date('M j, Y', strtotime($lead['created_at']))) ?></td>
                            <td>
                                <a href="lead.php?id=<?= (int) $lead['id'] ?>" class="admin-btn admin-btn--ghost admin-btn--sm">
                                    Open
                                    <span class="material-symbols-outlined">chevron_right</span>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php
$content = ob_get_clean();
$pageTitle = 'Dashboard';
$pageSubtitle = 'Overview of leads and activity';
$activeNav = 'dashboard';
require __DIR__ . '/includes/layout.php';
