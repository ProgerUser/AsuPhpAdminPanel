<?php
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
require_once 'config/config.php';
require_once 'config/security.php';

// Разрешаем выход как по POST (c CSRF), так и по GET (через ссылку)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        http_response_code(400);
        exit('Недопустимый токен безопасности');
    }
}

try {
    // Логирование выхода опционально (инициализируется в местах, где доступен логгер)
    
    // Очистка remember me cookies
    if (isset($_COOKIE['series_id']) || isset($_COOKIE['remember_token'])) {
        $db = getDbInstance();
        
        // Очистка токенов в базе данных
        if (isset($_SESSION['user_id'])) {
            $db->where('id', $_SESSION['user_id']);
            $db->update('admin_accounts', [
                'series_id' => null,
                'remember_token' => null,
                'expires' => null
            ]);
        }
        
        // Удаление cookies
        clearAuthCookie();
    }
    
    // Очистка сессии
    $_SESSION = array();
    
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    
    session_destroy();
    
    // Перенаправление на страницу входа
    header('Location: login.php');
    exit();
    
} catch (Exception $e) {
    http_response_code(500);
    exit('Произошла ошибка при выходе из системы');
}

?>