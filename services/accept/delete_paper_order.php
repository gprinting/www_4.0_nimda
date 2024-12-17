<?php
/**
 * Created by PhpStorm.
 * User: dpdev01
 * Date: 2018-07-17
 * Time: 14:27
 */

include_once($_SERVER["DOCUMENT_ROOT"] . "/services/accept/db_handler.php");

$token = $_POST["token"];
$order_id = $_POST["order_id"];
reset($_POST);

$json = array();

$db_handler = new db_handler();
$conn = $db_handler->connect();

if ($db_handler->update_paper_order_state($conn, $order_id) === true) {
	$json["result"]["code"] = "0000";
	$json["result"]["value"] = "succeeded";
} else {
	$json["result"]["code"] = "0001";
	$json["result"]["value"] = "failed";
}

$db_handler->disconnect($conn);

// output json
echo json_encode($json);

?>