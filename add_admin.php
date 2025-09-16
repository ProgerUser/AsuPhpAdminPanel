<?php
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
require_once 'config/config.php';
require_once 'config/security.php';
require_once BASE_PATH . '/includes/auth_validate.php';

// Проверка прав доступа
if ($_SESSION['admin_type'] !== 'super') {
    $logger->log('Unauthorized access attempt to add admin', 'WARNING', [
        'user_id' => $_SESSION['user_id'],
        'admin_type' => $_SESSION['admin_type']
    ]);
    http_response_code(403);
    exit('Доступ запрещен');
}

// Генерация CSRF токена
$csrf_token = generateCSRFToken();

// Обработка POST запроса
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Проверка CSRF токена
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        $logger->log('CSRF validation failed during admin creation', 'WARNING', [
            'user_id' => $_SESSION['user_id']
        ]);
        $_SESSION['failure'] = "Недопустимый токен безопасности";
        header('Location: add_admin.php');
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
    
    // Проверка пароля
    if (empty($password) || strlen($password) < 8) {
        $errors[] = "Пароль должен быть не менее 8 символов";
    } elseif ($password !== $confirm_password) {
        $errors[] = "Пароли не совпадают";
    }
    
    // Проверка типа администратора
    $allowed_types = ['super', 'admin'];
    if (!in_array($admin_type, $allowed_types)) {
        $errors[] = "Недопустимый тип администратора";
    }
    
    if (empty($errors)) {
        try {
            $db = getDbInstance();
            
            // Проверка существования пользователя
            $db->where('user_name', $username);
            $existing = $db->getOne('admin_accounts', 'id');
            if ($existing) {
                throw new Exception("Пользователь с таким именем уже существует");
            }
            
            // Хеширование пароля
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Добавление пользователя
            $insertId = $db->insert('admin_accounts', [
                'user_name' => $username,
                'password' => $hashed_password,
                'admin_type' => $admin_type
            ]);
            
            if ($insertId) {
                $logger->log('New admin created', 'INFO', [
                    'created_by' => $_SESSION['user_id'],
                    'username' => $username,
                    'admin_type' => $admin_type
                ]);
                $_SESSION['success'] = "Администратор успешно добавлен";
                header('Location: admin_users.php');
                exit();
            } else {
                throw new Exception("Не удалось добавить администратора");
            }
            
        } catch (Exception $e) {
            $logger->log('Error during admin creation', 'ERROR', [
                'error' => $e->getMessage(),
                'username' => $username
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
            <h2 class="page-header">Добавление администратора</h2>
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
                           value="<?php echo htmlspecialchars($username ?? ''); ?>"
                           maxlength="50" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Пароль *</label>
                    <input type="password" name="password" id="password" class="form-control" 
                           minlength="8" required>
                    <p class="help-block">Минимум 8 символов</p>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Подтверждение пароля *</label>
                    <input type="password" name="confirm_password" id="confirm_password" 
                           class="form-control" minlength="8" required>
                </div>
                
                <div class="form-group">
                    <label for="admin_type">Тип администратора *</label>
                    <select name="admin_type" id="admin_type" class="form-control" required>
                        <option value="">Выберите тип</option>
                        <option value="admin" <?php echo (isset($admin_type) && $admin_type === 'admin') ? 'selected' : ''; ?>>
                            Администратор
                        </option>
                        <option value="super" <?php echo (isset($admin_type) && $admin_type === 'super') ? 'selected' : ''; ?>>
                            Супер-администратор
                        </option>
                    </select>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Добавить</button>
                    <a href="admin_users.php" class="btn btn-default">Отмена</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include BASE_PATH . '/includes/footer.php'; ?>