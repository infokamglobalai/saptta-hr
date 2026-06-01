<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_once dirname(__DIR__) . '/includes/LeadRepository.php';

Auth::requireLogin();

$user = Auth::user();
$id = (int) ($_GET['id'] ?? 0);
$lead = $id > 0 ? LeadRepository::find($id) : null;

if (!$lead) {
    kam_redirect('leads.php');
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Auth::verifyCsrf($_POST['csrf'] ?? null)) {
        $error = 'Invalid session. Please try again.';
    } else {
        $action = $_POST['action'] ?? 'update';

        if ($action === 'note') {
            $note = trim((string) ($_POST['note'] ?? ''));
            if ($note !== '') {
                LeadRepository::addNote($id, $user['id'], $note);
                kam_log_activity($user['id'], 'lead', $id, 'note_added');
                $message = 'Note added.';
            }
        } else {
            $status = $_POST['status'] ?? $lead['status'];
            $priority = $_POST['priority'] ?? $lead['priority'];
            if (!in_array($status, kam_lead_statuses(), true)) {
                $status = $lead['status'];
            }
            LeadRepository::update($id, [
                'status' => $status,
                'priority' => in_array($priority, ['low', 'normal', 'high'], true) ? $priority : 'normal',
            ]);
            kam_log_activity($user['id'], 'lead', $id, 'updated', ['status' => $status]);
            $message = 'Lead updated.';
        }
        $lead = LeadRepository::find($id);
    }
}

$notes = LeadRepository::notes($id);
$csrf = Auth::csrfToken();

ob_start();
?>
<?php if ($message): ?><div class="admin-alert admin-alert--success"><?= kam_h($message) ?></div><?php endif; ?>
<?php if ($error): ?><div class="admin-alert admin-alert--error"><?= kam_h($error) ?></div><?php endif; ?>

<div class="admin-grid-2">
    <div>
        <div class="admin-card" style="margin-bottom:1.5rem">
            <div class="admin-card__head">
                <h2>Lead #<?= (int) $lead['id'] ?></h2>
                <a href="leads.php" class="admin-btn admin-btn--ghost admin-btn--sm">← Back</a>
            </div>
            <div style="padding:1.25rem">
                <dl class="admin-detail">
                    <dt>Name</dt>
                    <dd><?= kam_h($lead['name']) ?></dd>
                    <dt>Email</dt>
                    <dd><a href="mailto:<?= kam_h($lead['email']) ?>"><?= kam_h($lead['email']) ?></a></dd>
                    <dt>Phone</dt>
                    <dd><?= kam_h($lead['phone'] ?: '—') ?></dd>
                    <dt>Company</dt>
                    <dd><?= kam_h($lead['company'] ?: '—') ?></dd>
                    <dt>Inquiry type</dt>
                    <dd><?= kam_h(kam_inquiry_types()[$lead['inquiry_type']] ?? $lead['inquiry_type']) ?></dd>
                    <dt>Message</dt>
                    <dd style="white-space:pre-wrap"><?= kam_h($lead['message'] ?? '') ?></dd>
                    <dt>Source</dt>
                    <dd><?= kam_h($lead['source']) ?></dd>
                    <dt>Submitted</dt>
                    <dd><?= kam_h(date('M j, Y g:i A', strtotime($lead['created_at']))) ?></dd>
                </dl>
            </div>
        </div>

        <div class="admin-card">
            <div class="admin-card__head"><h2>Notes</h2></div>
            <div style="padding:1.25rem">
                <?php if (empty($notes)): ?>
                    <p style="color:var(--admin-muted);margin:0">No notes yet.</p>
                <?php else: ?>
                    <?php foreach ($notes as $n): ?>
                        <div class="admin-note">
                            <div class="admin-note__meta">
                                <?= kam_h($n['admin_name'] ?? 'System') ?> · <?= kam_h(date('M j, Y g:i A', strtotime($n['created_at']))) ?>
                            </div>
                            <?= nl2br(kam_h($n['note'])) ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                <form method="post" style="margin-top:1rem">
                    <input type="hidden" name="csrf" value="<?= kam_h($csrf) ?>"/>
                    <input type="hidden" name="action" value="note"/>
                    <div class="admin-form-group">
                        <label for="note">Add note</label>
                        <textarea id="note" name="note" required placeholder="Call summary, follow-up plan…"></textarea>
                    </div>
                    <button type="submit" class="admin-btn admin-btn--primary admin-btn--sm">Save note</button>
                </form>
            </div>
        </div>
    </div>

    <div>
        <div class="admin-card">
            <div class="admin-card__head"><h2>CRM status</h2></div>
            <div style="padding:1.25rem">
                <form method="post">
                    <input type="hidden" name="csrf" value="<?= kam_h($csrf) ?>"/>
                    <input type="hidden" name="action" value="update"/>
                    <div class="admin-form-group">
                        <label for="status">Pipeline status</label>
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
                                <option value="<?= $p ?>" <?= $lead['priority'] === $p ? 'selected' : '' ?>><?= ucfirst($p) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="admin-btn admin-btn--primary">Update lead</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
$pageTitle = 'Lead #' . $id;
$activeNav = 'leads';
require __DIR__ . '/includes/layout.php';
