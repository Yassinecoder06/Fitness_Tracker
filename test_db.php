<?php
// Load .env
foreach (file('.env') as $line) {
    $line = trim($line);
    if ($line && !str_starts_with($line, '#') && str_contains($line, '=')) {
        [$key, $value] = explode('=', $line, 2);
        putenv(trim($key) . '=' . trim($value));
    }
}

require 'config/db_postgres_env.php';

try {
    $pdo = getDBConnection();

    // 1. Check connection
    echo "✅ Connected to Supabase successfully!\n";

    // 2. Check your tables exist
    $tables = $pdo->query("
        SELECT table_name FROM information_schema.tables
        WHERE table_schema = 'public'
    ")->fetchAll();

    echo "\n📋 Tables found:\n";
    foreach ($tables as $row) {
        echo "  - " . $row['table_name'] . "\n";
    }

    // 3. Test a real query on goals
    $count = $pdo->query("SELECT COUNT(*) as c FROM goals")->fetch();
    echo "\n🎯 Goals table rows: " . $count['c'] . "\n";

} catch (PDOException $e) {
    echo "❌ Connection failed: " . $e->getMessage() . "\n";
}