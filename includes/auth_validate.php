<?php
// includes/auth_validate.php

// Правильный порядок включения файлов (абсолютные пути)
require_once dirname(__DIR__) . '/config/session_init.php'; // Сначала настройки сессии
require_once dirname(__DIR__) . '/config/config.php';       // Потом конфиг
require_once dirname(__DIR__) . '/config/security.php';     // Потом безопасность

// Инициализируем систему безопасности
$security = initSecurity();
$logger = $security['logger'];
$bruteforce = $security['bruteforce'];

// Проверка авторизации
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== TRUE) {
    $logger->log('Unauthorized access attempt', 'WARNING', [
        'ip' => $_SERVER['REMOTE_ADDR'],
        'uri' => $_SERVER['REQUEST_URI']
    ]);

    // Сохраняем URL для перенаправления после входа
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];

    header('Location: login.php');
    exit();
}

// Проверка remember me cookie
if (isset($_COOKIE['series_id']) && isset($_COOKIE['remember_token'])) {
    $series_id = filter_var($_COOKIE['series_id'], FILTER_SANITIZE_STRING);
    $remember_token = filter_var($_COOKIE['remember_token'], FILTER_SANITIZE_STRING);

    try {
        $db = getDbInstance();
        $db->where('series_id', $series_id);
        $row = $db->getOne('admin_accounts');

        if ($row) {
            if (password_verify($remember_token, $row['remember_token'])) {
                $expires = strtotime($row['expires']);

                if (strtotime('now') > $expires) {
                    // Очистка устаревших токенов
                    $db->where('id', $row['id']);
                    $db->update('admin_accounts', [
                        'series_id' => null,
                        'remember_token' => null,
                        'expires' => null
                    ]);

                    setcookie('series_id', '', [
                        'expires' => time() - 3600,
                        'path' => '/',
                        'secure' => true,
                        'httponly' => true,
                        'samesite' => 'Strict'
                    ]);
                    setcookie('remember_token', '', [
                        'expires' => time() - 3600,
                        'path' => '/',
                        'secure' => true,
                        'httponly' => true,
                        'samesite' => 'Strict'
                    ]);

                    $logger->log('Remember me token expired', 'INFO', ['user_id' => $row['id']]);
                } else {
                    // Обновление сессии
                    regenerateSession();
                    $_SESSION['user_logged_in'] = TRUE;
                    $_SESSION['admin_type'] = $row['admin_type'];
                    $_SESSION['user_id'] = $row['id'];
                    $_SESSION['user_name'] = $row['user_name'];

                    $logger->log('Session refreshed via remember me', 'INFO', ['user_id' => $row['id']]);
                }
            } else {
                // Неверный токен - очистка cookies
                clearAuthCookie();

                $logger->log('Invalid remember me token', 'WARNING', ['user_id' => $row['id']]);
            }
        }
    } catch (Exception $e) {
        $logger->log('Error validating remember me token', 'ERROR', [
            'error' => $e->getMessage(),
            'user_id' => $_SESSION['user_id'] ?? 'unknown'
        ]);
    }
}

// Проверка IP-адреса
if (isset($_SESSION['last_ip']) && $_SESSION['last_ip'] !== $_SERVER['REMOTE_ADDR']) {
    $logger->log('IP address changed during session', 'WARNING', [
        'user_id' => $_SESSION['user_id'],
        'old_ip' => $_SESSION['last_ip'],
        'new_ip' => $_SERVER['REMOTE_ADDR']
    ]);
    // Можно добавить дополнительную проверку или действия здесь
}
$_SESSION['last_ip'] = $_SERVER['REMOTE_ADDR'];

// Проверка User-Agent
if (isset($_SESSION['user_agent']) && $_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
    $logger->log('User-Agent changed during session', 'WARNING', [
        'user_id' => $_SESSION['user_id'],
        'old_ua' => $_SESSION['user_agent'],
        'new_ua' => $_SERVER['HTTP_USER_AGENT']
    ]);
    // Можно добавить дополнительную проверку или действия здесь
}
$_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];

?>