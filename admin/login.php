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
    <title>Login — KAM CRM</title>
    <link rel="stylesheet" href="assets/css/admin.css"/>
</head>
<body class="admin-auth">
    <div class="admin-auth__card">
        <h1>KAM CRM</h1>
        <p>Sign in to manage leads and inquiries.</p>
        <?php if ($error): ?>
            <div class="admin-alert admin-alert--error"><?= kam_h($error) ?></div>
        <?php endif; ?>
        <form method="post" autocomplete="on">
            <div class="admin-form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required autofocus
                       value="<?= kam_h($_POST['email'] ?? '') ?>"/>
            </div>
            <div class="admin-form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required/>
            </div>
            <button type="submit" class="admin-btn admin-btn--primary" style="width:100%">Sign in</button>
        </form>
    </div>
</body>
</html>
