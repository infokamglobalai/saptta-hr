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
        CmsRepository::testimonialDelete((int) $_POST['delete_id']);
        $message = 'Testimonial deleted.';
        $editId = 0;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_testimonial'])) {
    if (!Auth::verifyCsrf($_POST['csrf'] ?? null)) {
        $error = 'Invalid session.';
    } else {
        $id = (int) ($_POST['id'] ?? 0) ?: null;
        CmsRepository::testimonialSave($id, [
            'name' => trim((string) $_POST['name']),
            'role_title' => trim((string) ($_POST['role_title'] ?? '')),
            'company' => trim((string) ($_POST['company'] ?? '')),
            'quote' => trim((string) $_POST['quote']),
            'image_url' => trim((string) ($_POST['image_url'] ?? '')),
            'sort_order' => (int) ($_POST['sort_order'] ?? 0),
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
        ]);
        $message = 'Testimonial saved.';
        $editId = 0;
    }
}

$items = CmsRepository::testimonialsAll();
$editing = $editId > 0 ? CmsRepository::testimonialFind($editId) : null;
$csrf = Auth::csrfToken();

kam_admin_render(function () use ($items, $editing, $message, $error, $csrf, $editId): void {
    ob_start();
    ?>
    <?php if ($message): ?><div class="admin-alert admin-alert--success"><?= kam_h($message) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="admin-alert admin-alert--error"><?= kam_h($error) ?></div><?php endif; ?>
    <p class="admin-page-hint">The first active testimonial appears on the home page client quote section.</p>
    <div class="admin-grid-2">
        <div class="admin-card">
            <div class="admin-card__head"><h2><span class="material-symbols-outlined">format_quote</span> Testimonials</h2></div>
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead><tr><th>Client</th><th>Order</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($items as $t): ?>
                        <tr>
                            <td>
                                <strong><?= kam_h($t['name']) ?></strong>
                                <?php if (!empty($t['company'])): ?><br/><small><?= kam_h($t['company']) ?></small><?php endif; ?>
                            </td>
                            <td><?= (int) $t['sort_order'] ?></td>
                            <td><?= (int) $t['is_active'] ? 'Active' : 'Hidden' ?></td>
                            <td><a href="testimonials.php?edit=<?= (int) $t['id'] ?>" class="admin-btn admin-btn--ghost admin-btn--sm">Edit</a></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="admin-card">
            <div class="admin-card__head"><h2><?= $editing ? 'Edit testimonial' : 'Add testimonial' ?></h2></div>
            <div class="admin-card__body">
                <form method="post">
                    <input type="hidden" name="csrf" value="<?= kam_h($csrf) ?>"/>
                    <input type="hidden" name="save_testimonial" value="1"/>
                    <input type="hidden" name="id" value="<?= (int) ($editing['id'] ?? 0) ?>"/>
                    <div class="admin-form-group">
                        <label for="name">Name / attribution</label>
                        <input id="name" name="name" required value="<?= kam_h($editing['name'] ?? '') ?>"/>
                    </div>
                    <div class="admin-form-group">
                        <label for="role_title">Role title</label>
                        <input id="role_title" name="role_title" value="<?= kam_h($editing['role_title'] ?? '') ?>"/>
                    </div>
                    <div class="admin-form-group">
                        <label for="company">Company</label>
                        <input id="company" name="company" value="<?= kam_h($editing['company'] ?? '') ?>"/>
                    </div>
                    <div class="admin-form-group">
                        <label for="quote">Quote</label>
                        <textarea id="quote" name="quote" rows="4" required><?= kam_h($editing['quote'] ?? '') ?></textarea>
                    </div>
                    <div class="admin-form-group">
                        <label for="sort_order">Sort order</label>
                        <input id="sort_order" name="sort_order" type="number" value="<?= (int) ($editing['sort_order'] ?? 0) ?>"/>
                    </div>
                    <label class="admin-check">
                        <input type="checkbox" name="is_active" value="1" <?= !isset($editing['is_active']) || (int) $editing['is_active'] ? 'checked' : '' ?>/>
                        Active on website
                    </label>
                    <div class="admin-form-actions">
                        <button type="submit" class="admin-btn admin-btn--primary">Save</button>
                        <?php if ($editing): ?>
                            <button type="submit" name="delete_id" value="<?= (int) $editing['id'] ?>" class="admin-btn admin-btn--danger" onclick="return confirm('Delete this testimonial?');">Delete</button>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php
    $content = ob_get_clean();
    $pageTitle = 'Testimonials';
    $pageSubtitle = count($items) . ' testimonial(s)';
    $activeNav = 'testimonials';
    require __DIR__ . '/includes/layout.php';
});
