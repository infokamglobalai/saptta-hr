<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_once dirname(__DIR__) . '/includes/admin_app.php';
require_once dirname(__DIR__) . '/includes/JobRepository.php';

kam_admin_app_boot();

$id = (int) ($_GET['id'] ?? 0);
$item = $id > 0 ? JobRepository::jobFind($id) : null;
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Auth::verifyCsrf($_POST['csrf'] ?? null)) {
        $error = 'Invalid session token.';
    } else {
        try {
            $newId = JobRepository::jobSave($id ?: null, [
                'title' => trim((string) $_POST['title']),
                'slug' => trim((string) ($_POST['slug'] ?? '')),
                'category' => trim((string) ($_POST['category'] ?? 'Engineering')),
                'job_type' => trim((string) ($_POST['job_type'] ?? 'Full-time')),
                'location' => trim((string) ($_POST['location'] ?? 'India / GCC')),
                'image_url' => trim((string) ($_POST['image_url'] ?? '')),
                'experience_required' => trim((string) ($_POST['experience_required'] ?? '')),
                'salary_range' => trim((string) ($_POST['salary_range'] ?? '')),
                'vacancies' => max(1, (int) ($_POST['vacancies'] ?? 1)),
                'summary' => trim((string) ($_POST['summary'] ?? '')),
                'description' => (string) ($_POST['description'] ?? ''),
                'requirements' => (string) ($_POST['requirements'] ?? ''),
                'status' => in_array($_POST['status'] ?? '', ['active', 'closed', 'draft'], true) ? $_POST['status'] : 'active',
                'sort_order' => (int) ($_POST['sort_order'] ?? 0),
                'deadline' => !empty($_POST['deadline']) ? $_POST['deadline'] : null,
            ]);
            kam_redirect('job-edit.php?id=' . $newId . '&saved=1');
        } catch (Throwable $e) {
            $error = $e->getMessage();
        }
    }
}

if (isset($_GET['saved'])) {
    $message = 'Job opening saved successfully.';
    $id = (int) ($_GET['id'] ?? $id);
    $item = JobRepository::jobFind($id);
}

$csrf = Auth::csrfToken();
$categories = JobRepository::categories();

kam_admin_render(function () use ($item, $message, $error, $csrf, $categories, $id): void {
    ob_start();
    ?>
    <?php if ($message): ?><div class="admin-alert admin-alert--success"><?= kam_h($message) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="admin-alert admin-alert--error"><?= kam_h($error) ?></div><?php endif; ?>

    <form method="post" class="admin-card">
        <div class="admin-card__head">
            <h2><?= $id ? 'Edit job vacancy' : 'Post new job opening' ?></h2>
            <a href="jobs.php" class="admin-btn admin-btn--ghost admin-btn--sm">Back to list</a>
        </div>
        <div class="admin-card__body">
            <input type="hidden" name="csrf" value="<?= kam_h($csrf) ?>"/>

            <div class="admin-grid-2">
                <div class="admin-form-group">
                    <label for="title">Job Title *</label>
                    <input id="title" name="title" required value="<?= kam_h($item['title'] ?? '') ?>" placeholder="e.g. Mechanical Site Engineer (MEP)"/>
                </div>
                <div class="admin-form-group">
                    <label for="slug">URL Slug (optional)</label>
                    <input id="slug" name="slug" value="<?= kam_h($item['slug'] ?? '') ?>" placeholder="auto-generated-if-blank"/>
                </div>
            </div>

            <div class="admin-grid-2">
                <div class="admin-form-group">
                    <label for="image_url">Job Poster / Banner Image URL</label>
                    <input id="image_url" name="image_url" value="<?= kam_h($item['image_url'] ?? '') ?>" placeholder="e.g. assets/img/jobs/iti-dubai.png or uploads/job-banner.jpg"/>
                    <?php if (!empty($item['image_url'])): ?>
                        <div style="margin-top:8px;">
                            <img src="../<?= kam_h($item['image_url']) ?>" alt="Preview" style="max-height:80px;border-radius:8px;border:1px solid #cbd5e1;object-fit:cover;"/>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="admin-form-group">
                    <label for="category">Profile Category *</label>
                    <select id="category" name="category" required>
                        <?php foreach ($categories as $k => $c): ?>
                            <option value="<?= kam_h($k) ?>" <?= ($item['category'] ?? '') === $k ? 'selected' : '' ?>><?= kam_h($c) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="admin-grid-2">
                <div class="admin-form-group">
                    <label for="job_type">Job Type</label>
                    <input id="job_type" name="job_type" value="<?= kam_h($item['job_type'] ?? 'Full-time') ?>" placeholder="e.g. Full-time / Contract / Overseas"/>
                </div>
                <div class="admin-form-group">
                    <label for="location">Location *</label>
                    <input id="location" name="location" required value="<?= kam_h($item['location'] ?? 'India / GCC') ?>" placeholder="e.g. Bengaluru, India or Kuwait"/>
                </div>
            </div>

            <div class="admin-grid-3" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;">
                <div class="admin-form-group">
                    <label for="experience_required">Experience Required</label>
                    <input id="experience_required" name="experience_required" value="<?= kam_h($item['experience_required'] ?? '') ?>" placeholder="e.g. 2 - 5 Years / Fresher"/>
                </div>
                <div class="admin-form-group">
                    <label for="salary_range">Salary / Package</label>
                    <input id="salary_range" name="salary_range" value="<?= kam_h($item['salary_range'] ?? '') ?>" placeholder="e.g. Competitive / ₹30k - ₹45k"/>
                </div>
                <div class="admin-form-group">
                    <label for="vacancies">Vacancies (Nos.)</label>
                    <input type="number" id="vacancies" name="vacancies" min="1" value="<?= (int) ($item['vacancies'] ?? 1) ?>"/>
                </div>
            </div>

            <div class="admin-form-group">
                <label for="summary">Short Summary (displayed on job cards)</label>
                <textarea id="summary" name="summary" rows="2" placeholder="Brief overview of the role and key scope..."><?= kam_h($item['summary'] ?? '') ?></textarea>
            </div>

            <div class="admin-form-group">
                <label for="description">Job Scope &amp; Responsibilities *</label>
                <textarea id="description" name="description" rows="5" required placeholder="Detailed job scope, tasks, and site responsibilities..."><?= kam_h($item['description'] ?? '') ?></textarea>
            </div>

            <div class="admin-form-group">
                <label for="requirements">Requirements &amp; Qualifications (one per line or bullets)</label>
                <textarea id="requirements" name="requirements" rows="4" placeholder="• Qualification: Diploma / ITI / BE&#10;• Experience: 3+ years&#10;• Licenses: Wireman, GCC License, etc."><?= kam_h($item['requirements'] ?? '') ?></textarea>
            </div>

            <div class="admin-grid-3" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;">
                <div class="admin-form-group">
                    <label for="status">Publication Status</label>
                    <select id="status" name="status">
                        <option value="active" <?= ($item['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active (Visible on Careers Page)</option>
                        <option value="closed" <?= ($item['status'] ?? '') === 'closed' ? 'selected' : '' ?>>Closed</option>
                        <option value="draft" <?= ($item['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option>
                    </select>
                </div>
                <div class="admin-form-group">
                    <label for="sort_order">Display Priority (Sort Order)</label>
                    <input type="number" id="sort_order" name="sort_order" value="<?= (int) ($item['sort_order'] ?? 0) ?>"/>
                </div>
                <div class="admin-form-group">
                    <label for="deadline">Application Deadline</label>
                    <input type="date" id="deadline" name="deadline" value="<?= kam_h($item['deadline'] ?? '') ?>"/>
                </div>
            </div>
        </div>
        <div class="admin-card__footer" style="padding:16px 24px;border-top:1px solid #f1f5f9;display:flex;gap:12px;">
            <button type="submit" class="admin-btn admin-btn--primary">Save Job Vacancy</button>
            <a href="jobs.php" class="admin-btn admin-btn--ghost">Cancel</a>
        </div>
    </form>
    <?php
    $content = ob_get_clean();
    $pageTitle = $id ? 'Edit Job' : 'Post Job';
    $activeNav = 'jobs';
    require __DIR__ . '/includes/layout.php';
});
