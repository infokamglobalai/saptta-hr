<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_once dirname(__DIR__) . '/includes/admin_app.php';
require_once dirname(__DIR__) . '/includes/CmsRepository.php';

kam_admin_app_boot();

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Auth::verifyCsrf($_POST['csrf'] ?? null)) {
        $error = 'Invalid session.';
    } else {
        $keys = [
            'site_tagline', 'contact_email', 'contact_phone', 'copyright_text',
            'social_linkedin', 'social_facebook', 'social_x', 'social_youtube',
            'stat_years_value', 'stat_years_label', 'stat_clients_value', 'stat_clients_label',
            'stat_countries_value', 'stat_countries_label',
            'stat_industries_value', 'stat_industries_label',
            'stat_partner_value', 'stat_partner_label',
        ];
        $pairs = [];
        foreach ($keys as $key) {
            $pairs[$key] = trim((string) ($_POST[$key] ?? ''));
        }
        CmsRepository::settingsSave($pairs);
        $message = 'Settings saved.';
    }
}

$settings = CmsRepository::settingsAll();
$csrf = Auth::csrfToken();

kam_admin_render(function () use ($settings, $message, $error, $csrf): void {
    ob_start();
    ?>
    <?php if ($message): ?><div class="admin-alert admin-alert--success"><?= kam_h($message) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="admin-alert admin-alert--error"><?= kam_h($error) ?></div><?php endif; ?>
    <form method="post" class="admin-card">
        <div class="admin-card__head"><h2><span class="material-symbols-outlined">settings</span> Site settings</h2></div>
        <div class="admin-card__body">
            <input type="hidden" name="csrf" value="<?= kam_h($csrf) ?>"/>
            <div class="admin-grid-2">
                <div>
                    <h3 class="admin-form-section-title">Contact</h3>
                    <?php foreach (['contact_email' => 'Email', 'contact_phone' => 'Phone'] as $k => $label): ?>
                        <div class="admin-form-group">
                            <label for="<?= $k ?>"><?= kam_h($label) ?></label>
                            <input id="<?= $k ?>" name="<?= $k ?>" value="<?= kam_h($settings[$k] ?? '') ?>"/>
                        </div>
                    <?php endforeach; ?>
                    <div class="admin-form-group">
                        <label for="site_tagline">Footer tagline</label>
                        <textarea id="site_tagline" name="site_tagline" rows="3"><?= kam_h($settings['site_tagline'] ?? '') ?></textarea>
                    </div>
                    <div class="admin-form-group">
                        <label for="copyright_text">Copyright line</label>
                        <input id="copyright_text" name="copyright_text" value="<?= kam_h($settings['copyright_text'] ?? '') ?>"/>
                    </div>
                </div>
                <div>
                    <h3 class="admin-form-section-title">Social links</h3>
                    <?php foreach (['social_linkedin' => 'LinkedIn', 'social_facebook' => 'Facebook', 'social_x' => 'X', 'social_youtube' => 'YouTube'] as $k => $label): ?>
                        <div class="admin-form-group">
                            <label for="<?= $k ?>"><?= kam_h($label) ?> URL</label>
                            <input id="<?= $k ?>" name="<?= $k ?>" value="<?= kam_h($settings[$k] ?? '') ?>"/>
                        </div>
                    <?php endforeach; ?>
                    <h3 class="admin-form-section-title">Footer stats</h3>
                    <?php
                    $stats = [
                        ['stat_years_value', 'stat_years_label', 'Years stat'],
                        ['stat_clients_value', 'stat_clients_label', 'Clients stat'],
                        ['stat_countries_value', 'stat_countries_label', 'Countries stat'],
                        ['stat_industries_value', 'stat_industries_label', 'Industries stat (home trust band)'],
                        ['stat_partner_value', 'stat_partner_label', 'Partner stat (footer)'],
                    ];
                    foreach ($stats as [$vk, $lk, $title]):
                    ?>
                        <p class="admin-form-hint"><?= kam_h($title) ?></p>
                        <div class="admin-form-row">
                            <div class="admin-form-group">
                                <input name="<?= $vk ?>" placeholder="Value" value="<?= kam_h($settings[$vk] ?? '') ?>"/>
                            </div>
                            <div class="admin-form-group">
                                <input name="<?= $lk ?>" placeholder="Label" value="<?= kam_h($settings[$lk] ?? '') ?>"/>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <button type="submit" class="admin-btn admin-btn--primary">
                <span class="material-symbols-outlined">save</span> Save settings
            </button>
        </div>
    </form>
    <?php
    $content = ob_get_clean();
    $pageTitle = 'Site settings';
    $pageSubtitle = 'Contact, social, and footer stats';
    $activeNav = 'settings';
    require __DIR__ . '/includes/layout.php';
});
