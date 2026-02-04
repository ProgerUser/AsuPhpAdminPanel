<?php
session_start();
require_once 'config/config.php';
require_once BASE_PATH . '/includes/auth_validate.php';

// WordList class
require_once BASE_PATH . '/lib/WordList/WordList.php';
$wordlist = new WordList();

// Get Input data from query string
$search_string = filter_input(INPUT_GET, 'search_string');
$srch_dict = filter_input(INPUT_GET, 'srch_dict');
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
$select = array('id', 'word', 'translate', 'dict_ref', 'dict_name', 'created_at', 'updated_at');

//Start building query according to input parameters.
// If search string
if ($search_string) {
    $db->where('word', '%' . $search_string . '%', 'like');
    $db->orwhere('translate', '%' . $search_string . '%', 'like');
}
if ($srch_dict) {
    $db->where('dict_ref', $srch_dict, '=');
}

//If order by option selected
if ($order_by) {
    $db->orderBy($filter_col, $order_by);
}

// Set pagination limit
$db->pageLimit = $pagelimit;

// Get result of the query.
$rows = $db->arraybuilder()->paginate('v_word_list', $page, $select);
$total_pages = $db->totalPages;


include BASE_PATH . '/includes/header.php';
?>
<!-- Main container -->
<div id="page-wrapper">
    <div class="row">
        <div class="col-lg-8">
            <h1 class="page-header">Словари</h1>
        </div>
        <div class="col-lg-4">
            <div class="page-action-links text-right">
                <a href="add_wordlist.php?operation=create" class="btn btn-success"><i
                            class="glyphicon glyphicon-plus"></i> Добавить</a>
            </div>
        </div>
    </div>
    <?php include BASE_PATH . '/includes/flash_messages.php'; ?>

    <!-- Панель действий -->
    <div class="row" style="margin-bottom: 20px;">
        <div class="col-lg-12">
            <div class="panel panel-default">
                <div class="panel-body" style="padding: 15px;">
                    <div class="row">
                        <div class="col-md-4">
                            <a href="import_word.php" class="btn btn-info btn-block">
                                <i class="glyphicon glyphicon-import"></i> Импорт из Excel
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="export_wordlist.php" class="btn btn-info btn-block">
                                <i class="glyphicon glyphicon-export"></i> Экспорт в CSV
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="add_wordlist.php?operation=create" class="btn btn-success btn-block">
                                <i class="glyphicon glyphicon-plus"></i> Добавить слово
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="well filter-form">
        <form class="form" action="" method="get">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="input_search_dict">ID словаря</label>
                        <input type="text" class="form-control" id="input_search_dict" name="srch_dict"
                               placeholder="Напр.: 12" value="<?php echo xss_clean($srch_dict); ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="input_search">Поиск</label>
                        <input type="text" class="form-control" id="input_search" name="search_string"
                               placeholder="Слово или перевод" value="<?php echo xss_clean($search_string); ?>">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="input_order">Сортировка</label>
                        <select name="filter_col" class="form-control">
                            <?php
                            foreach ($wordlist->setOrderingValues() as $opt_value => $opt_name):
                                ($order_by === $opt_value) ? $selected = 'selected' : $selected = '';
                                echo ' <option value="' . $opt_value . '" ' . $selected . '>' . $opt_name . '</option>';
                            endforeach;
                            ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="input_order">Порядок</label>
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
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <div>
                            <button type="submit" class="btn btn-primary" style="width: 100%;">Применить</button>
                            <a href="wordlist.php" class="btn btn-default" style="width: 100%; margin-top: 5px;">Сброс</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <hr>
    <!-- //Filters -->


    <!-- Table -->
    <table class="table table-striped table-bordered table-condensed">
        <thead>
        <tr>
            <th width="5%">ID</th>
            <th width="25%">Слово</th>
            <th width="35%">Перевод</th>
            <th width="5%">Словарь ID</th>
            <th width="20%">Словарь Наименование</th>
            <th width="10%">Действия</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $row): ?>
            <tr id="row-<?php echo $row['id']; ?>">
                <td><?php echo $row['id']; ?></td>
                <td><?php echo xss_clean($row['word']); ?></td>
                <td><?php echo htmlspecialchars_decode($row['translate'], ENT_QUOTES); ?></td>
                <td><?php echo xss_clean($row['dict_ref']); ?></td>
                <td><?php echo xss_clean($row['dict_name']); ?></td>
                <td>
                    <?php
                        $return_params = $_GET;
                        // Обязательно передаём текущую страницу
                        $return_params['page'] = $page;
                        $return_qs = http_build_query($return_params);
                        $return_url = 'wordlist.php' . ($return_qs ? ('?' . $return_qs) : '') . '#row-' . $row['id'];
                        $edit_href = 'edit_wordlist.php?customer_id=' . $row['id'] . '&operation=edit&return=' . rawurlencode($return_url);
                    ?>
                    <a href="<?php echo $edit_href; ?>"
                       class="btn btn-primary"><i class="glyphicon glyphicon-edit"></i></a>
                    <a href="#" class="btn btn-danger delete_btn" data-toggle="modal"
                       data-target="#confirm-delete-<?php echo $row['id']; ?>"><i class="glyphicon glyphicon-trash"></i></a>
                </td>
            </tr>
            <!-- Delete Confirmation Modal -->
            <div class="modal fade" id="confirm-delete-<?php echo $row['id']; ?>" role="dialog">
                <div class="modal-dialog">
                    <form action="delete_wordlist.php" method="POST">
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
        <?php echo paginationLinks($page, $total_pages, 'wordlist.php'); ?>
    </div>
    <!-- //Pagination -->
<script>
// Автодополнение для поля поиска
(function(){
    if (typeof $ !== 'undefined' && typeof $.fn.autocomplete !== 'undefined') {
        $('#input_search').autocomplete({
            minLength: 2,
            delay: 150,
            source: function(request, response){
                $.getJSON('search_word_suggest.php', { term: request.term }, function(data){
                    response($.map(data, function(item){
                        return {
                            label: item.label,
                            value: item.value,
                            dict_name: item.dict_name || '',
                            dict_author: item.dict_author || ''
                        };
                    }));
                });
            },
            position: { my: 'left top+6', at: 'left bottom', of: '#input_search' }
        }).autocomplete('instance')._renderItem = function(ul, item) {
            var dn = item.dict_name ? ('<div class="sugg-dict">' + $('<div>').text(item.dict_name).html() + '</div>') : '';
            var da = item.dict_author ? ('<div class="sugg-author">' + $('<div>').text(item.dict_author).html() + '</div>') : '';
            var word = $('<div>').text(item.label).html();
            var html = '<div class="sugg-item"><div class="sugg-word">' + word + '</div>' + dn + da + '</div>';
            return $('<li class="sugg-li">').append(html).appendTo(ul);
        };
    }
})();
</script>
</div>
<!-- //Main container -->
<?php include BASE_PATH . '/includes/footer.php'; ?>
