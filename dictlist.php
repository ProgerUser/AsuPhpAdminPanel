<?php
session_start();
require_once 'config/config.php';
require_once BASE_PATH . '/includes/auth_validate.php';

// WordList class
require_once BASE_PATH . '/lib/DictList/DictList.php';
$dictlist = new DictList();

// Get Input data from query string
$search_string = filter_input(INPUT_GET, 'search_string');
$filter_col = filter_input(INPUT_GET, 'filter_col');
$order_by = filter_input(INPUT_GET, 'order_by');

// Per page limit for pagination.
$pagelimit = 15;

// Get current page.
$page = filter_input(INPUT_GET, 'page');
if (!$page) {
    $page = 1;
}

// If filter types are not selected we show latest added data first
if (!$filter_col) {
    $filter_col = 'id';
}
if (!$order_by) {
    $order_by = 'Asc';
}

//Get DB instance. i.e instance of MYSQLiDB Library
$db = getDbInstance();
$select = array('id', 'name', 'dict_author', 'year_pub', 'created_at', 'updated_at');

//Start building query according to input parameters.
// If search string
if ($search_string) {
    $db->where('name', '%' . $search_string . '%', 'like');
    $db->orwhere('dict_author', '%' . $search_string . '%', 'like');
}

//If order by option selected
if ($order_by) {
    $db->orderBy($filter_col, $order_by);
}

// Set pagination limit
$db->pageLimit = $pagelimit;

// Get result of the query.
$rows = $db->arraybuilder()->paginate('dict_list', $page, $select);
$total_pages = $db->totalPages;

include BASE_PATH . '/includes/header.php';
?>
<!-- Main container -->
<div id="page-wrapper">
    <div class="row">
        <div class="col-lg-6">
            <h1 class="page-header">Словари</h1>
        </div>
        <div class="col-lg-6">
            <div class="page-action-links text-right">
                <a href="add_dictlist.php?operation=create" class="btn btn-success"><i
                            class="glyphicon glyphicon-plus"></i> Добавить</a>
            </div>
        </div>
    </div>
    <?php include BASE_PATH . '/includes/flash_messages.php'; ?>

    <!-- Filters -->
    <div class="well text-center filter-form">
        <form class="form form-inline" action="">
            <label for="input_search">Поиск</label>
            <input type="text" class="form-control" id="input_search" name="search_string"
                   value="<?php echo xss_clean($search_string); ?>">
            <label for="input_order">Сортировка</label>
            <select name="filter_col" class="form-control">
                <?php
                foreach ($dictlist->setOrderingValues() as $opt_value => $opt_name):
                    ($order_by === $opt_value) ? $selected = 'selected' : $selected = '';
                    echo ' <option value="' . $opt_value . '" ' . $selected . '>' . $opt_name . '</option>';
                endforeach;
                ?>
            </select>
            <select name="order_by" class="form-control" id="input_order">
                <option value="Asc" <?php
                if ($order_by == 'Asc') {
                    echo 'selected';
                }
                ?> >Asc
                </option>
                <option value="Desc" <?php
                if ($order_by == 'Desc') {
                    echo 'selected';
                }
                ?>>Desc
                </option>
            </select>
            <input type="submit" value="Go" class="btn btn-primary">
        </form>
    </div>
    <hr>
    <!-- //Filters -->


    <div id="export-section">
        <a href="export_dictlist.php">
            <button class="btn btn-sm btn-primary">Экспортировать в CSV <i class="glyphicon glyphicon-export"></i>
            </button>
        </a>
    </div>

    <!-- Table -->
    <table class="table table-striped table-bordered table-condensed">
        <thead>
        <tr>
            <th width="5%">ID</th>
            <th width="45%">Название</th>
            <th width="20%">Автор</th>
            <th width="20%">Дата публикации</th>
            <th width="10%">Действия</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $row): ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo xss_clean($row['name']); ?></td>
                <td><?php echo xss_clean($row['dict_author']); ?></td>
                <td><?php echo xss_clean($row['year_pub']); ?></td>
                <td>
                    <a href="edit_dictlist.php?customer_id=<?php echo $row['id']; ?>&operation=edit"
                       class="btn btn-primary"><i class="glyphicon glyphicon-edit"></i></a>
                    <a href="#" class="btn btn-danger delete_btn" data-toggle="modal"
                       data-target="#confirm-delete-<?php echo $row['id']; ?>"><i class="glyphicon glyphicon-trash"></i></a>
                </td>
            </tr>
            <!-- Delete Confirmation Modal -->
            <div class="modal fade" id="confirm-delete-<?php echo $row['id']; ?>" role="dialog">
                <div class="modal-dialog">
                    <form action="delete_dictlist.php" method="POST">
                        <!-- Modal content -->
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                <h4 class="modal-title">Подтвердить</h4>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="del_id" id="del_id" value="<?php echo $row['id']; ?>">
                                <p>Вы уверены, что хотите удалить эту строку??</p>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-default pull-left">Да</button>
                                <button type="button" class="btn btn-default" data-dismiss="modal">Нет</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <!-- //Delete Confirmation Modal -->
        <?php endforeach; ?>
        </tbody>
    </table>
    <!-- //Table -->

    <!-- Pagination -->
    <div class="text-center">
        <?php echo paginationLinks($page, $total_pages, 'dictlist.php'); ?>
    </div>
    <!-- //Pagination -->
</div>
<!-- //Main container -->
<?php include BASE_PATH . '/includes/footer.php'; ?>
