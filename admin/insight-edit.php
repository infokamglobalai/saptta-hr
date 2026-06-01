<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_once dirname(__DIR__) . '/includes/admin_app.php';
require_once dirname(__DIR__) . '/includes/CmsRepository.php';
require_once dirname(__DIR__) . '/includes/InsightMedia.php';

kam_admin_app_boot();

$id = (int) ($_GET['id'] ?? 0);
$item = $id > 0 ? CmsRepository::insightFind($id) : null;
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Auth::verifyCsrf($_POST['csrf'] ?? null)) {
        $error = 'Invalid session.';
    } else {
        try {
            $imageUrl = trim((string) ($_POST['image_url'] ?? ''));
            $previousImage = $item['image_url'] ?? null;

            if (!empty($_FILES['image_file']['name'])) {
                $uploaded = InsightMedia::uploadFromRequest($_FILES['image_file']);
                if ($uploaded !== null) {
                    if ($previousImage && str_starts_with((string) $previousImage, 'assets/uploads/insights/')) {
                        InsightMedia::deleteFile((string) $previousImage);
                    }
                    $imageUrl = $uploaded;
                }
            } elseif (isset($_POST['remove_image'])) {
                InsightMedia::deleteFile($previousImage ? (string) $previousImage : null);
                $imageUrl = '';
            }

            $newId = CmsRepository::insightSave($id ?: null, [
                'title' => trim((string) $_POST['title']),
                'slug' => trim((string) ($_POST['slug'] ?? '')),
                'excerpt' => trim((string) ($_POST['excerpt'] ?? '')),
                'body_html' => (string) ($_POST['body_html'] ?? ''),
                'category' => trim((string) ($_POST['category'] ?? 'general')),
                'content_type' => $_POST['content_type'] === 'report' ? 'report' : 'article',
                'image_url' => $imageUrl !== '' ? $imageUrl : null,
                'download_url' => trim((string) ($_POST['download_url'] ?? '')),
                'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
                'status' => $_POST['status'] === 'published' ? 'published' : 'draft',
                'sort_order' => (int) ($_POST['sort_order'] ?? 0),
            ]);
            kam_redirect('insight-edit.php?id=' . $newId . '&saved=1');
        } catch (Throwable $e) {
            $error = $e->getMessage();
        }
    }
}

if (isset($_GET['saved'])) {
    $message = 'Insight saved.';
    $id = (int) ($_GET['id'] ?? $id);
    $item = CmsRepository::insightFind($id);
}

$csrf = Auth::csrfToken();
$categories = ['recruitment', 'hr-advisory', 'payroll', 'executive-search', 'industry-reports', 'general'];

kam_admin_render(function () use ($item, $message, $error, $csrf, $categories, $id): void {
    ob_start();
    ?>
    <?php if ($message): ?><div class="admin-alert admin-alert--success"><?= kam_h($message) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="admin-alert admin-alert--error"><?= kam_h($error) ?></div><?php endif; ?>
    <form method="post" class="admin-card" enctype="multipart/form-data">
        <div class="admin-card__head">
            <h2><?= $id ? 'Edit insight' : 'New insight' ?></h2>
            <a href="insights.php" class="admin-btn admin-btn--ghost admin-btn--sm">Back to list</a>
        </div>
        <div class="admin-card__body">
            <input type="hidden" name="csrf" value="<?= kam_h($csrf) ?>"/>
            <div class="admin-form-group">
                <label for="title">Title *</label>
                <input id="title" name="title" required value="<?= kam_h($item['title'] ?? '') ?>"/>
            </div>
            <div class="admin-form-group">
                <label for="slug">URL slug (optional)</label>
                <input id="slug" name="slug" placeholder="auto-from-title" value="<?= kam_h($item['slug'] ?? '') ?>"/>
            </div>
            <div class="admin-form-group">
                <label for="excerpt">Excerpt</label>
                <textarea id="excerpt" name="excerpt" rows="2"><?= kam_h($item['excerpt'] ?? '') ?></textarea>
            </div>
            <div class="admin-form-group">
                <label for="body_html">Body (HTML allowed)</label>
                <textarea id="body_html" name="body_html" rows="12"><?= kam_h($item['body_html'] ?? '') ?></textarea>
            </div>
            <div class="admin-grid-2">
                <div class="admin-form-group">
                    <label for="category">Category</label>
                    <select id="category" name="category">
                        <?php foreach ($categories as $c): ?>
                            <option value="<?= kam_h($c) ?>" <?= ($item['category'] ?? '') === $c ? 'selected' : '' ?>><?= kam_h($c) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="admin-form-group">
                    <label for="content_type">Content type</label>
                    <select id="content_type" name="content_type">
                        <option value="article" <?= ($item['content_type'] ?? 'article') === 'article' ? 'selected' : '' ?>>Article</option>
                        <option value="report" <?= ($item['content_type'] ?? '') === 'report' ? 'selected' : '' ?>>Report / download</option>
                    </select>
                </div>
            </div>
            <div class="admin-form-group admin-insight-media">
                <label>Cover image</label>
                <p class="admin-form-hint">Shown on insight cards and the article page. JPG, PNG, WebP, or GIF — max 5 MB.</p>
                <?php if (!empty($item['image_url'])): ?>
                    <div class="admin-insight-media__preview">
                        <img src="../<?= kam_h($item['image_url']) ?>" alt="Current cover preview"/>
                    </div>
                    <label class="admin-checkbox admin-insight-media__remove">
                        <input type="checkbox" name="remove_image" value="1"/> Remove current image
                    </label>
                <?php endif; ?>
                <div class="admin-form-group">
                    <label for="image_file">Upload photo</label>
                    <input id="image_file" name="image_file" type="file" accept="image/jpeg,image/png,image/webp,image/gif"/>
                </div>
                <div class="admin-form-group">
                    <label for="image_url">Or image URL / path</label>
                    <input id="image_url" name="image_url" placeholder="assets/img/example.png or https://…" value="<?= kam_h($item['image_url'] ?? '') ?>"/>
                </div>
            </div>
            <div class="admin-form-group">
                <label for="download_url">Download URL (reports)</label>
                <input id="download_url" name="download_url" value="<?= kam_h($item['download_url'] ?? '') ?>"/>
            </div>
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
            <label class="admin-checkbox"><input type="checkbox" name="is_featured" <?= !empty($item['is_featured']) ? 'checked' : '' ?>/> Featured on insights page</label>
            <button type="submit" class="admin-btn admin-btn--primary" style="margin-top:1.25rem">
                <span class="material-symbols-outlined">save</span> Save insight
            </button>
        </div>
    </form>
    <?php
    $content = ob_get_clean();
    $pageTitle = $id ? 'Edit insight' : 'New insight';
    $pageSubtitle = $item['slug'] ?? '';
    $activeNav = 'insights';
    require __DIR__ . '/includes/layout.php';
});
