<?php
session_start();
if (!empty($_SESSION['user_id'])) {
    header('Location: /index.php');
    exit;
}
require_once __DIR__ . '/backend/bootstrap.php';
require_once __DIR__ . '/backend/db.php';
$db= get_pdo();
function sanitize(string $value): string {
    return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
}
$errors  = [];
$success = '';
$old     = ['name' => '', 'email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name            = sanitize($_POST['name']             ?? '');
    $email           = sanitize($_POST['email']            ?? '');
    $password        = $_POST['password']                  ?? '';
    $confirmPassword = $_POST['confirm_password']          ?? '';

    $old = ['name' => $name, 'email' => $email];
    if ($name === '') {
        $errors['name'] = 'Full name is required.';
    } elseif (strlen($name) < 2) {
        $errors['name'] = 'Name must be at least 2 characters.';
    }

    if ($email === '') {
        $errors['email'] = 'Email address is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    }

    if ($password === '') {
        $errors['password'] = 'Password is required.';
    } elseif (strlen($password) < 8) {
        $errors['password'] = 'Password must be at least 8 characters.';
    }

    if ($confirmPassword === '') {
        $errors['confirm_password'] = 'Please confirm your password.';
    } elseif ($password !== $confirmPassword) {
        $errors['confirm_password'] = 'Passwords do not match.';
    }

    // 3. Check email uniqueness
    if (empty($errors)) {
        $stmt = $db->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $errors['email'] = 'This email is already registered. Please log in.';
        }
    }
    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

        $stmt = $db->prepare(
            'INSERT INTO users (name, email, password) VALUES (?, ?, ?)'
        );
        $stmt->execute([$name, $email, $hashedPassword]);
        $userId = $db->lastInsertId();

        // 5. Create authenticated session
        session_regenerate_id(true);
        $_SESSION['user_id']   = $userId;
        $_SESSION['user_name'] = $name;
        $_SESSION['user_email']= $email;
        header('Location: /index.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account — FitTrack</title>
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

        <h1 class="auth-heading">Create your account</h1>
        <p class="auth-subheading">Start tracking your fitness journey today.</p>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error mb-4" role="alert">
                <span>⚠</span>
                <span>Please fix the errors below and try again.</span>
            </div>
        <?php endif; ?>

        <!-- Registration Form -->
        <form method="POST" action="register.php" id="registerForm" novalidate>

            <!-- CSRF token -->
            <?php
                if (empty($_SESSION['csrf_token'])) {
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                }
            ?>
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

            <!-- Full Name -->
            <div class="form-group">
                <label class="form-label" for="name">
                    Full Name <span class="required" aria-hidden="true">*</span>
                </label>
                <div class="form-input-wrapper">
                    <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                         viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        class="form-input has-icon-left <?= isset($errors['name']) ? 'is-invalid' : '' ?>"
                        placeholder="Jane Doe"
                        value="<?= htmlspecialchars($old['name'], ENT_QUOTES) ?>"
                        autocomplete="name"
                        required
                    >
                </div>
                <?php if (isset($errors['name'])): ?>
                    <span class="form-error" role="alert">⚠ <?= $errors['name'] ?></span>
                <?php endif; ?>
            </div>

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
                        value="<?= htmlspecialchars($old['email'], ENT_QUOTES) ?>"
                        autocomplete="email"
                        required
                    >
                </div>
                <?php if (isset($errors['email'])): ?>
                    <span class="form-error" role="alert">⚠ <?= $errors['email'] ?></span>
                <?php endif; ?>
            </div>

            <!-- Password -->
            <div class="form-group">
                <label class="form-label" for="password">
                    Password <span class="required" aria-hidden="true">*</span>
                </label>
                <div class="form-input-wrapper">
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
                        placeholder="Min. 8 characters"
                        autocomplete="new-password"
                        required
                        minlength="8"
                    >
                    <button type="button" class="input-icon-right" id="togglePassword"
                            aria-label="Toggle password visibility">👁</button>
                </div>
                <!-- Password strength indicator -->
                <div class="password-strength" id="strengthContainer" hidden>
                    <div class="strength-bars">
                        <div class="strength-bar" id="bar1"></div>
                        <div class="strength-bar" id="bar2"></div>
                        <div class="strength-bar" id="bar3"></div>
                        <div class="strength-bar" id="bar4"></div>
                    </div>
                    <span class="strength-label" id="strengthLabel"></span>
                </div>
                <?php if (isset($errors['password'])): ?>
                    <span class="form-error" role="alert">⚠ <?= $errors['password'] ?></span>
                <?php endif; ?>
            </div>

            <!-- Confirm Password -->
            <div class="form-group">
                <label class="form-label" for="confirm_password">
                    Confirm Password <span class="required" aria-hidden="true">*</span>
                </label>
                <div class="form-input-wrapper">
                    <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                         viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    </svg>
                    <input
                        type="password"
                        id="confirm_password"
                        name="confirm_password"
                        class="form-input has-icon-left <?= isset($errors['confirm_password']) ? 'is-invalid' : '' ?>"
                        placeholder="Repeat your password"
                        autocomplete="new-password"
                        required
                    >
                </div>
                <?php if (isset($errors['confirm_password'])): ?>
                    <span class="form-error" role="alert">⚠ <?= $errors['confirm_password'] ?></span>
                <?php endif; ?>
            </div>

            <!-- Terms -->
            <div class="form-group mt-2">
                <label class="form-check">
                    <input type="checkbox" name="terms" id="terms" required>
                    <span class="form-check-label">
                        I agree to the
                        <a href="/terms.php" target="_blank">Terms of Service</a> and
                        <a href="/privacy.php" target="_blank">Privacy Policy</a>
                    </span>
                </label>
            </div>

            <!-- Submit -->
            <div class="mt-6">
                <button type="submit" class="btn btn-primary btn-lg btn-full" id="submitBtn">
                    Create Account
                </button>
            </div>

        </form>

        <div class="form-divider"><span>already have an account?</span></div>

        <a href="login.php" class="btn btn-secondary btn-full">
            Sign In
        </a>

    </div>
</div>

<script>
const form       = document.getElementById('registerForm');
const nameInput  = document.getElementById('name');
const emailInput = document.getElementById('email');
const passInput  = document.getElementById('password');
const confInput  = document.getElementById('confirm_password');
const submitBtn  = document.getElementById('submitBtn');
document.getElementById('togglePassword').addEventListener('click', function () {
    const isText = passInput.type === 'text';
    passInput.type = isText ? 'password' : 'text';
    this.textContent = isText ? '👁' : '🙈';
});

/* --- Password strength meter --- */
const strengthContainer = document.getElementById('strengthContainer');
const strengthLabel     = document.getElementById('strengthLabel');
const bars              = [
    document.getElementById('bar1'),
    document.getElementById('bar2'),
    document.getElementById('bar3'),
    document.getElementById('bar4'),
];

function measureStrength(pwd) {
    let score = 0;
    if (pwd.length >= 8)                          score++;
    if (pwd.length >= 12)                         score++;
    if (/[A-Z]/.test(pwd) && /[a-z]/.test(pwd))  score++;
    if (/[0-9]/.test(pwd))                        score++;
    if (/[^A-Za-z0-9]/.test(pwd))                 score = Math.min(score + 1, 4);
    return Math.min(score, 4);
}

passInput.addEventListener('input', function () {
    const pwd = this.value;


    if (pwd.length === 0) {
        strengthContainer.hidden = true;
        return;
    }

    strengthContainer.hidden = false;
    const level = measureStrength(pwd);
    const labels  = ['', 'Weak', 'Fair', 'Good', 'Strong'];
    const classes = ['', 'weak', 'fair', 'good', 'strong'];

    bars.forEach((bar, i) => {
        bar.className = 'strength-bar';
        if (i < level) bar.classList.add('filled');

        if (level <= 1) bar.classList.add('weak');
        else if (level === 2) bar.classList.add('fair');
        else if (level === 3) bar.classList.add('good');
        else bar.classList.add('strong');
    });

    strengthLabel.textContent = labels[level];
});
function setError(input, msg) {
    input.classList.add('is-invalid');
    input.classList.remove('is-valid');
    const existing = input.closest('.form-group').querySelector('.form-error.js-error');
    if (existing) existing.remove();
    const span = document.createElement('span');
    span.className = 'form-error js-error';
    span.role = 'alert';
    span.textContent = '⚠ ' + msg;
    input.closest('.form-group').appendChild(span);
}

function clearError(input) {
    input.classList.remove('is-invalid');
    input.classList.add('is-valid');
    const existing = input.closest('.form-group').querySelector('.form-error.js-error');
    if (existing) existing.remove();
}

nameInput.addEventListener('blur', function () {
    if (this.value.trim().length < 2) {
        setError(this, 'Name must be at least 2 characters.');
    } else {
        clearError(this);
    }
});

emailInput.addEventListener('blur', function () {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!re.test(this.value.trim())) {
        setError(this, 'Please enter a valid email address.');
    } else {
        clearError(this);
    }
});

passInput.addEventListener('blur', function () {
    if (this.value.length < 8) {
        setError(this, 'Password must be at least 8 characters.');
    } else {
        clearError(this);
    }
});

confInput.addEventListener('blur', function () {
    if (this.value !== passInput.value) {
        setError(this, 'Passwords do not match.');
    } else {
        clearError(this);
    }
});
form.addEventListener('submit', function (e) {
    let valid = true;

    if (nameInput.value.trim().length < 2) {
        setError(nameInput, 'Name must be at least 2 characters.');
        valid = false;
    }

    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!re.test(emailInput.value.trim())) {
        setError(emailInput, 'Please enter a valid email address.');
        valid = false;
    }

    if (passInput.value.length < 8) {
        setError(passInput, 'Password must be at least 8 characters.');
        valid = false;
    }

    if (confInput.value !== passInput.value) {
        setError(confInput, 'Passwords do not match.');
        valid = false;
    }

    if (!document.getElementById('terms').checked) {
        valid = false;
        alert('Please accept the Terms of Service to continue.');
    }

    if (!valid) { e.preventDefault(); return; }
    submitBtn.classList.add('loading');
    submitBtn.textContent = 'Creating account…';
    submitBtn.disabled = true;
});
</script>

</body>
</html>