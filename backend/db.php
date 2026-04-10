<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

function env_or_throw(string $key): string
{
    $value = getenv($key);
    if ($value === false || trim($value) === '') {
        throw new RuntimeException("Missing required env var: {$key}");
    }

    return $value;
}

function get_pdo(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $poolerDsn = env_or_throw('SUPABASE_PG_POOLER_DSN');
    $poolerUser = env_or_throw('SUPABASE_PG_POOLER_USER');
    $poolerPass = env_or_throw('SUPABASE_PG_POOLER_PASSWORD');

    $pdo = new PDO($poolerDsn, $poolerUser, $poolerPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    return $pdo;
}
