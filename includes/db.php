<?php
/**
 * includes/db.php — Shared database connection (PDO).
 *
 * HOW TO USE IN ANY PHP FILE:
 *   require_once __DIR__ . '/../includes/db.php';
 *   // $pdo is now available — use it directly.
 *
 * WHY PDO OVER mysqli?
 *   - Cleaner prepared-statement API (no bind_param / bind_result boilerplate).
 *   - Throws exceptions by default, making errors easy to catch.
 *   - Database-agnostic: if the project ever moves from MySQL, only the
 *     DSN string changes.
 *
 * CONFIGURATION:
 *   Change the four constants below to match your local environment.
 *   In a real production project these would come from a .env file
 *   that is never committed to version control (listed in .gitignore).
 */

define('DB_HOST',    'localhost');
define('DB_NAME',    'fittrack');
define('DB_USER',    'root');    // ← change for production
define('DB_PASS',    '');        // ← change for production
define('DB_CHARSET', 'utf8mb4'); // utf8mb4 supports emojis and all Unicode

$dsn = sprintf(
    'mysql:host=%s;dbname=%s;charset=%s',
    DB_HOST, DB_NAME, DB_CHARSET
);

$pdo_options = [
    // Throw a PDOException on any DB error instead of returning false silently
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,

    // Return rows as associative arrays: $row['name'] not $row[0]
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,

    /*
     * Use REAL prepared statements, not emulated ones.
     * Emulation builds the SQL string in PHP (still safe but less efficient).
     * Real prepared statements let MySQL do the heavy lifting.
     */
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $pdo_options);
} catch (PDOException $e) {
    /*
     * Log the real error to the server log (visible in XAMPP/WAMP logs).
     * NEVER echo $e->getMessage() to the browser — it can expose DB
     * credentials, table names, and other sensitive details.
     */
    error_log('[FitTrack] DB connection failed: ' . $e->getMessage());
    http_response_code(500);
    die('Service temporarily unavailable. Please try again later.');
}