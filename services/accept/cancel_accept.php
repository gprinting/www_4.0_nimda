<?php
/**
 * Created by PhpStorm.
 * User: dpdev01
 * Date: 2018-07-31
 * Time: 10:25
 */

include_once($_SERVER["DOCUMENT_ROOT"] . "/services/accept/db_handler.php");

$token = $_POST["token"];
$accept_id = $_POST["accept_id"];
reset($_POST);

$json = array();

$db_handler = new db_handler();
$conn = $db_handler->connect();

$db_handler->add_accept_event($conn, $accept_id, -1, "15");
$db_handler->update_accept_items_with_status($conn, $accept_id, "40");

$json["result"]["code"] = "0000";
$json["result"]["value"] = "succeeded";

$db_handler->disconnect($conn);

// output json
echo json_encode($json);

?>