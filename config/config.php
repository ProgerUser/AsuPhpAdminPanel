<?php
// config.php
error_reporting(E_ALL);
// В проде лучше не показывать ошибки в браузере
ini_set('display_errors', 'Off');
ini_set('log_errors', 'On');
ini_set('error_log', dirname(__FILE__) . '/../logs/php_errors.log');
define('BASE_PATH', dirname(dirname(__FILE__)));
define('APP_FOLDER', 'simpleadmin');
define('CURRENT_PAGE', basename($_SERVER['REQUEST_URI']));

require_once BASE_PATH . '/lib/MysqliDb/MysqliDb.php';
require_once BASE_PATH . '/helpers/helpers.php';

define('DB_HOST', "127.0.0.1");
define('DB_USER', "root");
define('DB_PASSWORD', "Ipman165");
define('DB_NAME', "corephpadmin");

function getDbInstance() {
    return new MysqliDb(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
}

// Убедитесь, что НЕ включаете security.php здесь
// Он будет включаться отдельно в нужных местах
?>