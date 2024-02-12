<?php

use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

session_start();
require_once 'config/config.php';
require_once BASE_PATH . '/includes/auth_validate.php';

$db = getDbInstance();

$allowedFileType = [
    'application/vnd.ms-excel',
    'text/xls',
    'text/xlsx',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
];

if (in_array($_FILES["file"]["type"], $allowedFileType)) {

    $targetPath = 'uploads/' . $_FILES['file']['name'];
    move_uploaded_file($_FILES['file']['tmp_name'], $targetPath);

    $Reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();

    $spreadSheet = $Reader->load($targetPath);
    $excelSheet = $spreadSheet->getActiveSheet();
    $spreadSheetAry = $excelSheet->toArray();
    $sheetCount = count($spreadSheetAry);

    for ($i = 1; $i <= $sheetCount; $i++) {
        $name = "";
        if (isset($spreadSheetAry[$i][0])) {
            $name = mysqli_real_escape_string($conn, $spreadSheetAry[$i][0]);
        }
        $description = "";
        if (isset($spreadSheetAry[$i][1])) {
            $description = mysqli_real_escape_string($conn, $spreadSheetAry[$i][1]);
        }

        if (!empty($name) || !empty($description)) {
            $query = "insert into tbl_info(name,description) values(?,?)";
            $paramType = "ss";
            $paramArray = array(
                $name,
                $description
            );
            $insertId = $db->insert($query, $paramType, $paramArray);
            // $query = "insert into tbl_info(name,description) values('" . $name . "','" . $description . "')";
            // $result = mysqli_query($conn, $query);

            if (!empty($insertId)) {
                $type = "success";
                $message = "Excel Data Imported into the Database";
            } else {
                $type = "error";
                $message = "Problem in Importing Excel Data";
            }
        }
    }
} else {
    $type = "error";
    $message = "Invalid File Type. Upload Excel File.";
}