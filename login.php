<?php
session_start();

if (!empty($_SESSION['user_id'])) {
    header('Location: /dashboard.php');
    exit;
}
require_once __DIR__ . '/backend/bootstrap.php';
require_once __DIR__ . '/backend/db.php';
$pdo = get_pdo();

function sanitize(string $value): string {
    return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
}
const MAX_ATTEMPTS   = 5;
const LOCKOUT_SECS   = 900; 

$now = time();

if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['login_lockout']  = 0;
}

$isLockedOut = $_SESSION['login_lockout'] > $now;
$lockoutRemaining = max(0, $_SESSION['login_lockout'] - $now);

$errors  = [];
$oldEmail = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$isLockedOut) {

    // CSRF check
    if (
        empty($_POST['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])
    ) {
        $errors['general'] = 'Invalid request. Please refresh and try again.';
    } else {
        $email    = sanitize($_POST['email']    ?? '');
        $password = $_POST['password']          ?? '';
        $remember = !empty($_POST['remember']);
        $oldEmail = $email;

        // 2. Basic validation
        if ($email === '') {
            $errors['email'] = 'Email address is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please enter a valid email address.';
        }

        if ($password === '') {
            $errors['password'] = 'Password is required.';
        }

        // 3. Verify credentials
        if (empty($errors)) {
            $stmt = $pdo->prepare(
                'SELECT id, name, email, password FROM users WHERE email = ? LIMIT 1'
            );
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if (!$user || !password_verify($password, $user['password'])) {
                $_SESSION['login_attempts']++;

                if ($_SESSION['login_attempts'] >= MAX_ATTEMPTS) {
                    $_SESSION['login_lockout'] = $now + LOCKOUT_SECS;
                    $isLockedOut       = true;
                    $lockoutRemaining  = LOCKOUT_SECS;
                    $errors['general'] = 'Too many failed attempts. Account locked for 15 minutes.';
                } else {
                    $remaining = MAX_ATTEMPTS - $_SESSION['login_attempts'];
                    $errors['general'] = "Invalid email or password. $remaining attempt(s) remaining.";
                }

            } else {
                $_SESSION['login_attempts'] = 0;
                $_SESSION['login_lockout']  = 0;

                // 5. Regenerate session ID to prevent fixation
                session_regenerate_id(true);

                // 6. Store user in session
                $_SESSION['user_id']    = $user['id'];
                $_SESSION['user_name']  = $user['name'];
                $_SESSION['user_email'] = $user['email'];

                // 7. Persistent "remember me" cookie (30 days)
                if ($remember) {
                    $token  = bin2hex(random_bytes(32));
                    $expiry = $now + (30 * 24 * 60 * 60);
                    setcookie('remember_token', $token, [
                        'expires'  => $expiry,
                        'path'     => '/',
                        'secure'   => true,
                        'httponly' => true,
                        'samesite' => 'Lax',
                    ]);
                    $stmt = $pdo->prepare("
                    INSERT INTO remember_tokens (user_id, token, expires_at)
                    VALUES (?, ?, ?)
                ");

                $stmt->execute([
                    $user['id'],
                    $token,
                    date('Y-m-d H:i:s', $expiry)
    ]);

                }
           
                // 8. Redirect to intended page or dashboard
                $redirect = filter_var($_GET['redirect'] ?? '', FILTER_SANITIZE_URL);
                $safe = (str_starts_with($redirect, '/') && !str_starts_with($redirect, '//'))
                    ? $redirect
                    : '/index.php';

                header('Location: ' . $safe);
                exit;
            }
        }
    }
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — FitTrack</title>
    <link rel="stylesheet" href="/css/login.css">
</head>
<body>

<div class="auth-wrapper">
    <div class="auth-card">

        <!-- Logo -->
        <div class="auth-logo">
            <div class="logo-icon">🏋️</div>
            <span class="logo-text">Fit<span>Track</span></span>
        </div>

        <h1 class="auth-heading">Welcome back</h1>
        <p class="auth-subheading">Sign in to continue your fitness journey.</p>

        <!-- Flash: welcome after registration -->
        <?php if (!empty($_GET['welcome'])): ?>
            <div class="alert alert-success mb-4" role="alert">
                <span>✓</span>
                <span>Account created! You're now signed in.</span>
            </div>
        <?php endif; ?>

        <!-- Lockout notice -->
        <?php if ($isLockedOut): ?>
            <div class="alert alert-error mb-4" role="alert">
                <span>🔒</span>
                <span>
                    Too many failed attempts. Please wait
                    <strong id="countdown"><?= ceil($lockoutRemaining / 60) ?> minute(s)</strong>
                    before trying again.
                </span>
            </div>
        <?php endif; ?>

        <!-- General error (invalid credentials / CSRF) -->
        <?php if (isset($errors['general'])): ?>
            <div class="alert alert-error mb-4" role="alert">
                <span>⚠</span>
                <span><?= htmlspecialchars($errors['general'], ENT_QUOTES) ?></span>
            </div>
        <?php endif; ?>

        <!-- Login Form -->
        <form method="POST" action="/login.php<?= !empty($_GET['redirect']) ? '?redirect='.urlencode($_GET['redirect']) : '' ?>"
              id="loginForm" novalidate>

            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

            <!-- Email -->
            <div class="form-group">
                <label class="form-label" for="email">
                    Email Address <span class="required" aria-hidden="true">*</span>
                </label>
                <div class="form-input-wrapper">
                    <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                         viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect width="20" height="16" x="2" y="4" rx="2"/>
                        <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
                    </svg>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="form-input has-icon-left <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                        placeholder="jane@example.com"
                        value="<?= htmlspecialchars($oldEmail, ENT_QUOTES) ?>"
                        autocomplete="email"
                        <?= $isLockedOut ? 'disabled' : '' ?>
                        required
                    >
                </div>
                <?php if (isset($errors['email'])): ?>
                    <span class="form-error" role="alert">⚠ <?= $errors['email'] ?></span>
                <?php endif; ?>
            </div>

            <!-- Password -->
            <div class="form-group">
                <div class="flex justify-between items-center">
                    <label class="form-label" for="password" style="margin-bottom:0">
                        Password <span class="required" aria-hidden="true">*</span>
                    </label>
                    <a href="forgot-password.php" class="text-sm text-primary" tabindex="-1">
                        Forgot password?
                    </a>
                </div>
                <div class="form-input-wrapper mt-2">
                    <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                         viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect width="18" height="11" x="3" y="11" rx="2" ry="2"/>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                    </svg>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-input has-icon-left has-icon-right <?= isset($errors['password']) ? 'is-invalid' : '' ?>"
                        placeholder="Your password"
                        autocomplete="current-password"
                        <?= $isLockedOut ? 'disabled' : '' ?>
                        required
                    >
                    <button type="button" class="input-icon-right" id="togglePassword"
                            aria-label="Toggle password visibility">👁</button>
                </div>
                <?php if (isset($errors['password'])): ?>
                    <span class="form-error" role="alert">⚠ <?= $errors['password'] ?></span>
                <?php endif; ?>
            </div>

            <!-- Remember me -->
            <div class="form-group">
                <label class="form-check">
                    <input type="checkbox" name="remember" id="remember"
                           <?= $isLockedOut ? 'disabled' : '' ?>>
                    <span class="form-check-label">Remember me for 30 days</span>
                </label>
            </div>

            <!-- Submit -->
            <div class="mt-6">
                <button
                    type="submit"
                    class="btn btn-primary btn-lg btn-full"
                    id="submitBtn"
                    <?= $isLockedOut ? 'disabled' : '' ?>
                >
                    Sign In
                </button>
            </div>

        </form>

        <div class="form-divider"><span>don't have an account?</span></div>

        <a href="register.php" class="btn btn-secondary btn-full">
            Create Account
        </a>

    </div>
</div>

<script>
const passInput = document.getElementById('password');
document.getElementById('togglePassword').addEventListener('click', function () {
    const isText = passInput.type === 'text';
    passInput.type = isText ? 'password' : 'text';
    this.textContent = isText ? '👁' : '🙈';
});
const form      = document.getElementById('loginForm');
const emailInput = document.getElementById('email');
const submitBtn = document.getElementById('submitBtn');

function setError(input, msg) {
    input.classList.add('is-invalid');
    const group    = input.closest('.form-group');
    const existing = group.querySelector('.form-error.js-error');
    if (existing) existing.remove();
    const span = document.createElement('span');
    span.className = 'form-error js-error';
    span.role = 'alert';
    span.textContent = '⚠ ' + msg;
    group.appendChild(span);
}

form.addEventListener('submit', function (e) {
    let valid = true;

    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!re.test(emailInput.value.trim())) {
        setError(emailInput, 'Please enter a valid email address.');
        valid = false;
    }

    if (passInput.value === '') {
        setError(passInput, 'Password is required.');
        valid = false;
    }

    if (!valid) { e.preventDefault(); return; }

    submitBtn.classList.add('loading');
    submitBtn.textContent = 'Signing in…';
    submitBtn.disabled = true;
});

/* --- Lockout countdown --- */
const countdownEl = document.getElementById('countdown');
if (countdownEl) {
    let seconds = <?= $lockoutRemaining ?>;
    const interval = setInterval(() => {
        seconds--;
        if (seconds <= 0) {
            clearInterval(interval);
            location.reload();
        } else {
            const m = Math.floor(seconds / 60);
            const s = seconds % 60;
            countdownEl.textContent = m > 0 ? `${m}m ${s}s` : `${s}s`;
        }
    }, 1000);
}
</script>

</body>
</html>