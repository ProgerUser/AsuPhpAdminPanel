<?php
// config/session_init.php
// Настройки безопасности сессий ДО session_start()

ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1); // Раскомментируйте если используете HTTPS
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_samesite', 'Strict');

// Заголовки безопасности
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Content-Security-Policy: default-src 'self'");

// Запускаем сессию
session_start();
?>