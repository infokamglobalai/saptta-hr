<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_once dirname(__DIR__) . '/includes/admin_app.php';
require_once dirname(__DIR__) . '/includes/CmsRepository.php';

kam_admin_app_boot();

$message = '';
$error = '';
$editId = (int) ($_GET['edit'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    if (Auth::verifyCsrf($_POST['csrf'] ?? null)) {
        CmsRepository::officeDelete((int) $_POST['delete_id']);
        $message = 'Office deleted.';
        $editId = 0;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_office'])) {
    if (!Auth::verifyCsrf($_POST['csrf'] ?? null)) {
        $error = 'Invalid session.';
    } else {
        $id = (int) ($_POST['id'] ?? 0) ?: null;
        CmsRepository::officeSave($id, [
            'title' => trim((string) $_POST['title']),
            'address_line1' => trim((string) $_POST['address_line1']),
            'address_line2' => trim((string) ($_POST['address_line2'] ?? '')),
            'phone' => trim((string) ($_POST['phone'] ?? '')),
            'email' => trim((string) ($_POST['email'] ?? '')),
            'map_url' => trim((string) ($_POST['map_url'] ?? '')),
            'sort_order' => (int) ($_POST['sort_order'] ?? 0),
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
        ]);
        $message = 'Office saved.';
        $editId = 0;
    }
}

$offices = CmsRepository::officesAll();
$editing = $editId > 0 ? CmsRepository::officeFind($editId) : null;
$csrf = Auth::csrfToken();

kam_admin_render(function () use ($offices, $editing, $message, $error, $csrf, $editId): void {
    ob_start();
    ?>
    <?php if ($message): ?><div class="admin-alert admin-alert--success"><?= kam_h($message) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="admin-alert admin-alert--error"><?= kam_h($error) ?></div><?php endif; ?>
    <div class="admin-grid-2">
        <div class="admin-card">
            <div class="admin-card__head"><h2><span class="material-symbols-outlined">location_on</span> Offices</h2></div>
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead><tr><th>Office</th><th>Order</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($offices as $o): ?>
                        <tr>
                            <td><strong><?= kam_h($o['title']) ?></strong><br/><small><?= kam_h($o['address_line1']) ?></small></td>
                            <td><?= (int) $o['sort_order'] ?></td>
                            <td><?= (int) $o['is_active'] ? 'Active' : 'Hidden' ?></td>
                            <td><a href="offices.php?edit=<?= (int) $o['id'] ?>" class="admin-btn admin-btn--ghost admin-btn--sm">Edit</a></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="admin-card">
            <div class="admin-card__head"><h2><?= $editing ? 'Edit office' : 'Add office' ?></h2></div>
            <div class="admin-card__body">
                <form method="post">
                    <input type="hidden" name="csrf" value="<?= kam_h($csrf) ?>"/>
                    <input type="hidden" name="save_office" value="1"/>
                    <input type="hidden" name="id" value="<?= (int) ($editing['id'] ?? 0) ?>"/>
                    <?php foreach (['title' => 'Title', 'address_line1' => 'Address line 1', 'address_line2' => 'Address line 2', 'phone' => 'Phone', 'email' => 'Email', 'map_url' => 'Google Maps URL'] as $f => $label): ?>
                        <div class="admin-form-group">
                            <label for="<?= $f ?>"><?= kam_h($label) ?></label>
                            <input id="<?= $f ?>" name="<?= $f ?>" value="<?= kam_h($editing[$f] ?? '') ?>"/>
                        </div>
                    <?php endforeach; ?>
                    <div class="admin-form-group">
                        <label for="sort_order">Sort order</label>
                        <input type="number" id="sort_order" name="sort_order" value="<?= (int) ($editing['sort_order'] ?? 0) ?>"/>
                    </div>
                    <label class="admin-checkbox"><input type="checkbox" name="is_active" <?= !$editing || (int) $editing['is_active'] ? 'checked' : '' ?>/> Active on website</label>
                    <button type="submit" class="admin-btn admin-btn--primary" style="margin-top:1rem">Save office</button>
                    <?php if ($editing): ?>
                        <button type="submit" name="delete_id" value="<?= (int) $editing['id'] ?>" class="admin-btn admin-btn--ghost" style="margin-left:0.5rem" onclick="return confirm('Delete this office?')">Delete</button>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>
    <?php
    $content = ob_get_clean();
    $pageTitle = 'Offices';
    $pageSubtitle = count($offices) . ' location(s)';
    $activeNav = 'offices';
    require __DIR__ . '/includes/layout.php';
});
