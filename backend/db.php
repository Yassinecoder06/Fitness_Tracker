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

function env_optional(string $key): ?string
{
    $value = getenv($key);
    if ($value === false) {
        return null;
    }

    $trimmed = trim($value);
    return $trimmed === '' ? null : $trimmed;
}

function get_pdo(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = env_or_throw('SUPABASE_PG_DSN');
    $user = env_or_throw('SUPABASE_PG_USER');
    $pass = env_or_throw('SUPABASE_PG_PASSWORD');

    try {
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    } catch (PDOException $primaryEx) {
        $poolerDsn = env_optional('SUPABASE_PG_POOLER_DSN');
        $poolerUser = env_optional('SUPABASE_PG_POOLER_USER');
        $poolerPass = env_optional('SUPABASE_PG_POOLER_PASSWORD');

        if ($poolerDsn === null || $poolerUser === null || $poolerPass === null) {
            throw $primaryEx;
        }

        $pdo = new PDO($poolerDsn, $poolerUser, $poolerPass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }

    return $pdo;
}
