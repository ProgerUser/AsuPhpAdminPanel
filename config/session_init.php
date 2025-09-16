<?php
// config/session_init.php
// Настройки безопасности сессий ДО session_start()

ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_samesite', 'Strict');

// cookie_secure только если HTTPS
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
ini_set('session.cookie_secure', $isHttps ? '1' : '0');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
?>