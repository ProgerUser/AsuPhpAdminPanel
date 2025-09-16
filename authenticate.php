<?php
require_once './config/config.php';
require_once './config/security.php';
require_once './includes/session_handler.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Проверка метода запроса
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die('Method Not allowed');
}

// Проверка CSRF токена
if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
    header('Location: login.php?error=invalid_request');
    exit;
}

// Получение и валидация входных данных
$username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
$password = filter_input(INPUT_POST, 'passwd', FILTER_UNSAFE_RAW);
$remember = filter_input(INPUT_POST, 'remember', FILTER_VALIDATE_BOOLEAN);

if (!$username || !$password) {
    $_SESSION['login_failure'] = "Пожалуйста, заполните все поля";
    header('Location: login.php');
    exit;
}

// Проверка на брутфорс
$ip = $_SERVER['REMOTE_ADDR'];

// Инициализация модулей безопасности
$security = initSecurity();
$bruteforce = $security['bruteforce'];

if ($bruteforce->isBlocked($username, $ip)) {
    $_SESSION['login_failure'] = "Слишком много попыток входа. Попробуйте позже.";
    header('Location: login.php');
    exit;
}

try {
    // Получение пользователя из базы данных (MysqliDb)
    $db = getDbInstance();
    $db->where('user_name', $username);
    $row = $db->getOne('admin_accounts');

    if ($row && password_verify($password, $row['password'])) {
        // Успешная аутентификация
        regenerateSession();
        
        $_SESSION['user_logged_in'] = TRUE;
        $_SESSION['admin_type'] = $row['admin_type'];
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['user_name'] = $row['user_name'];

        // Обработка "запомнить меня"
        if ($remember) {
            $series_id = bin2hex(random_bytes(16));
            $remember_token = bin2hex(random_bytes(20));
            $hashed_token = password_hash($remember_token, PASSWORD_DEFAULT);

            $expiry_time = date('Y-m-d H:i:s', strtotime('+30 days'));
            $expires = strtotime($expiry_time);

            // Установка безопасных куки
            setcookie('series_id', $series_id, [
                'expires' => $expires,
                'path' => '/',
                'secure' => true,
                'httponly' => true,
                'samesite' => 'Strict'
            ]);
            setcookie('remember_token', $remember_token, [
                'expires' => $expires,
                'path' => '/',
                'secure' => true,
                'httponly' => true,
                'samesite' => 'Strict'
            ]);

            // Обновление токена в базе (MysqliDb)
            $db->where('id', $row['id']);
            $db->update('admin_accounts', [
                'series_id' => $series_id,
                'remember_token' => $hashed_token,
                'expires' => $expiry_time
            ]);
        }
        header('Location: index.php');
        exit;
    } else {
        // Неудачная попытка входа
        $bruteforce->addAttempt($username, $ip);
        $_SESSION['login_failure'] = "Неверные учетные данные";
        header('Location: login.php');
        exit;
    }
} catch (Exception $e) {
    $_SESSION['login_failure'] = "Произошла ошибка. Попробуйте позже.";
    header('Location: login.php');
    exit;
}