<?php
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
require_once 'config/config.php';
require_once 'config/security.php';
require_once BASE_PATH . '/includes/auth_validate.php';

// Проверка прав доступа
if ($_SESSION['admin_type'] !== 'super') {
    $logger->log('Unauthorized access attempt to edit admin', 'WARNING', [
        'user_id' => $_SESSION['user_id'],
        'admin_type' => $_SESSION['admin_type']
    ]);
    http_response_code(403);
    exit('Доступ запрещен');
}

// Получение ID редактируемого пользователя
$admin_user_id = filter_input(INPUT_GET, 'admin_user_id', FILTER_VALIDATE_INT);
if (!$admin_user_id) {
    $_SESSION['failure'] = "Неверный ID пользователя";
    header('Location: admin_users.php');
    exit();
}

try {
    $db = getDbInstance();
    
    // Получение данных пользователя
    $db->where('id', $admin_user_id);
    $admin_account = $db->getOne('admin_accounts', 'id, user_name, admin_type');
    
    if (!$admin_account) {
        throw new Exception("Пользователь не найден");
    }
    
    // Запрет на редактирование собственной учетной записи
    if ($admin_user_id == $_SESSION['user_id']) {
        throw new Exception("Нельзя редактировать собственную учетную запись");
    }
    
} catch (Exception $e) {
    $logger->log('Error accessing admin account', 'ERROR', [
        'error' => $e->getMessage(),
        'admin_id' => $admin_user_id
    ]);
    $_SESSION['failure'] = $e->getMessage();
    header('Location: admin_users.php');
    exit();
}

// Генерация CSRF токена
$csrf_token = generateCSRFToken();

// Обработка POST запроса
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Проверка CSRF токена
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        $logger->log('CSRF validation failed during admin update', 'WARNING', [
            'user_id' => $_SESSION['user_id']
        ]);
        $_SESSION['failure'] = "Недопустимый токен безопасности";
        header('Location: edit_admin.php?admin_user_id=' . $admin_user_id);
        exit();
    }
    
    // Валидация входных данных
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $password = filter_input(INPUT_POST, 'password', FILTER_UNSAFE_RAW);
    $confirm_password = filter_input(INPUT_POST, 'confirm_password', FILTER_UNSAFE_RAW);
    $admin_type = filter_input(INPUT_POST, 'admin_type', FILTER_SANITIZE_STRING);
    
    $errors = [];
    
    // Проверка имени пользователя
    if (empty($username) || strlen($username) < 3 || strlen($username) > 50) {
        $errors[] = "Имя пользователя должно быть от 3 до 50 символов";
    }
    
    // Проверка пароля только если он был введен
    if (!empty($password)) {
        if (strlen($password) < 8) {
            $errors[] = "Пароль должен быть не менее 8 символов";
        } elseif ($password !== $confirm_password) {
            $errors[] = "Пароли не совпадают";
        }
    }
    
    // Проверка типа администратора
    $allowed_types = ['super', 'admin'];
    if (!in_array($admin_type, $allowed_types)) {
        $errors[] = "Недопустимый тип администратора";
    }
    
    if (empty($errors)) {
        try {
            $db = getDbInstance();
            
            // Проверка существования пользователя с таким именем
            $db->where('user_name', $username);
            $db->where('id', $admin_user_id, '!=');
            $exists = $db->getOne('admin_accounts', 'id');
            if ($exists) {
                throw new Exception("Пользователь с таким именем уже существует");
            }
            
            // Подготовка SQL запроса
            if (!empty($password)) {
                // Обновление с паролем
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $db->where('id', $admin_user_id);
                $ok = $db->update('admin_accounts', [
                    'user_name' => $username,
                    'password' => $hashed_password,
                    'admin_type' => $admin_type
                ]);
            } else {
                // Обновление без пароля
                $db->where('id', $admin_user_id);
                $ok = $db->update('admin_accounts', [
                    'user_name' => $username,
                    'admin_type' => $admin_type
                ]);
            }
            
            if ($ok !== false) {
                $logger->log('Admin account updated', 'INFO', [
                    'updated_by' => $_SESSION['user_id'],
                    'admin_id' => $admin_user_id,
                    'username' => $username,
                    'admin_type' => $admin_type
                ]);
                $_SESSION['success'] = "Данные администратора успешно обновлены";
                header('Location: admin_users.php');
                exit();
            } else {
                throw new Exception("Не удалось обновить данные администратора");
            }
            
        } catch (Exception $e) {
            $logger->log('Error updating admin account', 'ERROR', [
                'error' => $e->getMessage(),
                'admin_id' => $admin_user_id
            ]);
            $errors[] = $e->getMessage();
        }
    }
}

include BASE_PATH . '/includes/header.php';
?>

<div id="page-wrapper">
    <div class="row">
        <div class="col-lg-12">
            <h2 class="page-header">Редактирование администратора</h2>
        </div>
    </div>
    
    <?php include BASE_PATH . '/includes/flash_messages.php'; ?>
    
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-lg-6">
            <form class="form" action="" method="post">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                
                <div class="form-group">
                    <label for="username">Имя пользователя *</label>
                    <input type="text" name="username" id="username" class="form-control" 
                           value="<?php echo htmlspecialchars($admin_account['user_name']); ?>"
                           maxlength="50" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Новый пароль</label>
                    <input type="password" name="password" id="password" class="form-control" 
                           minlength="8">
                    <p class="help-block">Оставьте пустым, если не хотите менять пароль. Минимум 8 символов.</p>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Подтверждение нового пароля</label>
                    <input type="password" name="confirm_password" id="confirm_password" 
                           class="form-control" minlength="8">
                </div>
                
                <div class="form-group">
                    <label for="admin_type">Тип администратора *</label>
                    <select name="admin_type" id="admin_type" class="form-control" required>
                        <option value="">Выберите тип</option>
                        <option value="admin" <?php echo ($admin_account['admin_type'] === 'admin') ? 'selected' : ''; ?>>
                            Администратор
                        </option>
                        <option value="super" <?php echo ($admin_account['admin_type'] === 'super') ? 'selected' : ''; ?>>
                            Супер-администратор
                        </option>
                    </select>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Сохранить</button>
                    <a href="admin_users.php" class="btn btn-default">Отмена</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include BASE_PATH . '/includes/footer.php'; ?>