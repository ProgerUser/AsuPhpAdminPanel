<?php
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
require_once 'config/config.php';
require_once 'config/security.php';
require_once BASE_PATH . '/includes/auth_validate.php';
require_once BASE_PATH . '/lib/Users/Users.php';

// Инициализация класса Users
$users = new Users();

// Проверка прав доступа
if ($_SESSION['admin_type'] !== 'super') {
    $logger->log('Unauthorized access attempt to admin users', 'WARNING', [
        'user_id' => $_SESSION['user_id'],
        'admin_type' => $_SESSION['admin_type']
    ]);
    http_response_code(403);
    exit('Доступ запрещен');
}

// Получение параметров
$search_string = filter_input(INPUT_GET, 'search_string', FILTER_SANITIZE_STRING);
$filter_col = filter_input(INPUT_GET, 'filter_col', FILTER_SANITIZE_STRING);
$order_by = filter_input(INPUT_GET, 'order_by', FILTER_SANITIZE_STRING);
$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);
$pagelimit = 20;

// Валидация параметров
if (!$page) {
    $page = 1;
}

// Проверка допустимых значений для сортировки
$allowed_columns = ['id', 'user_name', 'admin_type'];
if (!in_array($filter_col, $allowed_columns)) {
    $filter_col = 'id';
}

$allowed_order = ['Asc', 'Desc'];
if (!in_array($order_by, $allowed_order)) {
    $order_by = 'Desc';
}

try {
    $db = getDbInstance();
    
    // Построение запроса через MysqliDb
    if ($search_string) {
        $db->where('user_name', "%{$search_string}%", 'LIKE');
    }
    $db->orderBy($filter_col, $order_by);
    $offset = ($page - 1) * $pagelimit;
    $rows = $db->withTotalCount()->get('admin_accounts', [$offset, $pagelimit], ['id','user_name','admin_type']);
    $total_rows = $db->totalCount;
    $total_pages = ceil($total_rows / $pagelimit);
    
} catch (Exception $e) {
    $logger->log('Error in admin users list', 'ERROR', ['error' => $e->getMessage()]);
    exit('Произошла ошибка при получении списка пользователей');
}

// Генерация CSRF токена для форм
$csrf_token = generateCSRFToken();

include BASE_PATH . '/includes/header.php';
?>

<!-- Main container -->
<div id="page-wrapper">
    <div class="row">
        <div class="col-lg-6">
            <h1 class="page-header">Пользователи-администраторы</h1>
        </div>
        <div class="col-lg-6">
            <div class="page-action-links text-right">
                <a href="add_admin.php" class="btn btn-success"><i class="glyphicon glyphicon-plus"></i> Добавить</a>
            </div>
        </div>
    </div>
    
    <?php include BASE_PATH . '/includes/flash_messages.php'; ?>
    
    <!-- Filters -->
    <div class="well text-center filter-form">
        <form class="form form-inline" action="">
            <label for="input_search">Поиск</label>
            <input type="text" class="form-control" id="input_search" name="search_string" 
                   value="<?php echo htmlspecialchars($search_string, ENT_QUOTES, 'UTF-8'); ?>">
            <label for="input_order">Сортировать по</label>
            <select name="filter_col" class="form-control">
                <?php
                foreach ($users->setOrderingValues() as $opt_value => $opt_name):
                    $selected = ($filter_col === $opt_value) ? 'selected' : '';
                    echo '<option value="' . htmlspecialchars($opt_value) . '" ' . $selected . '>' . 
                         htmlspecialchars($opt_name) . '</option>';
                endforeach;
                ?>
            </select>
            <select name="order_by" class="form-control" id="input_order">
                <option value="Asc" <?php echo ($order_by == 'Asc') ? 'selected' : ''; ?>>По возрастанию</option>
                <option value="Desc" <?php echo ($order_by == 'Desc') ? 'selected' : ''; ?>>По убыванию</option>
            </select>
            <input type="submit" value="Применить" class="btn btn-primary">
        </form>
    </div>
    
    <!-- Table -->
    <table class="table table-striped table-bordered table-condensed">
        <thead>
        <tr>
            <th width="5%">ID</th>
            <th width="45%">Имя пользователя</th>
            <th width="40%">Тип администратора</th>
            <th width="10%">Действия</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $row): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['id']); ?></td>
                <td><?php echo htmlspecialchars($row['user_name']); ?></td>
                <td><?php echo htmlspecialchars($row['admin_type']); ?></td>
                <td>
                    <a href="edit_admin.php?admin_user_id=<?php echo $row['id']; ?>&operation=edit"
                       class="btn btn-primary"><i class="glyphicon glyphicon-edit"></i></a>
                    <a href="#" class="btn btn-danger delete_btn" data-toggle="modal"
                       data-target="#confirm-delete-<?php echo $row['id']; ?>">
                        <i class="glyphicon glyphicon-trash"></i>
                    </a>
                </td>
            </tr>
            <!-- Delete Confirmation Modal -->
            <div class="modal fade" id="confirm-delete-<?php echo $row['id']; ?>" role="dialog">
                <div class="modal-dialog">
                    <form action="delete_user.php" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                <h4 class="modal-title">Подтверждение удаления</h4>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="del_id" value="<?php echo $row['id']; ?>">
                                <p>Вы уверены, что хотите удалить этого пользователя?</p>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-danger pull-left">Да</button>
                                <button type="button" class="btn btn-default" data-dismiss="modal">Нет</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
        </tbody>
    </table>
    
    <!-- Pagination -->
    <div class="text-center">
        <?php if ($total_pages > 1): ?>
            <ul class="pagination">
                <?php
                $get_params = $_GET;
                unset($get_params['page']);
                $get_string = http_build_query($get_params);
                $get_string = $get_string ? "?{$get_string}&" : '?';
                
                for ($i = 1; $i <= $total_pages; $i++):
                    $active = ($page == $i) ? ' class="active"' : '';
                    echo "<li{$active}><a href=\"admin_users.php{$get_string}page={$i}\">{$i}</a></li>";
                endfor;
                ?>
            </ul>
        <?php endif; ?>
    </div>
</div>

<?php include BASE_PATH . '/includes/footer.php'; ?>
