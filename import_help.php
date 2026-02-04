<?php
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
require_once 'config/config.php';
require_once BASE_PATH . '/includes/auth_validate.php';
include BASE_PATH . '/includes/header.php';
?>
<div id="page-wrapper">
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header">Как подготовить файл для импорта</h1>
        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading">Формат CSV файла</div>
        <div class="panel-body">
            <p>Импорт поддерживает файлы CSV в кодировке UTF-8, разделитель — точка с запятой (;).</p>
            <p>Порядок колонок:</p>
            <ol>
                <li><b>Слово</b> — обязательное поле</li>
                <li><b>Перевод</b> — текст, допускается HTML (будет сохранён как есть)</li>
            </ol>
            <p>Словарь для импорта выбирается на странице импорта выпадающим списком.</p>
            <p>Пример содержимого файла:</p>
            <pre>абца;пример перевода
абцахь;другой перевод</pre>
            <p>Рекомендации:</p>
            <ul>
                <li>Сохраните файл в UTF-8 без BOM.</li>
                <li>Не добавляйте заголовок колонок.</li>
                <li>Проверяйте, что каждая строка содержит минимум две колонки.</li>
            </ul>
        </div>
    </div>

    <a href="import_word.php" class="btn btn-default">Вернуться к импорту</a>
</div>
<?php include BASE_PATH . '/includes/footer.php'; ?>





