<?php
/** @var array $stats */
/** @var array $recent */
$statusCounts = $stats['status_counts'] ?? [];
$pipelineTotal = max(1, (int) ($stats['total_leads'] ?? 0));
$user = Auth::user();
$firstName = trim(explode(' ', $user['name'] ?? 'Admin')[0]);
$hour = (int) date('G');
$greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
?>
<section class="admin-welcome">
    <div class="admin-welcome__copy">
        <p class="admin-welcome__eyebrow"><?= kam_h($greeting) ?></p>
        <h2>Welcome back, <span><?= kam_h($firstName) ?></span></h2>
        <p>Here’s what’s happening with your leads and newsletter today.</p>
    </div>
    <div class="admin-welcome__chips">
        <span class="admin-welcome__chip">
            <span class="material-symbols-outlined">fiber_new</span>
            <?= (int) $stats['new_leads'] ?> new
        </span>
        <span class="admin-welcome__chip">
            <span class="material-symbols-outlined">calendar_month</span>
            <?= (int) $stats['leads_this_week'] ?> this week
        </span>
        <span class="admin-welcome__chip admin-welcome__chip--accent">
            <span class="material-symbols-outlined">emoji_events</span>
            <?= (int) $stats['won_leads'] ?> won
        </span>
    </div>
</section>

<div class="admin-stats">
    <div class="admin-stat admin-stat--blue">
        <div class="admin-stat__glow" aria-hidden="true"></div>
        <div class="admin-stat__icon"><span class="material-symbols-outlined">groups</span></div>
        <div class="admin-stat__body">
            <strong><?= (int) $stats['total_leads'] ?></strong>
            <span>Total leads</span>
        </div>
    </div>
    <div class="admin-stat admin-stat--cyan">
        <div class="admin-stat__glow" aria-hidden="true"></div>
        <div class="admin-stat__icon"><span class="material-symbols-outlined">fiber_new</span></div>
        <div class="admin-stat__body">
            <strong><?= (int) $stats['new_leads'] ?></strong>
            <span>New (open)</span>
        </div>
    </div>
    <div class="admin-stat admin-stat--navy">
        <div class="admin-stat__glow" aria-hidden="true"></div>
        <div class="admin-stat__icon"><span class="material-symbols-outlined">calendar_month</span></div>
        <div class="admin-stat__body">
            <strong><?= (int) $stats['leads_this_week'] ?></strong>
            <span>This week</span>
        </div>
    </div>
    <div class="admin-stat admin-stat--green">
        <div class="admin-stat__glow" aria-hidden="true"></div>
        <div class="admin-stat__icon"><span class="material-symbols-outlined">emoji_events</span></div>
        <div class="admin-stat__body">
            <strong><?= (int) $stats['won_leads'] ?></strong>
            <span>Won deals</span>
        </div>
    </div>
    <div class="admin-stat admin-stat--amber">
        <div class="admin-stat__glow" aria-hidden="true"></div>
        <div class="admin-stat__icon"><span class="material-symbols-outlined">mail</span></div>
        <div class="admin-stat__body">
            <strong><?= (int) $stats['subscribers'] ?></strong>
            <span>Newsletter subs</span>
        </div>
    </div>
</div>

<div class="admin-dashboard-grid">
    <div class="admin-card admin-card--pipeline">
        <div class="admin-card__head">
            <h2><span class="material-symbols-outlined">account_tree</span> Pipeline overview</h2>
            <a href="leads.php" class="admin-btn admin-btn--ghost admin-btn--sm">
                All leads
                <span class="material-symbols-outlined">arrow_forward</span>
            </a>
        </div>
        <div class="admin-card__body admin-card__body--flush">
            <div class="admin-pipeline">
                <?php foreach (kam_lead_statuses() as $status): ?>
                    <?php
                    $count = (int) ($statusCounts[$status] ?? 0);
                    $pct = $pipelineTotal > 0 ? min(100, round(($count / $pipelineTotal) * 100)) : 0;
                    ?>
                    <a class="admin-pipeline__row" href="leads.php?status=<?= kam_h($status) ?>">
                        <span class="admin-pipeline__label">
                            <span class="admin-badge admin-badge--<?= kam_h($status) ?>"><?= kam_h(kam_status_label($status)) ?></span>
                        </span>
                        <span class="admin-pipeline__bar" aria-hidden="true">
                            <span class="admin-pipeline__fill admin-pipeline__fill--<?= kam_h($status) ?>" style="width:<?= (int) $pct ?>%"></span>
                        </span>
                        <span class="admin-pipeline__count"><?= $count ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="admin-card admin-card--quick">
        <div class="admin-card__head">
            <h2><span class="material-symbols-outlined">bolt</span> Quick actions</h2>
        </div>
        <div class="admin-card__body">
            <div class="admin-quick-actions">
                <a href="leads.php?status=new" class="admin-quick-action">
                    <span class="admin-quick-action__icon admin-quick-action__icon--blue">
                        <span class="material-symbols-outlined">inbox</span>
                    </span>
                    <span class="admin-quick-action__text">
                        <strong>New leads</strong>
                        <small>Review open inquiries</small>
                    </span>
                    <span class="admin-quick-action__badge"><?= (int) $stats['new_leads'] ?></span>
                </a>
                <a href="subscribers.php" class="admin-quick-action">
                    <span class="admin-quick-action__icon admin-quick-action__icon--cyan">
                        <span class="material-symbols-outlined">campaign</span>
                    </span>
                    <span class="admin-quick-action__text">
                        <strong>Subscribers</strong>
                        <small>Newsletter list</small>
                    </span>
                    <span class="admin-quick-action__badge"><?= (int) $stats['subscribers'] ?></span>
                </a>
                <a href="../contact.html" target="_blank" rel="noopener" class="admin-quick-action">
                    <span class="admin-quick-action__icon admin-quick-action__icon--navy">
                        <span class="material-symbols-outlined">language</span>
                    </span>
                    <span class="admin-quick-action__text">
                        <strong>Contact page</strong>
                        <small>View live form</small>
                    </span>
                    <span class="material-symbols-outlined admin-quick-action__arrow">north_east</span>
                </a>
                <a href="check.php" class="admin-quick-action admin-quick-action--muted">
                    <span class="admin-quick-action__icon">
                        <span class="material-symbols-outlined">health_and_safety</span>
                    </span>
                    <span class="admin-quick-action__text">
                        <strong>System check</strong>
                        <small>Server &amp; database</small>
                    </span>
                </a>
            </div>
        </div>
    </div>
</div>

<div class="admin-card admin-card--table">
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
