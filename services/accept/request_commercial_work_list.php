<?php
/**
 * Created by PhpStorm.
 * User: dpdev01
 * Date: 2018-07-04
 * Time: 10:45
 */

include_once($_SERVER["DOCUMENT_ROOT"] . "/services/accept/db_handler.php");
include_once($_SERVER["DOCUMENT_ROOT"] . "/services/accept/string_util.php");
include_once($_SERVER["DOCUMENT_ROOT"] . "/services/accept/accept_type.php");

$token = $_GET["token"];
$platform = $_GET["platform"];
reset($_GET);

$json = array();

$db_handler = new db_handler();
$conn = $db_handler->connect();

$accept_types = array();
if (strtolower($platform) === "ibm") {
	$accept_types[] = accept_type::manu_commercial_ibm;
	$accept_types[] = accept_type::manu_commercial_retry;
} else if (strtolower($platform) === "mac") {
	$accept_types[] = accept_type::manu_commercial_mac;
}

$works = $db_handler->get_accept_works_for_manual($conn, $token, $accept_types, -1);

$manu_works = array();
foreach ($works as $work) {
	$count_in_group = $db_handler->get_order_count_in_one_file_group($conn, $work["order_id"]);
	if ($count_in_group <= 1) {
		$manu_work = array();
		$manu_work["accept"]["id"] = $work["accept_id"];
		$manu_work["accept"]["type"] = $work["accept_type"];
		$manu_work["accept"]["worker"]["id"] = $work["worker_id"];
		$manu_work["order"]["id"] = $work["order_id"];
		$manu_works[] = $manu_work;
	} else {        // one-file
		$order_id_sets = $db_handler->get_order_ids_in_one_file_group($conn, $work["order_id"]);
		$order_ids = array();
		foreach ($order_ids as $value) {
			$order_ids[] = $value["order_id"];
		}
		$temp_works = $db_handler->get_ready_accept_works($conn, $order_ids, $token, $accept_types);
		foreach ($temp_works as $temp_work) {
			$manu_work = array();
			$manu_work["accept"]["id"] = $temp_work["accept_id"];
			$manu_work["accept"]["type"] = $temp_work["accept_type"];
			$manu_work["accept"]["worker"]["id"] = $temp_work["worker_id"];
			$manu_work["order"]["id"] = $temp_work["order_id"];
			$manu_works[] = $manu_work;
		}
	}
}

$order_ids = array();
foreach ($manu_works as $work) {
	$order_ids[] = $work["order"]["id"];
}

$orders = $db_handler->get_order_infos($conn, $order_ids);

$db_handler->disconnect($conn);

$json["result"]["code"] = "0000";
$json["result"]["value"] = "succeeded";
$json["works"] = $manu_works;
$json["orders"] = $orders;

// output json
echo json_encode($json);

?>