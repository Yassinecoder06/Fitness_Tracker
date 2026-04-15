<?php
/**
 * auth/processLogin.php — Handles the login form POST.
 *
 * Flow:
 *  1. Start session.
 *  2. Reject non-POST requests.
 *  3. Sanitize and validate inputs.
 *  4. Look up user by email.
 *  5. Verify bcrypt hash with password_verify().
 *  6. Regenerate session ID (prevents session fixation).
 *  7. Store minimal user data in session.
 *  8. Redirect to dashboard on success, back to index.php on failure.
 *
 * Why a SEPARATE processor file instead of handling in index.php?
 *  - Keeps index.php clean (display only).
 *  - This file is a pure POST handler — it never outputs HTML.
 *  - Follows the Post/Redirect/Get (PRG) pattern, preventing duplicate
 *    form submissions on browser refresh.
 */

session_start();

/* ── Reject any non-POST request ── */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /index.php');
    exit;
}

require_once __DIR__ . '/includes/db.php';

/* ── 1. Sanitize inputs ── */
$email    = trim($_POST['email']    ?? '');
$password = trim($_POST['password'] ?? '');

/* ── 2. Basic presence check before hitting the database ── */
if (empty($email) || empty($password)) {
    $_SESSION['login_error']     = 'Please fill in both fields.';
    $_SESSION['last_login_email'] = $email;
    header('Location: ../index.php');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['login_error']     = 'Please enter a valid email address.';
    $_SESSION['last_login_email'] = $email;
    header('Location: ../index.php');
    exit;
}

/* ── 3. Look up user by email ── */
$stmt = $pdo->prepare('SELECT id, name, password FROM users WHERE email = ? LIMIT 1');
$stmt->execute([$email]);
$user = $stmt->fetch();

/*
 * ── 4. Verify password ──
 *
 * IMPORTANT: We give the SAME generic error whether the email doesn't exist
 * OR the password is wrong. This prevents attackers from learning which
 * emails are registered in the system (user enumeration attack).
 */
if (!$user || !password_verify($password, $user['password'])) {
    $_SESSION['login_error']     = 'Invalid email or password. Please try again.';
    $_SESSION['last_login_email'] = $email;
    header('Location: ../index.php');
    exit;
}

/* ── 5. Successful login ── */

/*
 * Regenerate the session ID to prevent session fixation.
 * An attacker might plant a known session ID before login and try to
 * inherit the authenticated session after — regenerating invalidates that.
 */
session_regenerate_id(true);

/*
 * Store only the minimum necessary data in the session.
 * Never store passwords, full objects, or sensitive fields here.
 */
$_SESSION['user_id']   = $user['id'];
$_SESSION['user_name'] = $user['name'];
$_SESSION['user_email'] = $email;

/* ── 6. Redirect to the dashboard (Member 2's page) ── */
header('Location: /dashboard.php');
exit;