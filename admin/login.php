<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';

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
            $error = 'Database connection failed. Run install.php first.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>Sign in — KAM CRM</title>
    <link rel="stylesheet" href="assets/css/admin.css"/>
</head>
<body class="admin-auth">
    <div class="admin-auth__panel">
        <h2>Workforce CRM for KAM Global HR</h2>
        <p>Manage inquiries, track pipeline status, and grow client relationships from one secure dashboard.</p>
        <ul class="admin-auth__features">
            <li>
                <span class="material-symbols-outlined">group</span>
                Lead management &amp; follow-ups
            </li>
            <li>
                <span class="material-symbols-outlined">trending_up</span>
                Pipeline status tracking
            </li>
            <li>
                <span class="material-symbols-outlined">mail</span>
                Newsletter subscriber list
            </li>
        </ul>
    </div>
    <div class="admin-auth__main">
        <div class="admin-auth__card">
            <div class="admin-auth__card-logo" aria-hidden="true">
                <span class="material-symbols-outlined">lock</span>
            </div>
            <h1>Welcome back</h1>
            <p>Sign in to your admin account to continue.</p>
            <?php if ($error): ?>
                <div class="admin-alert admin-alert--error"><?= kam_h($error) ?></div>
            <?php endif; ?>
            <form method="post" autocomplete="on">
                <div class="admin-form-group">
                    <label for="email">Email address</label>
                    <input type="email" id="email" name="email" required autofocus
                           placeholder="you@company.com"
                           value="<?= kam_h($_POST['email'] ?? '') ?>"/>
                </div>
                <div class="admin-form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required
                           placeholder="Enter your password"/>
                </div>
                <button type="submit" class="admin-btn admin-btn--primary" style="width:100%;margin-top:0.25rem">
                    <span class="material-symbols-outlined">login</span>
                    Sign in
                </button>
            </form>
        </div>
    </div>
</body>
</html>
