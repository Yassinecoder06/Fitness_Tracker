<?php
/**
 * index.php — FitTrack landing page.
 *
 * This is the PUBLIC entry point of the app (root of project).
 * It shows a tab-based UI: Login | Register.
 *
 * - If the user is already logged in, redirect straight to dashboard.
 * - Login is processed by auth/processLogin.php (POST).
 * - Registration is processed by auth/register.php (POST).
 * - Both processors redirect BACK here with error/success flags in the session.
 */

session_start();

/* ── Already authenticated? Skip the login page ── */
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

/* ── Pull one-time flash messages set by the processors ── */
$login_error    = $_SESSION['login_error']    ?? '';
$register_error = $_SESSION['register_error'] ?? '';
$register_ok    = $_SESSION['register_ok']    ?? false;

// Clear flash messages immediately after reading them
unset($_SESSION['login_error'], $_SESSION['register_error'], $_SESSION['register_ok']);

/*
 * Decide which tab to show on load:
 *  - 'register' tab if the user just tried to register (error or success)
 *  - 'login'    tab in all other cases
 */
$active_tab = ($register_error || $register_ok) ? 'register' : 'login';

/* ── Re-populate form fields so the user doesn't retype on validation failure ── */
$login_email      = htmlspecialchars($_SESSION['last_login_email']    ?? '');
$reg_name         = htmlspecialchars($_SESSION['last_reg_name']        ?? '');
$reg_email        = htmlspecialchars($_SESSION['last_reg_email']       ?? '');
unset($_SESSION['last_login_email'], $_SESSION['last_reg_name'], $_SESSION['last_reg_email']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FitTrack | Welcome</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* ── Auth page layout ─────────────────────────────────── */
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: var(--gray-50);
        }

        .auth-wrapper {
            width: 100%;
            max-width: 440px;
            padding: 1.5rem;
        }

        /* Brand header above the card */
        .auth-brand {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .6rem;
            margin-bottom: 1.75rem;
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--primary);
        }

        .auth-brand__icon {
            width: 36px;
            height: 36px;
            background: var(--primary);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
        }

        .auth-brand__icon svg {
            width: 22px;
            height: 22px;
        }

        /* Card holds both tabs */
        .auth-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(0,0,0,.08);
            overflow: hidden;
        }

        /* ── Tab switcher ─────────────────────────────────────── */
        .auth-tabs {
            display: flex;
            border-bottom: 1px solid var(--gray-200);
        }

        .auth-tab {
            flex: 1;
            padding: 1rem;
            background: none;
            border: none;
            cursor: pointer;
            font-size: .95rem;
            font-weight: 600;
            color: var(--gray-500);
            transition: color .2s, box-shadow .2s;
        }

        .auth-tab.active {
            color: var(--primary);
            box-shadow: inset 0 -2px 0 var(--primary);
        }

        /* ── Forms ────────────────────────────────────────────── */
        .auth-form {
            display: none;
            padding: 2rem;
        }

        .auth-form.active {
            display: block;
        }

        .auth-form h2 {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: .35rem;
        }

        .auth-form .subtitle {
            font-size: .875rem;
            color: var(--gray-500);
            margin-bottom: 1.5rem;
        }

        /* ── Alert boxes ──────────────────────────────────────── */
        .alert {
            padding: .75rem 1rem;
            border-radius: 8px;
            font-size: .875rem;
            margin-bottom: 1.25rem;
            line-height: 1.5;
        }

        .alert--error {
            background: var(--accent-red-bg);
            color: var(--accent-red);
            border: 1px solid #fca5a5;
        }

        .alert--success {
            background: var(--accent-green-bg);
            color: var(--accent-green);
            border: 1px solid #6ee7b7;
        }

        /* Inline JS error (shown before submit) */
        .inline-error {
            display: none;
            font-size: .8rem;
            color: var(--accent-red);
            margin-top: .25rem;
        }

        /* ── Footer link ──────────────────────────────────────── */
        .auth-switch {
            text-align: center;
            margin-top: 1.25rem;
            font-size: .875rem;
            color: var(--gray-500);
        }

        .auth-switch button {
            background: none;
            border: none;
            color: var(--primary);
            font-weight: 600;
            cursor: pointer;
            padding: 0;
        }
    </style>
</head>
<body>

<div class="auth-wrapper">

    <!-- Brand logo -->
    <div class="auth-brand">
        <div class="auth-brand__icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                 stroke-linecap="round" stroke-linejoin="round">
                <path d="M18 20V10"/><path d="M12 20V4"/><path d="M6 20v-6"/>
            </svg>
        </div>
        FitTrack
    </div>

    <div class="auth-card">

        <!-- Tab buttons -->
        <div class="auth-tabs">
            <button class="auth-tab <?= $active_tab === 'login'    ? 'active' : '' ?>"
                    id="tab-login"    onclick="switchTab('login')">Sign In</button>
            <button class="auth-tab <?= $active_tab === 'register' ? 'active' : '' ?>"
                    id="tab-register" onclick="switchTab('register')">Create Account</button>
        </div>

        <!-- ══════════════════════════════════════════════════════
             LOGIN FORM
             Action → auth/processLogin.php
        ═══════════════════════════════════════════════════════ -->
        <div class="auth-form <?= $active_tab === 'login' ? 'active' : '' ?>" id="form-login">
            <h2>Welcome back</h2>
            <p class="subtitle">Sign in to continue tracking your fitness journey.</p>

            <?php if ($login_error): ?>
                <div class="alert alert--error"><?= htmlspecialchars($login_error) ?></div>
            <?php endif; ?>

            <form id="login-form" method="POST" action="auth/processLogin.php" novalidate>

                <div class="form-group">
                    <label class="form-label" for="login-email">Email address</label>
                    <input class="form-input" type="email" id="login-email" name="email"
                           value="<?= $login_email ?>" required autocomplete="email"
                           placeholder="you@example.com">
                    <span class="inline-error" id="login-email-err">Please enter a valid email.</span>
                </div>

                <div class="form-group">
                    <label class="form-label" for="login-password">Password</label>
                    <input class="form-input" type="password" id="login-password" name="password"
                           required autocomplete="current-password" placeholder="••••••••">
                    <span class="inline-error" id="login-pass-err">Password is required.</span>
                </div>

                <button class="btn btn--primary" type="submit"
                        style="width:100%;margin-top:.5rem">Sign In</button>
            </form>

            <p class="auth-switch">
                Don't have an account?
                <button onclick="switchTab('register')">Create one</button>
            </p>
        </div>

        <!-- ══════════════════════════════════════════════════════
             REGISTER FORM
             Action → auth/register.php
        ═══════════════════════════════════════════════════════ -->
        <div class="auth-form <?= $active_tab === 'register' ? 'active' : '' ?>" id="form-register">
            <h2>Get started</h2>
            <p class="subtitle">Create your free FitTrack account.</p>

            <?php if ($register_error): ?>
                <div class="alert alert--error"><?= htmlspecialchars($register_error) ?></div>
            <?php endif; ?>

            <?php if ($register_ok): ?>
                <div class="alert alert--success">
                    Account created successfully! You can now sign in.
                </div>
            <?php endif; ?>

            <form id="register-form" method="POST" action="auth/register.php" novalidate>

                <div class="form-group">
                    <label class="form-label" for="reg-name">Full name</label>
                    <input class="form-input" type="text" id="reg-name" name="name"
                           value="<?= $reg_name ?>" required autocomplete="name"
                           placeholder="John Doe">
                    <span class="inline-error" id="reg-name-err">Name is required.</span>
                </div>

                <div class="form-group">
                    <label class="form-label" for="reg-email">Email address</label>
                    <input class="form-input" type="email" id="reg-email" name="email"
                           value="<?= $reg_email ?>" required autocomplete="email"
                           placeholder="you@example.com">
                    <span class="inline-error" id="reg-email-err">Enter a valid email address.</span>
                </div>

                <div class="form-group">
                    <label class="form-label" for="reg-password">
                        Password
                        <span style="font-weight:400;color:var(--gray-400);font-size:.8rem">(min. 8 characters)</span>
                    </label>
                    <input class="form-input" type="password" id="reg-password" name="password"
                           required autocomplete="new-password" placeholder="••••••••">
                    <span class="inline-error" id="reg-pass-err">Password must be at least 8 characters.</span>
                </div>

                <div class="form-group">
                    <label class="form-label" for="reg-confirm">Confirm password</label>
                    <input class="form-input" type="password" id="reg-confirm" name="confirm"
                           required autocomplete="new-password" placeholder="••••••••">
                    <span class="inline-error" id="reg-confirm-err">Passwords do not match.</span>
                </div>

                <button class="btn btn--primary" type="submit"
                        style="width:100%;margin-top:.5rem">Create Account</button>
            </form>

            <p class="auth-switch">
                Already have an account?
                <button onclick="switchTab('login')">Sign in</button>
            </p>
        </div>

    </div><!-- /.auth-card -->
</div><!-- /.auth-wrapper -->

<script src="js/auth-validation.js"></script>
<script>
    /**
     * switchTab(name) — swap between 'login' and 'register' panels.
     * Called both from the tab buttons and from the "switch" links inside each form.
     */
    function switchTab(name) {
        document.querySelectorAll('.auth-tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.auth-form').forEach(f => f.classList.remove('active'));
        document.getElementById('tab-' + name).classList.add('active');
        document.getElementById('form-' + name).classList.add('active');
    }
</script>

</body>
</html>