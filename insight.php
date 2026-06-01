<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/CmsRepository.php';

$slug = trim((string) ($_GET['slug'] ?? ''));
$article = $slug !== '' ? CmsRepository::insightBySlug($slug) : null;
$related = [];

if ($article) {
    $allArticles = CmsRepository::insightsPublic('article', 8);
    foreach ($allArticles as $item) {
        if ($item['slug'] !== $slug) {
            $related[] = $item;
        }
        if (count($related) >= 3) {
            break;
        }
    }
}

$pageTitle = $article ? (string) $article['title'] : 'Article not found';
$metaDesc = $article
    ? (string) ($article['excerpt'] ?? '')
    : 'This insight is not available.';
$isReport = $article && ($article['content_type'] ?? '') === 'report';
$downloadUrl = $isReport ? trim((string) ($article['download_url'] ?? '')) : '';
$categoryLabel = $article
    ? ucwords(str_replace('-', ' ', (string) $article['category']))
    : '';

if (!$article) {
    http_response_code(404);
    $body = '<p class="text-lg">This insight is not available. <a href="insights.html">Browse all insights</a>.</p>';
} else {
    $body = $article['body_html'] ?? '<p>' . htmlspecialchars($article['excerpt'] ?? '', ENT_QUOTES) . '</p>';
}
?>
<!DOCTYPE html>
<html class="scroll-smooth light" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES) ?> | KAM Global HR</title>
    <meta name="description" content="<?= htmlspecialchars($metaDesc, ENT_QUOTES) ?>"/>
    <link rel="canonical" href="https://www.kamglobalhr.com/insight.php?slug=<?= rawurlencode($slug) ?>"/>
    <link rel="icon" href="assets/img/favicon.ico" sizes="any"/>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200"/>
    <script src="assets/js/tailwind-config.js"></script>
    <link href="assets/css/global.css" rel="stylesheet"/>
</head>
<body class="site-page bg-surface text-on-surface">
<header class="insight-page-header" role="banner">
    <div class="insight-page-header__inner">
        <a href="index.html" aria-label="KAM Global HR home">
            <img src="assets/img/logo.png" alt="KAM Global HR" class="insight-page-header__logo" width="180" height="64"/>
        </a>
        <nav class="insight-page-header__nav" aria-label="Article navigation">
            <a href="insights.html">Insights</a>
            <a href="contact.html" class="page-btn page-btn--primary">Contact us</a>
        </nav>
    </div>
</header>

<article class="insight-article">
    <a href="insights.html" class="inline-flex items-center gap-1 text-secondary font-semibold mb-6">
        <span class="material-symbols-outlined text-[18px]">arrow_back</span> Back to Insights
    </a>
    <?php if ($article): ?>
        <span class="insights-badge mb-3 inline-block"><?= htmlspecialchars($categoryLabel, ENT_QUOTES) ?></span>
        <h1 class="font-headline-lg text-primary mb-4"><?= htmlspecialchars($article['title'], ENT_QUOTES) ?></h1>
        <?php if ($metaDesc !== ''): ?>
            <p class="text-on-surface-variant text-lg mb-8"><?= htmlspecialchars($metaDesc, ENT_QUOTES) ?></p>
        <?php endif; ?>
        <?php if (!empty($article['image_url'])): ?>
            <img src="<?= htmlspecialchars($article['image_url'], ENT_QUOTES) ?>" alt="" class="rounded-xl mb-10 w-full max-h-96 object-cover" loading="lazy"/>
        <?php endif; ?>
        <?php if ($isReport): ?>
            <div class="insight-article__actions">
                <?php if ($downloadUrl !== ''): ?>
                    <a href="<?= htmlspecialchars($downloadUrl, ENT_QUOTES) ?>" class="page-btn page-btn--primary" target="_blank" rel="noopener noreferrer">
                        <span class="material-symbols-outlined text-[18px]">download</span> Download report
                    </a>
                <?php endif; ?>
                <a href="contact.html" class="page-btn page-btn--ghost">Request a briefing</a>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <h1 class="font-headline-lg text-primary mb-4">Article not found</h1>
    <?php endif; ?>
    <div class="insight-article__body"><?= $body ?></div>
</article>

<?php if ($related): ?>
    <aside class="insight-related" aria-labelledby="insight-related-title">
        <h2 class="insight-related__title" id="insight-related-title">More insights</h2>
        <ul class="insight-related__list">
            <?php foreach ($related as $item): ?>
                <li>
                    <a href="insight.php?slug=<?= rawurlencode((string) $item['slug']) ?>">
                        <?= htmlspecialchars((string) $item['title'], ENT_QUOTES) ?>
                        <span><?= htmlspecialchars((string) ($item['excerpt'] ?? ''), ENT_QUOTES) ?></span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </aside>
<?php endif; ?>

<?php include __DIR__ . '/partials/site-footer.html'; ?>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script src="assets/js/main.js"></script>
</body>
</html>
