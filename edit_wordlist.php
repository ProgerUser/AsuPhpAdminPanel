<?php
session_start();
require_once './config/config.php';
require_once 'includes/auth_validate.php';


// Sanitize if you want
$wordlist_id = filter_input(INPUT_GET, 'customer_id', FILTER_VALIDATE_INT);
$operation = filter_input(INPUT_GET, 'operation',FILTER_UNSAFE_RAW/*FILTER_SANITIZE_STRING*/);
($operation == 'edit') ? $edit = true : $edit = false;
 $db = getDbInstance();

//Handle update request. As the form's action attribute is set to the same script, but 'POST' method, 
if ($_SERVER['REQUEST_METHOD'] == 'POST') 
{
    //Get customer id form query string parameter.
    $wordlist_id = filter_input(INPUT_GET, 'customer_id', FILTER_UNSAFE_RAW);

    //Get input data
    $data_to_update = filter_input_array(INPUT_POST);
    
    $data_to_update['updated_at'] = date('Y-m-d H:i:s');
    $db = getDbInstance();
    $db->where('id',$wordlist_id);
    $stat = $db->update('word_list', $data_to_update);

    if($stat)
    {
        $_SESSION['success'] = "Строка успешно обновлена!";
        //Redirect to the listing page,
        header('location: wordlist.php');
        //Important! Don't execute the rest put the exit/die. 
        exit();
    }
}


//If edit variable is set, we are performing the update operation.
if($edit)
{
    $db->where('id', $wordlist_id);
    //Get data to pre-populate the form.
    $wordlist = $db->getOne("word_list");
}
?>


<?php
    include_once 'includes/header.php';
?>
<div id="page-wrapper">
    <div class="row">
        <h2 class="page-header">Обновить список слов</h2>
    </div>
    <!-- Flash messages -->
    <?php
        include('./includes/flash_messages.php')
    ?>

    <form class="" action="" method="post" enctype="multipart/form-data" id="contact_form">
        
        <?php
            //Include the common form for add and edit  
            require_once('./forms/wordlist_form.php');
        ?>
    </form>
</div>




<?php include_once 'includes/footer.php'; ?>