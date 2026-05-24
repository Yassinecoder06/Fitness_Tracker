<?php
session_start();
require_once __DIR__ . '/backend/bootstrap.php';
require_once __DIR__ . '/backend/db.php';
$pdo = get_pdo();
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
setcookie('remember_token', '', time() - 42000, '/');
header('Location: login.php');
exit;