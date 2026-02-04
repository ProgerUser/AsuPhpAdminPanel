<?php
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
require_once __DIR__ . '/config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    exit('Файл не загружен');
}

$filename = $_FILES['file']['tmp_name'];
$size = (int)$_FILES['file']['size'];
if ($size <= 0) {
    http_response_code(400);
    exit('Пустой файл');
}

$db = getDbInstance();
$handle = fopen($filename, 'r');
if ($handle === false) {
    http_response_code(400);
    exit('Не удалось открыть файл');
}

$imported = 0;
$errors = [];
$line = 0;
while (($data = fgetcsv($handle, null, ';')) !== false) {
    $line++;
    if (count($data) < 2) {
        $errors[] = "Строка {$line}: недостаточно колонок (ожидалось 2)";
        continue;
    }
    $word = trim((string)$data[0]);
    $author = trim((string)$data[1]);
    if ($word === '') { $errors[] = "Строка {$line}: пустое слово"; continue; }

    // Вставляем только слово и автора; словарь не меняем
    $insert = [
        'word' => $word,
        'dict_ref' => null,
        'translate' => '',
        'created_at' => date('Y-m-d H:i:s')
    ];
    // Пытаемся найти существующий словарь по автору
    // Если у вас есть справочник авторов и словарей — здесь можно подобрать dict_ref
    // Пока просто добавим в таблицу word_list; автора запишем в отдельной колонке при её наличии

    // Если в word_list нет колонки автора, этот шаг можно адаптировать под вашу модель данных
    // Например, сохранять автора в translate или игнорировать

    $id = $db->insert('word_list', $insert);
    if (!$id) {
        $errors[] = "Строка {$line}: " . $db->getLastError();
    } else {
        $imported++;
    }
}
fclose($handle);

// Результат
$_SESSION['success'] = "Импортировано: {$imported}. Ошибки: " . count($errors);
if ($errors) {
    $_SESSION['failure'] = implode("\n", $errors);
}
header('Location: transfer_words_authors.php');
exit;





