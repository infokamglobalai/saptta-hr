<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_once dirname(__DIR__) . '/includes/admin_app.php';
require_once dirname(__DIR__) . '/includes/LeadRepository.php';

kam_admin_app_boot();

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    kam_redirect('leads.php');
}

kam_admin_render(function () use ($id): void {
    $user = Auth::user();
    $lead = LeadRepository::find($id);

    if (!$lead) {
        kam_redirect('leads.php');
    }

    $message = '';
    $error = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!Auth::verifyCsrf($_POST['csrf'] ?? null)) {
            $error = 'Invalid session. Please try again.';
        } elseif (($_POST['action'] ?? '') === 'delete') {
            kam_log_activity($user['id'], 'lead', $id, 'deleted', ['email' => $lead['email']]);
            LeadRepository::delete($id);
            kam_redirect('leads.php?deleted=1');
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
                $assigned = $_POST['assigned_to'] ?? null;
                $assignedTo = ($assigned === '' || $assigned === null) ? null : (int) $assigned;

                LeadRepository::update($id, [
                    'status' => $status,
                    'priority' => in_array($priority, ['low', 'normal', 'high'], true) ? $priority : 'normal',
                    'assigned_to' => $assignedTo,
                ]);
                kam_log_activity($user['id'], 'lead', $id, 'updated', [
                    'status' => $status,
                    'priority' => $priority,
                ]);
                $message = 'Lead updated.';
            }
            $lead = LeadRepository::find($id);
        }
    }

    $notes = LeadRepository::notes($id);
    $activity = LeadRepository::activity($id);
    $admins = LeadRepository::adminsForAssign();
    $csrf = Auth::csrfToken();

    ob_start();
    include __DIR__ . '/views/lead-detail.php';
    $content = ob_get_clean();
    $pageTitle = 'Lead #' . $id;
    $pageSubtitle = kam_h($lead['name']) . ' · ' . kam_h($lead['email']);
    $activeNav = 'leads';
    require __DIR__ . '/includes/layout.php';
});
