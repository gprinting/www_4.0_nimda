<?php
/**
 * Created by PhpStorm.
 * User: dpdev01
 * Date: 2018-06-19
 * Time: 14:36
 */

include_once($_SERVER["DOCUMENT_ROOT"] . "/services/accept/db_handler.php");

$token = $_GET["token"];
$accept_id = $_GET["accept_id"];
reset($_GET);

$json = array();

$db_handler = new db_handler();
$conn = $db_handler->connect();

$order_id = $db_handler->get_order_id_from_accept_id($conn, $accept_id);

$db_handler->disconnect($conn);

$json["result"]["code"] = "0000";
$json["result"]["value"] = "succeeded";
$json["order"]["id"] = $order_id;

// output json
echo json_encode($json);

?>