<?php
require_once 'config/config.php';
require_once 'config/security.php';
require_once 'includes/session_handler.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Если пользователь уже авторизован, перенаправляем на главную
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === TRUE) {
    header('Location: index.php');
    exit;
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
                    clearAuthCookie();
                    header('Location: login.php');
                    exit;
                }

                regenerateSession();
                $_SESSION['user_logged_in'] = TRUE;
                $_SESSION['admin_type'] = $row['admin_type'];
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['user_name'] = $row['user_name'];
                header('Location: index.php');
                exit;
            }
        }
        
        clearAuthCookie();
    } catch (Exception $e) {
        clearAuthCookie();
    }
}

// Генерация CSRF токена для формы
$csrf_token = generateCSRFToken();

include BASE_PATH . '/includes/header.php';
?>

<div id="page-" class="col-md-4 col-md-offset-4">
    <form class="form loginform" method="POST" action="authenticate.php">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
        
        <div class="login-panel panel panel-default">
            <div class="panel-heading">Вход в систему</div>
            <div class="panel-body">
                <div class="form-group">
                    <label class="control-label">Логин</label>
                    <input type="text" name="username" class="form-control" required="required" 
                           pattern="[a-zA-Z0-9_-]{3,16}" 
                           title="Логин должен содержать от 3 до 16 символов (буквы, цифры, - и _)"
                           autocomplete="username">
                </div>
                <div class="form-group">
                    <label class="control-label">Пароль</label>
                    <input type="password" name="passwd" class="form-control" required="required"
                           pattern=".{8,}" 
                           title="Пароль должен содержать минимум 8 символов"
                           autocomplete="current-password">
                </div>
                <div class="checkbox">
                    <label>
                        <input name="remember" type="checkbox" value="1">Запомнить меня
                    </label>
                </div>
                <?php if (isset($_SESSION['login_failure'])): ?>
                    <div class="alert alert-danger alert-dismissable fade in">
                        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                        <?php
                        echo htmlspecialchars($_SESSION['login_failure']);
                        unset($_SESSION['login_failure']);
                        ?>
                    </div>
                <?php endif; ?>
                <button type="submit" class="btn btn-success loginField">Войти</button>
            </div>
        </div>
    </form>
</div>

<?php include BASE_PATH . '/includes/footer.php'; ?>
