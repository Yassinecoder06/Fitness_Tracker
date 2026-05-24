<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

function env_value(string $key, string $default = ''): string
{
    $value = getenv($key);
    if ($value === false || trim($value) === '') {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? $default;
    }

    return (string)$value;
}

function env_or_throw(string $key): string
{
    $value = env_value($key);
    if (trim($value) === '') {
        throw new RuntimeException("Missing required env var: {$key}");
    }

    return (string)$value;
}

function get_pdo(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $useCloud = env_value('SUPABASE_USE_CLOUD') === 'true';
    
    $dsnKey = $useCloud ? 'SUPABASE_PG_POOLER_DSN_CLOUD' : 'SUPABASE_PG_POOLER_DSN_LOCAL';
    $userKey = $useCloud ? 'SUPABASE_PG_POOLER_USER_CLOUD' : 'SUPABASE_PG_POOLER_USER_LOCAL';
    $passKey = $useCloud ? 'SUPABASE_PG_POOLER_PASSWORD_CLOUD' : 'SUPABASE_PG_POOLER_PASSWORD_LOCAL';

    $poolerDsn = env_or_throw($dsnKey);
    $poolerUser = env_or_throw($userKey);
    $poolerPass = env_or_throw($passKey);
    $pdo = new PDO($poolerDsn, $poolerUser, $poolerPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    return $pdo;
}
