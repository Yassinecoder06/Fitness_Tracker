<?php
/**
 * auth/logout.php — Destroys the user session completely.
 *
 * Steps:
 *  1. Start the existing session (we need to open it before we can destroy it).
 *  2. Clear all session data from memory.
 *  3. Expire the session cookie in the browser.
 *  4. Destroy the session record on the server.
 *  5. Redirect to the login page.
 *
 * Best practice tip for production:
 *  Protect this endpoint with a POST + CSRF token so that attackers
 *  cannot force a logout via a crafted link (CSRF logout attack).
 *  For this academic project scope, a simple GET redirect is acceptable.
 */

session_start();

/* Step 1 — Wipe all values from the $_SESSION superglobal */
$_SESSION = [];

/* Step 2 — Expire the cookie in the browser immediately */
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',                    // empty value
        time() - 42000,        // timestamp in the past = browser deletes it
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

/* Step 3 — Destroy the session record on the server */
session_destroy();

/* Step 4 — Send user back to the login page */
header('Location: /../index.php');
exit;