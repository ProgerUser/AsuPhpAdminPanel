<?php
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
require_once 'config/config.php';
require_once BASE_PATH . '/includes/auth_validate.php';

include BASE_PATH . '/includes/header.php';
?>
<div id="page-wrapper">
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header">Импорт / Экспорт — Слова и Авторы</h1>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">Экспорт CSV</div>
                <div class="panel-body">
                    <p>Выгрузка всех слов с авторами в CSV (UTF-8, разделитель — ;). Формат: <code>word;author</code></p>
                    <a href="export_words_authors.php" class="btn btn-info"><i class="glyphicon glyphicon-export"></i> Экспортировать</a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">Импорт CSV</div>
                <div class="panel-body">
                    <p>Загрузите CSV в формате: <code>word;author</code> (UTF-8, без заголовка). Пустые строки игнорируются.</p>
                    <form id="taImportForm" action="import_words_authors.php" method="post" enctype="multipart/form-data">
                        <div class="form-group">
                            <input type="file" name="file" accept=".csv" required>
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="glyphicon glyphicon-import"></i> Импортировать</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include BASE_PATH . '/includes/footer.php'; ?>





