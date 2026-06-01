<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_once dirname(__DIR__) . '/includes/admin_guard.php';

kam_admin_bootstrap();

if (Auth::check()) {
    kam_redirect('index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        $error = 'Email and password are required.';
    } else {
        try {
            $pdo = Database::connection();
            $stmt = $pdo->prepare(
                'SELECT * FROM admins WHERE email = ? AND is_active = 1 LIMIT 1'
            );
            $stmt->execute([$email]);
            $admin = $stmt->fetch();

            if ($admin && password_verify($password, $admin['password_hash'])) {
                Auth::login($admin);
                $pdo->prepare('UPDATE admins SET last_login_at = NOW() WHERE id = ?')
                    ->execute([(int) $admin['id']]);
                kam_log_activity((int) $admin['id'], 'admin', (int) $admin['id'], 'login');
                kam_redirect('index.php');
            }
            $error = 'Invalid email or password.';
        } catch (Throwable) {
            $error = 'Database connection failed. Check config/.env or run install.php.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <meta name="robots" content="noindex, nofollow"/>
    <title>Sign in — KAM CRM</title>
    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
    <link rel="stylesheet" href="assets/css/admin.css"/>
</head>
<body class="admin-auth">
    <div class="admin-auth__panel">
        <div class="admin-auth__panel-inner">
            <div class="admin-auth__brand-pill">
                <span class="material-symbols-outlined">corporate_fare</span>
                KAM Global HR
            </div>
            <h2>Workforce CRM</h2>
            <p>Manage inquiries, track pipeline status, and grow client relationships from one secure dashboard.</p>
            <ul class="admin-auth__features">
                <li>
                    <span class="admin-auth__feature-icon"><span class="material-symbols-outlined">group</span></span>
                    <span>
                        <strong>Lead management</strong>
                        <small>Capture &amp; follow up on every inquiry</small>
                    </span>
                </li>
                <li>
                    <span class="admin-auth__feature-icon"><span class="material-symbols-outlined">trending_up</span></span>
                    <span>
                        <strong>Pipeline tracking</strong>
                        <small>New → won/lost status workflow</small>
                    </span>
                </li>
                <li>
                    <span class="admin-auth__feature-icon"><span class="material-symbols-outlined">mail</span></span>
                    <span>
                        <strong>Newsletter CRM</strong>
                        <small>Subscriber list from insights page</small>
                    </span>
                </li>
            </ul>
        </div>
    </div>
    <div class="admin-auth__main">
        <div class="admin-auth__card">
            <div class="admin-auth__card-logo" aria-hidden="true">
                <span class="material-symbols-outlined">lock</span>
            </div>
            <h1>Welcome back</h1>
            <p>Sign in to your admin account to continue.</p>
            <?php if ($error): ?>
                <div class="admin-alert admin-alert--error">
                    <span class="material-symbols-outlined">error</span>
                    <?= kam_h($error) ?>
                </div>
            <?php endif; ?>
            <form method="post" autocomplete="on" class="admin-auth__form">
                <div class="admin-form-group">
                    <label for="email">Email address</label>
                    <div class="admin-input-wrap">
                        <span class="material-symbols-outlined">mail</span>
                        <input type="email" id="email" name="email" required autofocus
                               placeholder="you@company.com"
                               value="<?= kam_h($_POST['email'] ?? '') ?>"/>
                    </div>
                </div>
                <div class="admin-form-group">
                    <label for="password">Password</label>
                    <div class="admin-input-wrap">
                        <span class="material-symbols-outlined">key</span>
                        <input type="password" id="password" name="password" required
                               placeholder="Enter your password"/>
                    </div>
                </div>
                <button type="submit" class="admin-btn admin-btn--primary admin-btn--lg">
                    <span class="material-symbols-outlined">login</span>
                    Sign in to dashboard
                </button>
            </form>
            <p class="admin-auth__footer-note">
                <a href="check.php">System check</a> · Secure admin access
            </p>
        </div>
    </div>
</body>
</html>
