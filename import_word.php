<?php
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
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


// AJAX обработчик импорта
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['ajax']) && $_GET['ajax'] === '1') {
    header('Content-Type: application/json');
    $errors = [];
    try {
        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Файл не загружен');
        }
        $dictRef = filter_input(INPUT_POST, 'search_dict', FILTER_VALIDATE_INT);
        if (!$dictRef) {
            throw new Exception('Не выбран словарь');
        }
        $filename = $_FILES['file']['tmp_name'];
        $size = (int)$_FILES['file']['size'];
        if ($size <= 0) {
            throw new Exception('Пустой файл');
        }

        $handle = fopen($filename, 'r');
        if ($handle === false) {
            throw new Exception('Не удалось открыть файл');
        }

        $rowIndex = 0;
        $imported = 0;
        $db = getDbInstance();
        while (($getData = fgetcsv($handle, null, ";")) !== false) {
            $rowIndex++;
            if (count($getData) < 2) {
                $errors[] = "Строка {$rowIndex}: недостаточно колонок";
                continue;
            }
            $word = trim((string)$getData[0]);
            $translate = (string)$getData[1];
            if ($word === '') {
                $errors[] = "Строка {$rowIndex}: пустое слово";
                continue;
            }
            $data_to_store = [
                'created_at' => date('Y-m-d H:i:s'),
                'word' => $word,
                'translate' => $translate,
                'dict_ref' => $dictRef
            ];
            $last_id = $db->insert('word_list', $data_to_store);
            if (!$last_id) {
                $errors[] = "Строка {$rowIndex}: " . $db->getLastError();
            } else {
                $imported++;
            }
        }
        fclose($handle);
        echo json_encode(['ok' => true, 'imported' => $imported, 'errors' => $errors]);
        exit;
    } catch (Exception $e) {
        echo json_encode(['ok' => false, 'error' => $e->getMessage(), 'errors' => $errors]);
        exit;
    }
}

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
              enctype="multipart/form-data" id="importForm">
            <fieldset>
                <div class="form-group">
                    <select name="search_dict" class="form-control" required>
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
                        <input type="file" name="file" id="file" class="input-large" accept=".csv" required>
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
                <div class="form-group" style="min-width:300px;">
                    <div class="progress" style="height:10px; margin-top:10px;">
                        <div id="uploadProgress" class="progress-bar progress-bar-striped active" role="progressbar" style="width:0%;"></div>
                    </div>
                </div>
                <div id="importResult" class="text-left" style="margin-top:10px;"></div>
            </fieldset>
        </form>
        <div style="margin-top:10px;">
            <a href="import_help.php" class="btn btn-link">Как подготовить файл для импорта?</a>
        </div>
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
<script>
// AJAX импорт с прогресс-баром
(function(){
    var form = document.getElementById('importForm');
    if (!form) return;
    var progress = document.getElementById('uploadProgress');
    var result = document.getElementById('importResult');

    form.addEventListener('submit', function(e){
        e.preventDefault();
        result.innerHTML = '';
        var formData = new FormData(form);

        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'import_word.php?ajax=1', true);

        xhr.upload.onprogress = function (e) {
            if (e.lengthComputable) {
                var percent = Math.round((e.loaded / e.total) * 100);
                progress.style.width = percent + '%';
            }
        };

        xhr.onload = function(){
            try {
                var data = JSON.parse(xhr.responseText || '{}');
                if (data.ok) {
                    var html = '<div class="alert alert-success">Импортировано строк: ' + (data.imported||0) + '</div>';
                    if (data.errors && data.errors.length) {
                        html += '<div class="alert alert-warning"><b>Ошибки:</b><ul>' + data.errors.map(function(er){ return '<li>' + er.replace(/</g,'&lt;').replace(/>/g,'&gt;') + '</li>'; }).join('') + '</ul></div>';
                    }
                    result.innerHTML = html;
                } else {
                    result.innerHTML = '<div class="alert alert-danger">Ошибка: ' + (data.error||'Неизвестная ошибка') + '</div>';
                }
            } catch (e) {
                result.innerHTML = '<div class="alert alert-danger">Ошибка обработки ответа сервера</div>';
            }
            setTimeout(function(){ progress.style.width = '0%'; }, 1000);
        };

        xhr.onerror = function(){
            result.innerHTML = '<div class="alert alert-danger">Сетевая ошибка при загрузке</div>';
            progress.style.width = '0%';
        };

        xhr.send(formData);
    });
})();
</script>
