<?php
/**
 * includes/auth_guard.php — Protects any page that requires authentication.
 *
 * ════════════════════════════════════════════════════════════
 * HOW EVERY TEAM MEMBER USES THIS FILE
 * ════════════════════════════════════════════════════════════
 * Add THESE TWO LINES at the very top of any protected .php page,
 * BEFORE any HTML output:
 *
 *   <?php
 *   require_once __DIR__ . '/../includes/auth_guard.php';
 *   require_once __DIR__ . '/../includes/db.php';
 *   // ... rest of the page
 *
 * ═══════════════════════════════════════════════════════════
 * THEN — to show the logged-in user's name anywhere in HTML:
 *
 *   Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?>!
 *
 * To get the user's ID for database queries:
 *
 *   $user_id = $_SESSION['user_id'];
 *
 * ════════════════════════════════════════════════════════════
 * WHICH PAGES NEED THIS FILE?
 *   - dashboard.php  (Member 2)
 *   - diary.php      (Member 3)
 *   - food.php       (Member 4)
 *   - exercise.php   (Member 5)
 *   - goals.php      (Member 6)
 *   - progress.php   (Member 6)
 *
 * DO NOT add this to: index.php, auth/processLogin.php, auth/register.php
 * ════════════════════════════════════════════════════════════
 */

/* Only start a new session if one isn't already running.
 * This prevents "Cannot send session cache limiter" errors when
 * multiple files call session_start() on the same request. */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* If no user_id in session → not logged in → redirect to login page */
if (!isset($_SESSION['user_id'])) {
    /*
     * Store the URL they tried to visit so we can redirect them back
     * after a successful login (optional enhancement for later).
     */
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? '/';

    header('Location: /index.php');

    /*
     * CRITICAL: Always exit() after header('Location: ...')
     * Without exit, PHP continues executing the rest of the script,
     * which could output sensitive data to an unauthenticated user.
     */
    exit;
}