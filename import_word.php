<?php
session_start();
require_once 'config/config.php';
require_once BASE_PATH . '/includes/auth_validate.php';

require_once BASE_PATH . '/lib/DictList/DictList.php';
require_once BASE_PATH . '/lib/WordList/WordList.php';

$dictlist = new DictList();
// Get Input data from query string
$search_dict = filter_input(INPUT_GET, 'search_dict');
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
// |________________________________|
// |Words                           |
// |________________________________|
$wordlist = new WordList();
// Get Input data from query string
$search_string = filter_input(INPUT_GET, 'search_string');
$filter_col = filter_input(INPUT_GET, 'filter_col');
$order_by = filter_input(INPUT_GET, 'order_by');
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
$select_words = array('id', 'word', 'translate', 'dict_ref', 'dict_name', 'created_at', 'updated_at');
//Start building query according to input parameters.
// If search string
if ($search_dict) {
    $db->where(whereProp: 'dict_ref', whereValue: $search_dict, operator: '=');
}
//If order by option selected
if ($order_by) {
    $db->orderBy($filter_col, $order_by);
}
// Set pagination limit
$db->pageLimit = $pagelimit;
// Get result of the query.
$rows = $db->arraybuilder()->paginate('v_word_list', $page, $select_words);
$total_pages = $db->totalPages;


if (isset($_POST["Import"])) {
    $search_dict = filter_input(INPUT_GET, 'search_dict');
    //echo 'Дошли' . $search_dict;
    $filename = $_FILES["file"]["tmp_name"];
    if ($_FILES["file"]["size"] > 0) {
        $file = fopen($filename, "r");
        while (($getData = fgetcsv($file, null, ";")) !== FALSE) {

            //Insert timestamp
            $data_to_store['created_at'] = date('Y-m-d H:i:s');
            $data_to_store['word'] = $getData[0];
            $data_to_store['translate'] = $getData[1];
            $data_to_store['dict_ref'] = $getData[2];
            $db = getDbInstance();

            $last_id = $db->insert('word_list', $data_to_store);
            if (!isset($last_id)) {
                echo "<script type=\"text/javascript\">
              alert(\"Invalid File:Please Upload CSV File.\");
              window.location = \"import_word.php\"
              </script>";
            } else {
                echo "<script type=\"text/javascript\">
            alert(\"CSV Успешно импортирован.\");
            window.location = \"import_word.php\"
          </script>";
            }
        }

        fclose($file);
    }
}

include BASE_PATH . '/includes/header.php';
?>
<!-- Main container -->
<div id="page-wrapper">
    <div class="row">
        <div class="col-lg-6">
            <h1 class="page-header">Импорт</h1>
        </div>
    </div>
    <?php include BASE_PATH . '/includes/flash_messages.php'; ?>

    <!-- Filters -->
    <div class="well text-center filter-form">
        <form class="form form-inline" action="" name="upload_excel" method="post"
              enctype="multipart/form-data">
            <fieldset>
                <div class="form-group">
                    <select name="search_dict" class="form-control">
                        <option value="0">Выберите книгу</option>
                        <?php
                        foreach ($rows_dict as $row) {
                            echo ' <option value="' .
                                $row['id'] . '" ' .
                                $row['id'] . '>' .
                                $row['id'] . ' -> ' .
                                $row['dict_author'] .
                                '</option>';
                        }
                        ?>
                    </select>
                </div>
                <!-- File Button -->
                <div class="form-group">
                    <label class="col-md-4 control-label" for="filebutton">Выбрать файл</label>
                    <div class="col-md-4">
                        <input type="file" name="file" id="file" class="input-large">
                    </div>
                </div>
                <!-- Button -->
                <div class="form-group">
                    <label class="col-md-4 control-label" for="singlebutton"></label>
                    <div class="col-md-4">
                        <button type="submit" id="submit" action="" name="Import"
                                class="btn btn-primary button-loading"
                                data-loading-text="Loading...">Импорт
                        </button>
                    </div>
                </div>
            </fieldset>
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
        </tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $row): ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo xss_clean($row['word']); ?></td>
                <td><?php echo xss_clean($row['translate']); ?></td>
                <td><?php echo xss_clean($row['dict_ref']); ?></td>
                <td><?php echo xss_clean($row['dict_name']); ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <!-- //Table -->

    <!-- Pagination -->
    <div class="text-center">
        <?php echo paginationLinks($page, $total_pages, 'import_word.php'); ?>
    </div>
    <!-- //Pagination -->
</div>
<!-- //Main container -->
<?php include BASE_PATH . '/includes/footer.php'; ?>
