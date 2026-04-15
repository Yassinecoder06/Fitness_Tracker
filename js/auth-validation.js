/**
 * js/auth-validation.js
 *
 * Client-side validation for the FitTrack login and registration forms.
 *
 * ─── KEY PRINCIPLES ──────────────────────────────────────────────────────────
 *
 *  1. THIS IS UX, NOT SECURITY.
 *     JS validation gives the user instant feedback without a page reload.
 *     It is NOT a replacement for server-side validation in register.php /
 *     processLogin.php — those ALWAYS run regardless of what happens here.
 *
 *  2. SEPARATE FILE, NOT IN main.js.
 *     main.js runs on every page of the app. Auth validation is only needed
 *     on index.php. Keeping it separate avoids unnecessary overhead everywhere
 *     else, and keeps concerns cleanly separated.
 *
 *  3. IIFE PATTERN.
 *     Everything is wrapped in an Immediately Invoked Function Expression
 *     to avoid polluting the global scope with helper variables.
 *
 * ─────────────────────────────────────────────────────────────────────────────
 */

(function () {
    'use strict';

    /* ══════════════════════════════════════════════════════════════════
       HELPER UTILITIES
    ══════════════════════════════════════════════════════════════════ */

    /**
     * Show an inline error message below a field.
     * @param {string} id  - The element ID of the <span class="inline-error">
     * @param {string} msg - The message to display (pass '' to use the element's existing text)
     */
    function showErr(id, msg) {
        var el = document.getElementById(id);
        if (!el) return;
        if (msg) el.textContent = msg;
        el.style.display = 'block';
    }

    /** Hide an inline error message. */
    function hideErr(id) {
        var el = document.getElementById(id);
        if (el) el.style.display = 'none';
    }

    /** Hide all inline errors inside a form. */
    function clearAllErrors(form) {
        form.querySelectorAll('.inline-error').forEach(function (el) {
            el.style.display = 'none';
        });
    }

    /**
     * Basic email format check.
     * The browser's own :valid/:invalid is unreliable across engines,
     * so we use a simple regex that catches the most common mistakes.
     * The server does the authoritative check with PHP's filter_var().
     */
    function isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    /* ══════════════════════════════════════════════════════════════════
       LOGIN FORM VALIDATION
    ══════════════════════════════════════════════════════════════════ */

    var loginForm = document.getElementById('login-form');

    if (loginForm) {

        /* Show/hide inline errors as the user types (live feedback) */
        document.getElementById('login-email').addEventListener('input', function () {
            if (isValidEmail(this.value.trim())) hideErr('login-email-err');
        });

        document.getElementById('login-password').addEventListener('input', function () {
            if (this.value.length > 0) hideErr('login-pass-err');
        });

        /* Intercept submit and validate before the form posts */
        loginForm.addEventListener('submit', function (e) {
            clearAllErrors(loginForm);

            var email    = document.getElementById('login-email').value.trim();
            var password = document.getElementById('login-password').value;
            var hasError = false;

            if (!isValidEmail(email)) {
                showErr('login-email-err', 'Please enter a valid email address.');
                hasError = true;
            }

            if (!password) {
                showErr('login-pass-err', 'Password is required.');
                hasError = true;
            }

            if (hasError) {
                e.preventDefault(); // stop the form from submitting
            }
        });
    }

    /* ══════════════════════════════════════════════════════════════════
       REGISTRATION FORM VALIDATION
    ══════════════════════════════════════════════════════════════════ */

    var registerForm = document.getElementById('register-form');

    if (registerForm) {

        /* Live feedback while typing */
        document.getElementById('reg-name').addEventListener('input', function () {
            if (this.value.trim().length > 0) hideErr('reg-name-err');
        });

        document.getElementById('reg-email').addEventListener('input', function () {
            if (isValidEmail(this.value.trim())) hideErr('reg-email-err');
        });

        document.getElementById('reg-password').addEventListener('input', function () {
            if (this.value.length >= 8) hideErr('reg-pass-err');
            // Also re-check confirm field if the user has already touched it
            var confirm = document.getElementById('reg-confirm').value;
            if (confirm && this.value === confirm) hideErr('reg-confirm-err');
        });

        document.getElementById('reg-confirm').addEventListener('input', function () {
            var password = document.getElementById('reg-password').value;
            if (this.value === password) hideErr('reg-confirm-err');
        });

        /* Intercept submit */
        registerForm.addEventListener('submit', function (e) {
            clearAllErrors(registerForm);

            var name     = document.getElementById('reg-name').value.trim();
            var email    = document.getElementById('reg-email').value.trim();
            var password = document.getElementById('reg-password').value;
            var confirm  = document.getElementById('reg-confirm').value;
            var hasError = false;

            if (!name) {
                showErr('reg-name-err', 'Full name is required.');
                hasError = true;
            }

            if (!isValidEmail(email)) {
                showErr('reg-email-err', 'Enter a valid email address.');
                hasError = true;
            }

            if (password.length < 8) {
                showErr('reg-pass-err', 'Password must be at least 8 characters.');
                hasError = true;
            }

            if (password !== confirm) {
                showErr('reg-confirm-err', 'Passwords do not match.');
                hasError = true;
            }

            if (hasError) {
                e.preventDefault();
            }
        });
    }

})(); // end IIFE