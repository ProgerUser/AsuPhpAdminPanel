<?php 
session_start();
require_once 'includes/auth_validate.php';
require_once './config/config.php';
$del_id = filter_input(INPUT_POST, 'del_id');
if ($del_id && $_SERVER['REQUEST_METHOD'] == 'POST') 
{

	if($_SESSION['admin_type']!='super'){
		$_SESSION['failure'] = "У вас нет разрешения на выполнение этого действия";
    	header('location: customers.php');
        exit;

	}
    $customer_id = $del_id;

    $db = getDbInstance();
    $db->where('id', $customer_id);
    $status = $db->delete('customers');
    
    if ($status) 
    {
        $_SESSION['info'] = "Словарь успешно удален!";
        header('location: customers.php');
        exit;
    }
    else
    {
    	$_SESSION['failure'] = "Не удалось удалить словарь";
    	header('location: customers.php');
        exit;

    }
    
}