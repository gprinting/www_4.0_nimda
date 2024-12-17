<?php
/**
 * Created by PhpStorm.
 * User: dpdev01
 * Date: 2018-07-02
 * Time: 15:14
 */

include_once($_SERVER["DOCUMENT_ROOT"] . "/services/accept/db_handler.php");
include_once($_SERVER["DOCUMENT_ROOT"] . "/services/accept/string_util.php");

$token = $_POST["token"];
$accept_id = $_POST["accept_id"];
$accept_event = $_POST["accept_event"];
reset($_POST);

$json = array();

$db_handler = new db_handler();
$conn = $db_handler->connect();

$status = "";
switch ($accept_event) {
	case "11":
		$status = "11";
		break;
	case "12":
		$status = "40";
		break;
	case "13":
		$status = "12";
		break;
	case "14":
		$status = "11";
		break;
}

if ($accept_event === "11") {
	$worker_id = $db_handler->get_worker_id_for_accept($conn, $accept_id);
	if (string_util::IsNullOrEmptyString($worker_id) === true) {
		$db_handler->update_accept_work_with_worker_id($conn, $accept_id, $token);

		$db_handler->add_accept_event($conn, $accept_id, -1, $accept_event);
		if ($db_handler->update_accept_items_with_status($conn, $accept_id, $status) === false) {
			$order_info = $db_handler->get_order_info_from_accept_id($conn, $accept_id);
			$db_handler->create_accept_items($conn, $accept_id, $order_info["item_count"], $status);
		}

		$json["result"]["code"] = "0000";
		$json["result"]["value"] = "succeeded";
		$json["accept"]["worker"]["id"] = $token;
		$json["accept"]["worker"]["name"] = "";
	} else {
		$db_handler->add_accept_event($conn, $accept_id, -1, $accept_event);
		if ($db_handler->update_accept_items_with_status($conn, $accept_id, $status) === false) {
			$order_info = $db_handler->get_order_info_from_accept_id($conn, $accept_id);
			$db_handler->create_accept_items($conn, $accept_id, $order_info["item_count"], $status);
		}

		$json["result"]["code"] = "0002";
		$json["result"]["value"] = "worker exist";
		$json["accept"]["worker"]["id"] = $worker_id;
		$json["accept"]["worker"]["name"] = "";
	}
} else {
	$db_handler->add_accept_event($conn, $accept_id, -1, $accept_event);
	$db_handler->update_accept_items_with_status($conn, $accept_id, $status);

	$json["result"]["code"] = "0000";
	$json["result"]["value"] = "succeeded";
}

$db_handler->disconnect($conn);

// output json
echo json_encode($json);

?>