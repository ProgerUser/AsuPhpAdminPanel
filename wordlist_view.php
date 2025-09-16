<?php
session_start();
require_once 'config/config.php';
//require_once BASE_PATH . '/includes/auth_validate.php';

// WordList class
require_once BASE_PATH . '/lib/WordList/WordList.php';
require_once BASE_PATH . '/lib/DictList/DictList.php';
$wordlist = new WordList();

/*DICT*/
$dictlist = new DictList();
// Get Input data from query string
$search_dict = filter_input(INPUT_GET, 'srch_dict');
// Per page limit for pagination.
$pagelimit = 15;
// Get current page.
$page_dict = filter_input(INPUT_GET, 'page');

if (!$page_dict) {
    $page_dict = 1;
}
//Get DB instance. i.e instance of MYSQLiDB Library
$db = getDbInstance();
$select_dict = array('id', 'name', 'dict_author', 'year_pub', 'created_at', 'updated_at', 'wordcnt');
// Set pagination limit
$db->pageLimit = $pagelimit;
// Get result of the query.
$rows_dict = $db->arraybuilder()->paginate('v_dict_list', 1, $select_dict);
$total_pages_dict = $db->totalPages;
/*DICT*/


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
$select = array('id', 'word', 'word_clean', 'translate', 'dict_ref', 'dict_name', 'created_at', 'updated_at', 'dict_author');

//Start building query according to input parameters.
// If search string
if ($search_string) {
    $db->where('word_clean', $search_string . '%', 'like');
    //$db->orwhere('translate', '%' . $search_string . '%', 'like');
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
try {
    $rows = $db->arraybuilder()->paginate('v_word_list', $page, $select);
} catch (Exception $e) {

}
$total_pages = $db->totalPages;


include BASE_PATH . '/includes/header.php';
?>
<!-- Main container -->
<div id="page-wrapper">

    <div class="row">
        <div class="col-lg-6">
            <h1 class="page-header">Поиск слов</h1>
        </div>
        <div class="col-lg-6">
            <div class="page-action-links text-right">
                <a href="login.php" class="btn btn-success"><i
                            class="glyphicon glyphicon-plus"></i> Вход в личный кабинет</a>
            </div>
        </div>
    </div>

    <?php include BASE_PATH . '/includes/flash_messages.php'; ?>

    <!-- Filters -->
    <div class="well text-center filter-form">

        <form class="form form-inline" action="">

<!--            <select name="srch_dict" class="form-control">
                <option value="0">Выберите книгу</option>
                <?php
/*                foreach ($rows_dict as $row) {
                    echo ' <option value="' .
                        $row['id'] . '" ' .
                        $row['id'] . '>' .
                        $row['id'] . ' -> ' .
                        $row['dict_author'] .
                        '</option>';
                }
                */?>
            </select>-->

            <label for="input_search"></label>
            <input type="text" placeholder="Введите слово" class="form-control" id="input_search"
                   name="search_string"
                   value="<?php echo xss_clean($search_string); ?>">


<!--            <label for="input_order">Сортировка</label>
            <select name="filter_col" class="form-control">
                <?php
/*                foreach ($wordlist->setOrderingValues() as $opt_value => $opt_name):
                    ($order_by === $opt_value) ? $selected = 'selected' : $selected = '';
                    echo ' <option value="' . $opt_value . '" ' . $selected . '>' . $opt_name . '</option>';
                endforeach;
                */?>
            </select>-->


            <!--            <select name="order_by" class="form-control" id="input_order">
                <option value="Asc" <?php
            /*                if ($order_by == 'Asc') {
                                echo 'selected';
                            }
                            */ ?> >По возр.
                </option>
                <option value="Desc" <?php
            /*                if ($order_by == 'Desc') {
                                echo 'selected';
                            }
                            */ ?>>По уб.
                </option>
            </select>-->
            <input type="submit" value="Поиск" class="btn btn-primary">
        </form>
    </div>

    <hr>
    <!-- //Filters -->

    <!-- Table -->
    <table class="table table-striped table-bordered table-condensed">
        <thead>
        <tr>
            <th width="20%">Слово</th>
            <th width="60%">Перевод</th>
            <th width="20%">Наименование словаря</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $row): ?>
            <tr>
                <td><?php echo htmlspecialchars_decode($row['word'], ENT_QUOTES); ?></td>
                <td><?php echo htmlspecialchars_decode($row['translate'], ENT_QUOTES); ?></td>
                <td>
                    <strong>
                        <?php echo htmlspecialchars_decode($row['dict_author'], ENT_QUOTES); ?>
                    </strong>
                    <br>
                    <i>
                        <?php echo htmlspecialchars_decode($row['dict_name'], ENT_QUOTES); ?>
                    </i>
                </td>
            </tr>
            <!-- Delete Confirmation Modal -->

            <!-- //Delete Confirmation Modal -->
        <?php endforeach; ?>
        </tbody>
    </table>
    <!-- //Table -->

    <!-- Pagination -->
    <div class="text-center">
        <?php echo paginationLinks($page, $total_pages, 'wordlist_view.php'); ?>
    </div>
    <!-- //Pagination -->
</div>
<script src="vki/keyboard.js"></script>
<script>
    $(document).ready(function () {
        var myInput = document.getElementById('input_search');
        if (!myInput.VKI_attached)
            VKI_attach(myInput);
    })

    function addVKI() {
        var myInput = document.getElementById('input_search');
        if (!myInput.VKI_attached) VKI_attach(myInput);
    }
</script>
<!-- //Main container -->
<?php include BASE_PATH . '/includes/footer.php'; ?>
