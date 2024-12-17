<?php
/**
 * Created by PhpStorm.
 * User: dpdev01
 * Date: 2018-07-04
 * Time: 11:59
 */

include_once($_SERVER["DOCUMENT_ROOT"] . "/services/accept/db_handler.php");
include_once($_SERVER["DOCUMENT_ROOT"] . "/services/accept/string_util.php");
include_once($_SERVER["DOCUMENT_ROOT"] . "/services/accept/accept_type.php");

$token = $_GET["token"];
$platform = $_GET["platform"];
$farms = $_GET["farms"];
reset($_GET);

$json = array();

$db_handler = new db_handler();
$conn = $db_handler->connect();

$accept_types = array();
if (strtolower($platform) === "ibm") {
	if (strpos($farms, "normal") !== false) {
		$accept_types[] = accept_type::manu_normal_ibm;
		$accept_types[] = accept_type::manu_normal_retry;
	}
	if (strpos($farms, "real") !== false) {
		$accept_types[] = accept_type::manu_real_ibm;
		$accept_types[] = accept_type::manu_real_retry;
	}
} else if (strtolower($platform) === "mac") {
	if (strpos($farms, "normal") !== false) {
		$accept_types[] = accept_type::manu_normal_mac;
	}
	if (strpos($farms, "real") !== false) {
		$accept_types[] = accept_type::manu_real_mac;
	}
}

$works = $db_handler->get_accept_works_for_manual($conn, $token, $accept_types, 20);

$one_file_orders = array();
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
		foreach ($order_id_sets as $value) {
			$duplicated = false;
			foreach ($one_file_orders as $one_file_order) {
				if ($one_file_order["order_id"] === $value["order_id"]) {
					$duplicated = true;
					break;
				}
			}

			if ($duplicated === false) {
				$order_ids[] = $value["order_id"];
				$one_file_orders[] = $value;
			}
		}
		$temp_works = $db_handler->get_ready_accept_works($conn, $order_ids, $token, $accept_types);
		foreach ($temp_works as $temp_work) {
			$duplicated = false;
			foreach ($manu_works as $manu_work) {
				if ($manu_work["order"]["id"] === $temp_work["order_id"]) {
					$duplicated = true;
					break;
				}
			}

			if ($duplicated === false) {
				$manu_work = array();
				$manu_work["accept"]["id"] = $temp_work["accept_id"];
				$manu_work["accept"]["type"] = $temp_work["accept_type"];
				$manu_work["accept"]["worker"]["id"] = $temp_work["worker_id"];
				$manu_work["order"]["id"] = $temp_work["order_id"];
				$manu_work["order"]["type"] = "one-file";
				$manu_works[] = $manu_work;
			}
		}
	}

	if (count($manu_works) >= 20)
		break;
}

$result_works = array();
foreach ($manu_works as $manu_work) {
	$update_succeeded = true;
	if (string_util::IsNullOrEmptyString($manu_work["accept"]["worker"]["id"])) {
		if ($db_handler->update_accept_work_with_worker_id($conn, $manu_work["accept"]["id"], $token) === false)
			$update_succeeded = false;
	}

	if ($update_succeeded === true) {
		$manu_work["accept"]["worker"]["id"] = $token;
        $manu_work["accept"]["worker"]["name"] = $token;
		$result_works[] = $manu_work;
	}
}

$order_ids = array();
foreach ($result_works as $work) {
	$order_ids[] = $work["order"]["id"];
}

$orders = $db_handler->get_order_infos($conn, $order_ids);

$aaa = array();
foreach ($one_file_orders as $one_file_order) {
	foreach ($orders as &$order) {
		if ($order["order"]["id"] === $one_file_order["order_id"]) {
			$order["group"]["id"] = $one_file_order["group_id"];
			$aaa[] = $one_file_order["group_id"];
			break;
		}
	}
}

$db_handler->disconnect($conn);

$json["result"]["code"] = "0000";
$json["result"]["value"] = "succeeded";
$json["works"] = $result_works;
$json["orders"] = $orders;
$json["aaa"] = $aaa;

// output json
echo json_encode($json);

?>