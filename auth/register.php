<?php
/**
 * auth/register.php — Handles the registration form POST.
 *
 * Flow:
 *  1. Start session.
 *  2. Reject non-POST requests.
 *  3. Sanitize and validate all inputs (server-side — never trust client).
 *  4. Check for duplicate email.
 *  5. Hash the password with bcrypt.
 *  6. Insert new user into the database.
 *  7. Set a success flash message and redirect back to index.php (login tab).
 *
 * On any error: flash the error into the session and redirect back to
 * index.php — the page will automatically open the Register tab.
 */

session_start();

/* ── Reject non-POST ── */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /index.php');
    exit;
}

require_once __DIR__ . '/../includes/db.php';  

/* ── 1. Sanitize inputs ── */
$name     = trim($_POST['name']     ?? '');
$email    = trim($_POST['email']    ?? '');
$password = trim($_POST['password'] ?? '');
$confirm  = trim($_POST['confirm']  ?? '');

/* ── 2. Server-side validation ── */
// Collect all errors so the user sees them all at once (better UX)
$errors = [];

if (empty($name)) {
    $errors[] = 'Full name is required.';
} elseif (strlen($name) > 100) {
    $errors[] = 'Name must be 100 characters or fewer.';
}

if (empty($email)) {
    $errors[] = 'Email address is required.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Please enter a valid email address.';
} elseif (strlen($email) > 150) {
    $errors[] = 'Email must be 150 characters or fewer.';
}

if (empty($password)) {
    $errors[] = 'Password is required.';
} elseif (strlen($password) < 8) {
    $errors[] = 'Password must be at least 8 characters.';
}

if ($password !== $confirm) {
    $errors[] = 'Passwords do not match.';
}

/* ── If validation failed, flash errors and redirect ── */
if (!empty($errors)) {
    // Join multiple errors into one readable string
    $_SESSION['register_error'] = implode(' ', $errors);
    // Re-populate fields (don't make the user retype name/email)
    $_SESSION['last_reg_name']  = $name;
    $_SESSION['last_reg_email'] = $email;
    header('Location: /index.php');
    exit;
}

/* ── 3. Check for duplicate email ── */
$stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
$stmt->execute([$email]);

if ($stmt->fetch()) {
    $_SESSION['register_error'] = 'An account with that email already exists. Try signing in instead.';
    $_SESSION['last_reg_name']  = $name;
    // Don't re-populate the email — the user should know it's taken
    header('Location: /index.php');
    exit;
}

/* ── 4. Hash password ── */
/*
 * password_hash() with PASSWORD_BCRYPT automatically:
 *  - Generates a random salt
 *  - Applies bcrypt at the given cost factor
 *  - Returns the full encoded string (algo + salt + hash) in one value
 * Cost 12 is a good default: strong enough, ~200ms on modern hardware.
 */
$hashed_password = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

/* ── 5. Insert new user ── */
try {
    $stmt = $pdo->prepare(
        'INSERT INTO users (name, email, password) VALUES (?, ?, ?)'
    );
    $stmt->execute([$name, $email, $hashed_password]);
} catch (PDOException $e) {
    // Log the real error server-side, never expose it to the browser
    error_log('Registration INSERT failed: ' . $e->getMessage());
    $_SESSION['register_error'] = 'Something went wrong. Please try again later.';
    header('Location: /index.php');
    exit;
}

/* ── 6. Success — redirect to login tab with a success notice ── */
$_SESSION['register_ok'] = true;
header('Location: /../index.php');
exit;