<?php
/**
 * Created by PhpStorm.
 * User: dpdev01
 * Date: 2018-07-17
 * Time: 14:30
 */

include_once($_SERVER["DOCUMENT_ROOT"] . "/services/accept/db_handler.php");

$token = $_POST["token"];
$order_id = $_POST["order_id"];
$mask = $_POST["mask"];
$value = $_POST["value"];
reset($_POST);

$json = array();

$db_handler = new db_handler();
$conn = $db_handler->connect();

$result = false;
switch ($mask) {
	case "PAPER_MILL":
		$result = $db_handler->update_paper_order_with_paper_mill($conn, $order_id, $value);
		break;
	case "PAPER_INFO":
		$result = $db_handler->update_paper_order_with_paper_info($conn, $order_id, $value);
		break;
	case "PAPER_SIZE_1":
		$result = $db_handler->update_paper_order_with_paper_size_1($conn, $order_id, $value);
		break;
	case "PAPER_SIZE_2":
		$result = $db_handler->update_paper_order_with_paper_size_2($conn, $order_id, $value);
		break;
	case "PAPER_GRAIN":
		$result = $db_handler->update_paper_order_with_paper_grain($conn, $order_id, $value);
		break;
	case "QUANTITY":
		$result = $db_handler->update_paper_order_with_quantity($conn, $order_id, $value);
		break;
	case "PRINT_HOUSE":
		$result = $db_handler->update_paper_order_with_print_house($conn, $order_id, $value);
		break;
	case "SEQUENCE":
		$result = $db_handler->update_paper_order_with_sequence($conn, $order_id, $value);
		break;
	case "MEMO":
		$result = $db_handler->update_paper_order_with_memo($conn, $order_id, $value);
		break;
}

if ($result === true) {
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