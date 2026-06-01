<?php
/** @var array $lead */
/** @var array $notes */
/** @var array $activity */
/** @var array $admins */
/** @var string $message */
/** @var string $error */
/** @var string $csrf */
/** @var int $id */

$phoneTel = $lead['phone'] ? preg_replace('/[^\d+]/', '', $lead['phone']) : '';
$priority = $lead['priority'] ?? 'normal';
?>
<?php if ($message): ?>
    <div class="admin-alert admin-alert--success">
        <span class="material-symbols-outlined">check_circle</span>
        <?= kam_h($message) ?>
    </div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="admin-alert admin-alert--error">
        <span class="material-symbols-outlined">error</span>
        <?= kam_h($error) ?>
    </div>
<?php endif; ?>

<div class="admin-lead-header">
    <div class="admin-lead-header__main">
        <span class="admin-table__avatar admin-table__avatar--lg"><?= kam_h(kam_initials($lead['name'])) ?></span>
        <div>
            <h2 class="admin-lead-header__name"><?= kam_h($lead['name']) ?></h2>
            <p class="admin-lead-header__meta">
                Lead #<?= (int) $lead['id'] ?>
                · <?= kam_h(date('M j, Y g:i A', strtotime($lead['created_at']))) ?>
            </p>
            <div class="admin-lead-header__badges">
                <span class="admin-badge admin-badge--<?= kam_h($lead['status']) ?>"><?= kam_h(kam_status_label($lead['status'])) ?></span>
                <span class="admin-badge admin-badge--priority admin-badge--priority-<?= kam_h($priority) ?>"><?= kam_h(ucfirst($priority)) ?> priority</span>
                <?php if (!empty($lead['assigned_name'])): ?>
                    <span class="admin-badge admin-badge--assigned">
                        <span class="material-symbols-outlined">person</span>
                        <?= kam_h($lead['assigned_name']) ?>
                    </span>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="admin-lead-header__actions">
        <a href="leads.php" class="admin-btn admin-btn--ghost admin-btn--sm">
            <span class="material-symbols-outlined">arrow_back</span>
            All leads
        </a>
        <a href="mailto:<?= kam_h($lead['email']) ?>" class="admin-btn admin-btn--ghost admin-btn--sm">
            <span class="material-symbols-outlined">mail</span>
            Email
        </a>
        <?php if ($phoneTel): ?>
            <a href="tel:<?= kam_h($phoneTel) ?>" class="admin-btn admin-btn--ghost admin-btn--sm">
                <span class="material-symbols-outlined">call</span>
                Call
            </a>
        <?php endif; ?>
    </div>
</div>

<div class="admin-grid-2 admin-lead-grid">
    <div class="admin-lead-main">
        <div class="admin-card" style="margin-bottom:1.25rem">
            <div class="admin-card__head">
                <h2><span class="material-symbols-outlined">chat</span> Inquiry message</h2>
            </div>
            <div class="admin-card__body">
                <div class="admin-lead-message">
                    <?= nl2br(kam_h($lead['message'] ?? 'No message provided.')) ?>
                </div>
            </div>
        </div>

        <div class="admin-card" style="margin-bottom:1.25rem">
            <div class="admin-card__head">
                <h2><span class="material-symbols-outlined">contact_page</span> Contact details</h2>
            </div>
            <div class="admin-card__body">
                <dl class="admin-detail admin-detail--grid">
                    <dt>Email</dt>
                    <dd><a href="mailto:<?= kam_h($lead['email']) ?>"><?= kam_h($lead['email']) ?></a></dd>
                    <dt>Phone</dt>
                    <dd>
                        <?php if ($lead['phone']): ?>
                            <a href="tel:<?= kam_h($phoneTel) ?>"><?= kam_h($lead['phone']) ?></a>
                        <?php else: ?>
                            —
                        <?php endif; ?>
                    </dd>
                    <dt>Company</dt>
                    <dd><?= kam_h($lead['company'] ?: '—') ?></dd>
                    <dt>Inquiry type</dt>
                    <dd><?= kam_h(kam_inquiry_types()[$lead['inquiry_type']] ?? $lead['inquiry_type']) ?></dd>
                    <dt>Source</dt>
                    <dd><?= kam_h($lead['source']) ?></dd>
                    <?php if (!empty($lead['updated_at']) && $lead['updated_at'] !== $lead['created_at']): ?>
                        <dt>Last updated</dt>
                        <dd><?= kam_h(date('M j, Y g:i A', strtotime($lead['updated_at']))) ?></dd>
                    <?php endif; ?>
                    <?php if (!empty($lead['ip_address'])): ?>
                        <dt>IP address</dt>
                        <dd><code class="admin-code"><?= kam_h($lead['ip_address']) ?></code></dd>
                    <?php endif; ?>
                </dl>
            </div>
        </div>

        <div class="admin-card">
            <div class="admin-card__head">
                <h2><span class="material-symbols-outlined">sticky_note_2</span> Internal notes</h2>
            </div>
            <div class="admin-card__body">
                <?php if (empty($notes)): ?>
                    <p class="admin-empty-hint">No notes yet. Add a follow-up summary after you contact this lead.</p>
                <?php else: ?>
                    <?php foreach ($notes as $n): ?>
                        <div class="admin-note">
                            <div class="admin-note__meta">
                                <?= kam_h($n['admin_name'] ?? 'System') ?>
                                · <?= kam_h(date('M j, Y g:i A', strtotime($n['created_at']))) ?>
                            </div>
                            <?= nl2br(kam_h($n['note'])) ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                <form method="post" class="admin-lead-note-form">
                    <input type="hidden" name="csrf" value="<?= kam_h($csrf) ?>"/>
                    <input type="hidden" name="action" value="note"/>
                    <div class="admin-form-group">
                        <label for="note">Add note</label>
                        <textarea id="note" name="note" required placeholder="Call summary, next steps, meeting notes…"></textarea>
                    </div>
                    <button type="submit" class="admin-btn admin-btn--primary admin-btn--sm">
                        <span class="material-symbols-outlined">add</span>
                        Save note
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="admin-lead-sidebar">
        <div class="admin-card admin-pipeline-card">
            <div class="admin-card__head">
                <h2><span class="material-symbols-outlined">tune</span> Pipeline</h2>
            </div>
            <div class="admin-card__body">
                <form method="post" class="admin-lead-pipeline-form">
                    <input type="hidden" name="csrf" value="<?= kam_h($csrf) ?>"/>
                    <input type="hidden" name="action" value="update"/>
                    <div class="admin-form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status">
                            <?php foreach (kam_lead_statuses() as $s): ?>
                                <option value="<?= kam_h($s) ?>" <?= $lead['status'] === $s ? 'selected' : '' ?>><?= kam_h(kam_status_label($s)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="admin-form-group">
                        <label for="priority">Priority</label>
                        <select id="priority" name="priority">
                            <?php foreach (['low', 'normal', 'high'] as $p): ?>
                                <option value="<?= $p ?>" <?= $priority === $p ? 'selected' : '' ?>><?= ucfirst($p) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="admin-form-group">
                        <label for="assigned_to">Assigned to</label>
                        <select id="assigned_to" name="assigned_to">
                            <option value="">Unassigned</option>
                            <?php foreach ($admins as $admin): ?>
                                <option value="<?= (int) $admin['id'] ?>" <?= (int) ($lead['assigned_to'] ?? 0) === (int) $admin['id'] ? 'selected' : '' ?>>
                                    <?= kam_h($admin['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="admin-btn admin-btn--primary" style="width:100%">
                        <span class="material-symbols-outlined">check_circle</span>
                        Update lead
                    </button>
                </form>
            </div>
        </div>

        <?php if (!empty($activity)): ?>
            <div class="admin-card" style="margin-top:1.25rem">
                <div class="admin-card__head">
                    <h2><span class="material-symbols-outlined">history</span> Activity</h2>
                </div>
                <div class="admin-card__body admin-activity-list">
                    <?php foreach ($activity as $log): ?>
                        <div class="admin-activity-item">
                            <span class="admin-activity-item__dot" aria-hidden="true"></span>
                            <div>
                                <strong><?= kam_h(str_replace('_', ' ', $log['action'])) ?></strong>
                                <span class="admin-activity-item__meta">
                                    <?= kam_h($log['admin_name'] ?? 'System') ?>
                                    · <?= kam_h(date('M j, g:i A', strtotime($log['created_at']))) ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="admin-card admin-card--danger" style="margin-top:1.25rem">
            <div class="admin-card__body">
                <h3 class="admin-danger-title">Danger zone</h3>
                <p class="admin-empty-hint">Permanently remove this lead and all notes.</p>
                <form method="post" onsubmit="return confirm('Delete this lead permanently?');">
                    <input type="hidden" name="csrf" value="<?= kam_h($csrf) ?>"/>
                    <input type="hidden" name="action" value="delete"/>
                    <button type="submit" class="admin-btn admin-btn--danger admin-btn--sm">
                        <span class="material-symbols-outlined">delete</span>
                        Delete lead
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
