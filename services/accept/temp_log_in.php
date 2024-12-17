<?php
/**
 * Created by PhpStorm.
 * User: dpdev01
 * Date: 2018-07-17
 * Time: 10:48
 */

include_once($_SERVER["DOCUMENT_ROOT"] . "/services/accept/db_handler.php");

$id = $_POST["id"];
$pw = $_POST["pw"];
reset($_POST);

$json = array();

$db_handler = new db_handler();
$conn = $db_handler->connect();

$json["result"]["code"] = "0000";
$json["result"]["value"] = "succeeded";
$json["token"] = $id;

$db_handler->disconnect($conn);

// output json
echo json_encode($json);

?>