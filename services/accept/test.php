<?php
/**
 * Created by PhpStorm.
 * User: dpdev01
 * Date: 2018-06-08
 * Time: 15:39
 */

include_once($_SERVER["DOCUMENT_ROOT"] . "/services/accept/db_handler.php");

$token = $_POST["token"];
reset($_POST);

$db_handler = new db_handler();
$conn = $db_handler->connect();

$order_infos = $db_handler->test($conn);

$db_handler->disconnect($conn);

// output json
echo json_encode($order_infos);

?>