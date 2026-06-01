<?php
declare(strict_types=1);

/**
 * Idempotent CRM setup — safe to run on every deploy (creates missing tables only).
 */
function kam_crm_schema_statements(): array
{
    $path = KAM_ROOT . '/database/schema.sql';
    $sql = file_get_contents($path);
    if ($sql === false) {
        throw new RuntimeException('Could not read database/schema.sql');
    }

    // Strip BOM / bad first line
    $sql = preg_replace('/^\xEF\xBB\xBF/', '', $sql) ?? $sql;
    $sql = preg_replace('/^pu--/m', '--', $sql) ?? $sql;

    $statements = [];
    foreach (array_filter(array_map('trim', explode(';', $sql))) as $statement) {
        if ($statement === '' || str_starts_with($statement, '--')) {
            continue;
        }
        $upper = strtoupper(ltrim($statement));
        // On shared hosting the DB already exists in .env — skip CREATE DATABASE / USE
        if (str_starts_with($upper, 'CREATE DATABASE') || str_starts_with($upper, 'USE ')) {
            continue;
        }
        if (str_starts_with($upper, 'CREATE TABLE')) {
            $statements[] = $statement;
        }
    }

    return $statements;
}

function kam_crm_tables_exist(): bool
{
    try {
        $pdo = Database::connection();
        $pdo->query('SELECT 1 FROM admins LIMIT 1');
        $pdo->query('SELECT 1 FROM leads LIMIT 1');
        return true;
    } catch (Throwable) {
        return false;
    }
}

function kam_crm_ensure_admin(): string
{
    $pdo = Database::connection();
    $email = kam_env('ADMIN_EMAIL', 'admin@kamglobalhr.com');
    $pass = kam_env('ADMIN_PASSWORD', 'ChangeMe123!');
    $name = kam_env('ADMIN_NAME', 'KAM Admin');

    $check = $pdo->prepare('SELECT id FROM admins WHERE email = ?');
    $check->execute([$email]);
    if ($check->fetch()) {
        return 'Admin account already exists (password unchanged).';
    }

    $hash = password_hash($pass, PASSWORD_DEFAULT);
    $ins = $pdo->prepare(
        'INSERT INTO admins (name, email, password_hash, role) VALUES (?, ?, ?, ?)'
    );
    $ins->execute([$name, $email, $hash, 'super_admin']);

    return 'Admin account created: ' . $email;
}

/**
 * @return string[] status messages
 */
function kam_crm_ensure_installed(): array
{
    $messages = [];
    $pdo = Database::connection();

    foreach (kam_crm_schema_statements() as $statement) {
        $pdo->exec($statement);
    }

    Database::reset();
    $messages[] = 'Database tables verified (created if missing).';
    $messages[] = kam_crm_ensure_admin();

    require_once KAM_ROOT . '/includes/cms_seed.php';
    try {
        kam_cms_seed_defaults();
        $messages[] = 'CMS default content seeded (if empty).';
    } catch (Throwable $e) {
        $messages[] = 'CMS seed skipped: ' . $e->getMessage();
    }

    return $messages;
}
