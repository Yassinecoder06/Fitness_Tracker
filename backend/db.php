<?php
declare(strict_types=1);

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

    $dsn = env_or_throw('SUPABASE_PG_DSN');
    $user = env_or_throw('SUPABASE_PG_USER');
    $pass = env_or_throw('SUPABASE_PG_PASSWORD');

    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    return $pdo;
}
