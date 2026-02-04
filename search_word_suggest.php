<?php
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
require_once __DIR__ . '/config/config.php';

header('Content-Type: application/json; charset=utf-8');

$term = trim((string)($_GET['term'] ?? ''));
if ($term === '') {
    echo json_encode([]);
    exit;
}

$db = getDbInstance();
$db->where('word', "%{$term}%", 'LIKE');
$db->orderBy('word', 'ASC');
$db->pageLimit = 10;
$rows = $db->arraybuilder()->paginate('v_word_list', 1, ['word','dict_name','dict_author']);

$suggestions = [];
$seen = [];
foreach ($rows as $row) {
    $w = (string)$row['word'];
    $dn = isset($row['dict_name']) ? (string)$row['dict_name'] : '';
    $da = isset($row['dict_author']) ? (string)$row['dict_author'] : '';
    if (!isset($seen[$w])) {
        $label = $w;
        $suggestions[] = [ 'label' => $label, 'value' => $w, 'dict_name' => $dn, 'dict_author' => $da ];
        $seen[$w] = true;
    }
}

echo json_encode($suggestions);
exit;

