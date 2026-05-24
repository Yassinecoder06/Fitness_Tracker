<?php
declare(strict_types=1);

function start_session_if_needed(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

function is_https_request(): bool
{
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        return true;
    }

    return isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https';
}

function clear_remember_cookie(): void
{
    $cookieSecure = is_https_request();
    setcookie('remember_token', '', [
        'expires' => time() - 42000,
        'path' => '/',
        'secure' => $cookieSecure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

function hydrate_session_from_remember_token(PDO $pdo): void
{
    if (!isset($_COOKIE['remember_token'])) {
        return;
    }

    $stmt = $pdo->prepare(
        'SELECT user_id FROM remember_tokens WHERE token = ? AND expires_at > NOW() LIMIT 1'
    );
    $stmt->execute([$_COOKIE['remember_token']]);
    $tokenData = $stmt->fetch();

    if (!$tokenData) {
        clear_remember_cookie();
        return;
    }

    $stmt = $pdo->prepare('SELECT id, name, email FROM users WHERE id = ? LIMIT 1');
    $stmt->execute([$tokenData['user_id']]);
    $user = $stmt->fetch();

    if (!$user) {
        clear_remember_cookie();
        return;
    }

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];
}

function ensure_authenticated(PDO $pdo, string $redirectPath = '/index.php'): void
{
    start_session_if_needed();

    if (empty($_SESSION['user_id'])) {
        hydrate_session_from_remember_token($pdo);
    }

    if (empty($_SESSION['user_id'])) {
        $target = urlencode($redirectPath);
        header('Location: /login.php?redirect=' . $target);
        exit;
    }
}
