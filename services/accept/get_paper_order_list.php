<?php
/**
 * Created by PhpStorm.
 * User: dpdev01
 * Date: 2018-07-01
 * Time: 18:20
 */

include_once($_SERVER["DOCUMENT_ROOT"] . "/services/accept/db_handler.php");

$token = $_GET["token"];
$ordered_date_range = $_GET["ordered_date_range"];
reset($_GET);

$json = array();

$db_handler = new db_handler();
$conn = $db_handler->connect();

$date = explode('_', $ordered_date_range);
$orders = $db_handler->get_paper_orders($conn, $date[0], $date[1]);
if (count($orders) > 0) {
	$json["result"]["code"] = "0000";
	$json["result"]["value"] = "succeeded";

	foreach ($orders as $value) {
		if ($value["state"] !== "del") {
			$order = array();
			$order["order_id"] = $value["paper_op_seqno"];
			$order["paper_mill"] = ($value["etprs_name"] === null ? "" : $value["etprs_name"]);
			$order["paper_info"] = $value["name"] . " " . $value["color"] . " " . $value["basisweight"];
			$order["paper_size_1"] = $value["op_size"];
			$order["paper_size_2"] = $value["stor_size"];
			$order["paper_grain"] = $value["grain"];
			$order["quantity"] = $value["amt"];
			$order["print_house"] = $value["warehouser"];
			$order["memo"] = $value["memo"];
			$order["sequence"] = $value["op_degree"];
			$json["orders"][] = $order;
		}
	}
} else {
	$json["result"]["code"] = "0001";
	$json["result"]["value"] = "no orders";
}

$db_handler->disconnect($conn);

// output json
echo json_encode($json);

?>