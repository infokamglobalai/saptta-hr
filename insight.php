<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/CmsRepository.php';

$slug = trim((string) ($_GET['slug'] ?? ''));
$article = $slug !== '' ? CmsRepository::insightBySlug($slug) : null;

if (!$article) {
    http_response_code(404);
    $pageTitle = 'Article not found';
    $body = '<p class="text-lg">This insight is not available. <a href="insights.html">Browse all insights</a>.</p>';
} else {
    $pageTitle = $article['title'];
    $body = $article['body_html'] ?? '<p>' . htmlspecialchars($article['excerpt'] ?? '', ENT_QUOTES) . '</p>';
}
?>
<!DOCTYPE html>
<html class="scroll-smooth light" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES) ?> | KAM Global HR</title>
    <link rel="icon" href="assets/img/favicon.ico" sizes="any"/>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200"/>
    <script src="assets/js/tailwind-config.js"></script>
    <link href="assets/css/global.css" rel="stylesheet"/>
    <style>
        .insight-article { max-width: 48rem; margin: 0 auto; padding: 6rem 1.5rem 4rem; }
        .insight-article__body { line-height: 1.75; color: #334155; }
        .insight-article__body h2 { margin-top: 2rem; color: #001f3f; font-weight: 800; }
        .insight-article__body p { margin-bottom: 1rem; }
    </style>
</head>
<body class="site-page bg-surface text-on-surface">
<article class="insight-article">
    <a href="insights.html" class="inline-flex items-center gap-1 text-secondary font-semibold mb-6">
        <span class="material-symbols-outlined text-[18px]">arrow_back</span> Back to Insights
    </a>
    <?php if ($article): ?>
        <span class="insights-badge mb-3 inline-block"><?= htmlspecialchars(ucfirst(str_replace('-', ' ', $article['category'])), ENT_QUOTES) ?></span>
        <h1 class="font-headline-lg text-primary mb-4"><?= htmlspecialchars($article['title'], ENT_QUOTES) ?></h1>
        <?php if (!empty($article['excerpt'])): ?>
            <p class="text-on-surface-variant text-lg mb-8"><?= htmlspecialchars($article['excerpt'], ENT_QUOTES) ?></p>
        <?php endif; ?>
        <?php if (!empty($article['image_url'])): ?>
            <img src="<?= htmlspecialchars($article['image_url'], ENT_QUOTES) ?>" alt="" class="rounded-xl mb-10 w-full max-h-96 object-cover" loading="lazy"/>
        <?php endif; ?>
    <?php else: ?>
        <h1 class="font-headline-lg text-primary mb-4">Article not found</h1>
    <?php endif; ?>
    <div class="insight-article__body"><?= $body ?></div>
</article>
<script src="assets/js/main.js"></script>
</body>
</html>
