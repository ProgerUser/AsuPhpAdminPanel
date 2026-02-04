<?php
require_once __DIR__ . '/config/config.php';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="words_authors.csv"');

$out = fopen('php://output', 'w');

// Принудительно записываем BOM для Excel (по желанию можно убрать)
fwrite($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

$db = getDbInstance();
$db->orderBy('word', 'ASC');
$cols = ['word', 'dict_author'];
$page = 1;
$pageSize = 1000;

do {
    $db->pageLimit = $pageSize;
    $rows = $db->arraybuilder()->paginate('v_word_list', $page, $cols);
    foreach ($rows as $row) {
        $word = isset($row['word']) ? (string)$row['word'] : '';
        $author = isset($row['dict_author']) ? (string)$row['dict_author'] : '';
        // Разделитель ;
        fputcsv($out, [$word, $author], ';');
    }
    $page++;
} while (!empty($rows));

fclose($out);
exit;





