<?php
session_start();
require_once __DIR__ . '/backend/bootstrap.php';
require_once __DIR__ . '/backend/db.php';
$pdo = get_pdo();

function is_https_request(): bool {
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        return true;
    }
    return (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
}
if (isset($_COOKIE['remember_token'])) {
    $stmt = $pdo->prepare("DELETE FROM remember_tokens WHERE token = ?");
    $stmt->execute([$_COOKIE['remember_token']]);
}
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path']);
}
session_destroy();
$cookieSecure = is_https_request();
setcookie('remember_token', '', [
    'expires' => time() - 42000,
    'path' => '/',
    'secure' => $cookieSecure,
    'httponly' => true,
    'samesite' => 'Lax',
]);
header('Location: login.php');
exit;