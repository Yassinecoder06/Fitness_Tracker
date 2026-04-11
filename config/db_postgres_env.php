<?php
function getDBConnection() {
    $dsn = getenv('SUPABASE_PG_POOLER_DSN')
        ?: 'pgsql:host=localhost;port=5432;dbname=fittrack';

    $user = getenv('SUPABASE_PG_POOLER_USER') ?: 'postgres';
    $pass = getenv('SUPABASE_PG_POOLER_PASSWORD') ?: '';

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::ATTR_TIMEOUT            => 10, // fail after 10 seconds
    ];

    return new PDO($dsn, $user, $pass, $options);
}