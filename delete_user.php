<?php
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
require_once 'config/config.php';
require_once 'config/security.php';
require_once BASE_PATH . '/includes/auth_validate.php';

// Проверка прав доступа
if ($_SESSION['admin_type'] !== 'super') {
    $logger->log('Unauthorized deletion attempt', 'WARNING', [
        'user_id' => $_SESSION['user_id'],
        'admin_type' => $_SESSION['admin_type']
    ]);
    http_response_code(403);
    exit('Доступ запрещен');
}

// Проверка метода запроса
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Метод не разрешен');
}

// Проверка CSRF токена
if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
    $logger->log('CSRF validation failed during user deletion', 'WARNING', [
        'user_id' => $_SESSION['user_id']
    ]);
    http_response_code(400);
    exit('Недопустимый токен безопасности');
}

// Получение и валидация ID пользователя
$del_id = filter_input(INPUT_POST, 'del_id', FILTER_VALIDATE_INT);
if (!$del_id) {
    $logger->log('Invalid user ID for deletion', 'WARNING', [
        'provided_id' => $_POST['del_id'] ?? 'none'
    ]);
    $_SESSION['failure'] = "Неверный ID пользователя";
    header('Location: admin_users.php');
    exit();
}

try {
    $db = getDbInstance();
    
    // Проверка существования пользователя
    $db->where('id', $del_id);
    $user = $db->getOne('admin_accounts', 'id, user_name');
    
    if (!$user) {
        throw new Exception("Пользователь не найден");
    }
    
    // Запрет на удаление собственной учетной записи
    if ($del_id == $_SESSION['user_id']) {
        throw new Exception("Нельзя удалить собственную учетную запись");
    }
    
    // Удаление пользователя
    $db->where('id', $del_id);
    $ok = $db->delete('admin_accounts');
    
    if ($ok) {
        $logger->log('User deleted successfully', 'INFO', [
            'deleted_user_id' => $del_id,
            'deleted_by' => $_SESSION['user_id']
        ]);
        $_SESSION['success'] = "Пользователь успешно удален";
    } else {
        throw new Exception("Не удалось удалить пользователя");
    }
    
} catch (Exception $e) {
    $logger->log('Error during user deletion', 'ERROR', [
        'error' => $e->getMessage(),
        'user_id' => $del_id
    ]);
    $_SESSION['failure'] = $e->getMessage();
}

// Перенаправление обратно к списку пользователей
header('Location: admin_users.php');
exit();