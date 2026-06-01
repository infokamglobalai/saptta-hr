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
$inquiryLabel = kam_inquiry_types()[$lead['inquiry_type']] ?? $lead['inquiry_type'];
?>
<div class="admin-lead-page">
    <?php if ($message): ?>
        <div class="admin-alert admin-alert--success admin-lead-page__alert">
            <span class="material-symbols-outlined">check_circle</span>
            <?= kam_h($message) ?>
        </div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="admin-alert admin-alert--error admin-lead-page__alert">
            <span class="material-symbols-outlined">error</span>
            <?= kam_h($error) ?>
        </div>
    <?php endif; ?>

    <div class="admin-lead-hero">
        <div class="admin-lead-hero__bg" aria-hidden="true"></div>
        <div class="admin-lead-hero__inner">
            <nav class="admin-lead-breadcrumb" aria-label="Breadcrumb">
                <a href="leads.php">Leads</a>
                <span class="material-symbols-outlined">chevron_right</span>
                <span>#<?= (int) $lead['id'] ?></span>
            </nav>
            <div class="admin-lead-hero__row">
                <div class="admin-lead-hero__profile">
                    <span class="admin-lead-hero__avatar"><?= kam_h(kam_initials($lead['name'])) ?></span>
                    <div>
                        <h2 class="admin-lead-hero__name"><?= kam_h($lead['name']) ?></h2>
                        <?php if ($lead['company']): ?>
                            <p class="admin-lead-hero__company">
                                <span class="material-symbols-outlined">business</span>
                                <?= kam_h($lead['company']) ?>
                            </p>
                        <?php endif; ?>
                        <p class="admin-lead-hero__time">
                            <span class="material-symbols-outlined">schedule</span>
                            <?= kam_h(date('M j, Y · g:i A', strtotime($lead['created_at']))) ?>
                        </p>
                    </div>
                </div>
                <div class="admin-lead-hero__actions">
                    <a href="mailto:<?= kam_h($lead['email']) ?>" class="admin-btn admin-btn--white">
                        <span class="material-symbols-outlined">mail</span>
                        Send email
                    </a>
                    <?php if ($phoneTel): ?>
                        <a href="tel:<?= kam_h($phoneTel) ?>" class="admin-btn admin-btn--hero-ghost">
                            <span class="material-symbols-outlined">call</span>
                            Call
                        </a>
                    <?php endif; ?>
                    <a href="leads.php" class="admin-btn admin-btn--hero-ghost">
                        <span class="material-symbols-outlined">arrow_back</span>
                        Back
                    </a>
                </div>
            </div>
            <div class="admin-lead-hero__badges">
                <span class="admin-lead-chip admin-lead-chip--<?= kam_h($lead['status']) ?>">
                    <?= kam_h(kam_status_label($lead['status'])) ?>
                </span>
                <span class="admin-lead-chip admin-lead-chip--priority-<?= kam_h($priority) ?>">
                    <?= kam_h(ucfirst($priority)) ?> priority
                </span>
                <span class="admin-lead-chip admin-lead-chip--type">
                    <span class="material-symbols-outlined">label</span>
                    <?= kam_h($inquiryLabel) ?>
                </span>
                <?php if (!empty($lead['assigned_name'])): ?>
                    <span class="admin-lead-chip admin-lead-chip--assigned">
                        <span class="material-symbols-outlined">person</span>
                        <?= kam_h($lead['assigned_name']) ?>
                    </span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="admin-lead-body admin-grid-2 admin-lead-grid">
        <div class="admin-lead-main">
            <div class="admin-card admin-card--lead">
                <div class="admin-card__head">
                    <h2><span class="material-symbols-outlined">forum</span> Inquiry message</h2>
                </div>
                <div class="admin-card__body">
                    <blockquote class="admin-lead-message">
                        <?= nl2br(kam_h($lead['message'] ?? 'No message provided.')) ?>
                    </blockquote>
                </div>
            </div>

            <div class="admin-lead-contact-grid">
                <a href="mailto:<?= kam_h($lead['email']) ?>" class="admin-lead-contact-card">
                    <span class="admin-lead-contact-card__icon admin-lead-contact-card__icon--mail">
                        <span class="material-symbols-outlined">mail</span>
                    </span>
                    <span class="admin-lead-contact-card__label">Email</span>
                    <span class="admin-lead-contact-card__value"><?= kam_h($lead['email']) ?></span>
                </a>
                <?php if ($lead['phone']): ?>
                    <a href="tel:<?= kam_h($phoneTel) ?>" class="admin-lead-contact-card">
                        <span class="admin-lead-contact-card__icon admin-lead-contact-card__icon--phone">
                            <span class="material-symbols-outlined">call</span>
                        </span>
                        <span class="admin-lead-contact-card__label">Phone</span>
                        <span class="admin-lead-contact-card__value"><?= kam_h($lead['phone']) ?></span>
                    </a>
                <?php endif; ?>
                <div class="admin-lead-contact-card admin-lead-contact-card--static">
                    <span class="admin-lead-contact-card__icon admin-lead-contact-card__icon--source">
                        <span class="material-symbols-outlined">language</span>
                    </span>
                    <span class="admin-lead-contact-card__label">Source</span>
                    <span class="admin-lead-contact-card__value"><?= kam_h($lead['source']) ?></span>
                </div>
                <?php if (!empty($lead['ip_address'])): ?>
                    <div class="admin-lead-contact-card admin-lead-contact-card--static">
                        <span class="admin-lead-contact-card__icon admin-lead-contact-card__icon--meta">
                            <span class="material-symbols-outlined">pin</span>
                        </span>
                        <span class="admin-lead-contact-card__label">IP address</span>
                        <span class="admin-lead-contact-card__value admin-lead-contact-card__value--mono"><?= kam_h($lead['ip_address']) ?></span>
                    </div>
                <?php endif; ?>
            </div>

            <div class="admin-card admin-card--lead">
                <div class="admin-card__head">
                    <h2>
                        <span class="material-symbols-outlined">sticky_note_2</span>
                        Internal notes
                        <?php if (count($notes) > 0): ?>
                            <span class="admin-lead-count"><?= count($notes) ?></span>
                        <?php endif; ?>
                    </h2>
                </div>
                <div class="admin-card__body">
                    <?php if (empty($notes)): ?>
                        <div class="admin-lead-empty">
                            <span class="material-symbols-outlined">edit_note</span>
                            <p>No notes yet. Add a follow-up after you contact this lead.</p>
                        </div>
                    <?php else: ?>
                        <div class="admin-lead-notes">
                            <?php foreach ($notes as $n): ?>
                                <article class="admin-lead-note">
                                    <div class="admin-lead-note__avatar"><?= kam_h(kam_initials($n['admin_name'] ?? 'S')) ?></div>
                                    <div class="admin-lead-note__body">
                                        <header class="admin-lead-note__head">
                                            <strong><?= kam_h($n['admin_name'] ?? 'System') ?></strong>
                                            <time><?= kam_h(date('M j, Y · g:i A', strtotime($n['created_at']))) ?></time>
                                        </header>
                                        <p><?= nl2br(kam_h($n['note'])) ?></p>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <form method="post" class="admin-lead-compose">
                        <input type="hidden" name="csrf" value="<?= kam_h($csrf) ?>"/>
                        <input type="hidden" name="action" value="note"/>
                        <label for="note" class="admin-lead-compose__label">Add a note</label>
                        <textarea id="note" name="note" required placeholder="Call summary, next steps, meeting outcome…" rows="3"></textarea>
                        <button type="submit" class="admin-btn admin-btn--primary admin-btn--sm">
                            <span class="material-symbols-outlined">send</span>
                            Post note
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="admin-lead-sidebar">
            <div class="admin-card admin-card--lead admin-pipeline-card">
                <div class="admin-card__head">
                    <h2><span class="material-symbols-outlined">account_tree</span> Pipeline</h2>
                </div>
                <div class="admin-card__body admin-pipeline-card__body">
                    <div class="admin-lead-pipeline-track" role="list" aria-label="Pipeline progress">
                        <?php
                        $statusOrder = array_flip(kam_lead_statuses());
                        $currentIdx = $statusOrder[$lead['status']] ?? 0;
                        foreach (kam_lead_statuses() as $s):
                            $stepIdx = $statusOrder[$s] ?? 0;
                            $stepClass = $lead['status'] === $s ? 'is-current' : ($stepIdx < $currentIdx ? 'is-done' : '');
                        ?>
                            <div class="admin-lead-pipeline-step <?= kam_h($stepClass) ?>" role="listitem">
                                <span class="admin-lead-pipeline-step__dot" aria-hidden="true"></span>
                                <span class="admin-lead-pipeline-step__label"><?= kam_h(kam_status_label($s)) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <form method="post" class="admin-lead-pipeline-form">
                        <input type="hidden" name="csrf" value="<?= kam_h($csrf) ?>"/>
                        <input type="hidden" name="action" value="update"/>
                        <div class="admin-form-group">
                            <label for="status">Status</label>
                            <select id="status" name="status" class="admin-input-modern">
                                <?php foreach (kam_lead_statuses() as $s): ?>
                                    <option value="<?= kam_h($s) ?>" <?= $lead['status'] === $s ? 'selected' : '' ?>><?= kam_h(kam_status_label($s)) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="admin-form-group">
                            <label for="priority">Priority</label>
                            <select id="priority" name="priority" class="admin-input-modern">
                                <?php foreach (['low', 'normal', 'high'] as $p): ?>
                                    <option value="<?= $p ?>" <?= $priority === $p ? 'selected' : '' ?>><?= ucfirst($p) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="admin-form-group">
                            <label for="assigned_to">Assigned to</label>
                            <select id="assigned_to" name="assigned_to" class="admin-input-modern">
                                <option value="">Unassigned</option>
                                <?php foreach ($admins as $admin): ?>
                                    <option value="<?= (int) $admin['id'] ?>" <?= (int) ($lead['assigned_to'] ?? 0) === (int) $admin['id'] ? 'selected' : '' ?>>
                                        <?= kam_h($admin['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="admin-btn admin-btn--primary admin-lead-save-btn">
                            <span class="material-symbols-outlined">save</span>
                            Save changes
                        </button>
                    </form>
                </div>
            </div>

            <?php if (!empty($activity)): ?>
                <div class="admin-card admin-card--lead admin-lead-activity-card">
                    <div class="admin-card__head">
                        <h2><span class="material-symbols-outlined">history</span> Activity</h2>
                    </div>
                    <div class="admin-card__body">
                        <ul class="admin-lead-timeline">
                            <?php foreach ($activity as $log): ?>
                                <li class="admin-lead-timeline__item">
                                    <span class="admin-lead-timeline__icon">
                                        <span class="material-symbols-outlined">radio_button_checked</span>
                                    </span>
                                    <div>
                                        <strong><?= kam_h(ucwords(str_replace('_', ' ', $log['action']))) ?></strong>
                                        <span class="admin-lead-timeline__meta">
                                            <?= kam_h($log['admin_name'] ?? 'System') ?>
                                            · <?= kam_h(date('M j, g:i A', strtotime($log['created_at']))) ?>
                                        </span>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>

            <div class="admin-card admin-card--danger admin-lead-danger">
                <div class="admin-card__body">
                    <div class="admin-lead-danger__head">
                        <span class="material-symbols-outlined">warning</span>
                        <div>
                            <h3>Delete lead</h3>
                            <p>Permanently remove this inquiry and all notes.</p>
                        </div>
                    </div>
                    <form method="post" onsubmit="return confirm('Delete this lead permanently?');">
                        <input type="hidden" name="csrf" value="<?= kam_h($csrf) ?>"/>
                        <input type="hidden" name="action" value="delete"/>
                        <button type="submit" class="admin-btn admin-btn--danger">
                            <span class="material-symbols-outlined">delete</span>
                            Delete lead
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
