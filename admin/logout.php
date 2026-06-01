<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';

$user = Auth::user();
if ($user) {
    kam_log_activity($user['id'], 'admin', $user['id'], 'logout');
}
Auth::logout();
kam_redirect('login.php');
