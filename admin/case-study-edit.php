<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_once dirname(__DIR__) . '/includes/admin_app.php';
require_once dirname(__DIR__) . '/includes/CmsRepository.php';

kam_admin_app_boot();

$id = (int) ($_GET['id'] ?? 0);
$item = $id > 0 ? CmsRepository::caseFind($id) : null;
$message = isset($_GET['saved']) ? 'Case study saved.' : '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Auth::verifyCsrf($_POST['csrf'] ?? null)) {
        $error = 'Invalid session.';
    } else {
        $newId = CmsRepository::caseSave($id ?: null, [
            'title' => trim((string) $_POST['title']),
            'slug' => trim((string) ($_POST['slug'] ?? '')),
            'industry' => trim((string) ($_POST['industry'] ?? 'General')),
            'summary' => trim((string) ($_POST['summary'] ?? '')),
            'challenge' => trim((string) $_POST['challenge']),
            'solution' => trim((string) $_POST['solution']),
            'outcome' => trim((string) $_POST['outcome']),
            'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
            'status' => $_POST['status'] === 'published' ? 'published' : 'draft',
            'sort_order' => (int) ($_POST['sort_order'] ?? 0),
        ]);
        kam_redirect('case-study-edit.php?id=' . $newId . '&saved=1');
    }
}

if ($id && !$item) {
    kam_redirect('case-studies.php');
}

$csrf = Auth::csrfToken();

kam_admin_render(function () use ($item, $message, $error, $csrf, $id): void {
    ob_start();
    ?>
    <?php if ($message): ?><div class="admin-alert admin-alert--success"><?= kam_h($message) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="admin-alert admin-alert--error"><?= kam_h($error) ?></div><?php endif; ?>
    <form method="post" class="admin-card">
        <div class="admin-card__head">
            <h2><?= $id ? 'Edit case study' : 'New case study' ?></h2>
            <a href="case-studies.php" class="admin-btn admin-btn--ghost admin-btn--sm">Back to list</a>
        </div>
        <div class="admin-card__body">
            <input type="hidden" name="csrf" value="<?= kam_h($csrf) ?>"/>
            <div class="admin-form-group">
                <label for="title">Title *</label>
                <input id="title" name="title" required value="<?= kam_h($item['title'] ?? '') ?>"/>
            </div>
            <div class="admin-form-group">
                <label for="industry">Industry</label>
                <input id="industry" name="industry" value="<?= kam_h($item['industry'] ?? '') ?>"/>
            </div>
            <div class="admin-form-group">
                <label for="summary">Short summary</label>
                <input id="summary" name="summary" value="<?= kam_h($item['summary'] ?? '') ?>"/>
            </div>
            <?php foreach (['challenge' => 'Challenge', 'solution' => 'Solution', 'outcome' => 'Outcome'] as $f => $label): ?>
                <div class="admin-form-group">
                    <label for="<?= $f ?>"><?= kam_h($label) ?> *</label>
                    <textarea id="<?= $f ?>" name="<?= $f ?>" rows="3" required><?= kam_h($item[$f] ?? '') ?></textarea>
                </div>
            <?php endforeach; ?>
            <div class="admin-form-row">
                <div class="admin-form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="draft" <?= ($item['status'] ?? 'draft') === 'draft' ? 'selected' : '' ?>>Draft</option>
                        <option value="published" <?= ($item['status'] ?? '') === 'published' ? 'selected' : '' ?>>Published</option>
                    </select>
                </div>
                <div class="admin-form-group">
                    <label for="sort_order">Sort order</label>
                    <input type="number" id="sort_order" name="sort_order" value="<?= (int) ($item['sort_order'] ?? 0) ?>"/>
                </div>
            </div>
            <label class="admin-checkbox"><input type="checkbox" name="is_featured" <?= !empty($item['is_featured']) ? 'checked' : '' ?>/> Featured</label>
            <button type="submit" class="admin-btn admin-btn--primary" style="margin-top:1.25rem">Save case study</button>
        </div>
    </form>
    <?php
    $content = ob_get_clean();
    $pageTitle = $id ? 'Edit case study' : 'New case study';
    $pageSubtitle = 'Shown on case-studies.html';
    $activeNav = 'cases';
    require __DIR__ . '/includes/layout.php';
});
