<?php
/**
 * Created by PhpStorm.
 * User: dpdev01
 * Date: 2018-07-17
 * Time: 16:33
 */

include_once($_SERVER["DOCUMENT_ROOT"] . "/services/accept/db_handler.php");

$token = $_GET["token"];
$type = $_GET["type"];
$created_date_range = $_GET["created_date_range"];
reset($_GET);

$json = array();

$db_handler = new db_handler();
$conn = $db_handler->connect();

$dates = explode('_', $created_date_range);
$plates = $db_handler->get_plates($conn, $type, $dates[0], $dates[1]);
if (count($orders) > 0) {
	$json["result"]["code"] = "0000";
	$json["result"]["value"] = "succeeded";

	foreach ($plates as $value) {
		$plate = array();
		$plate["id"] = $value["plate_id"];
		$plate["item_count"] = $value["item_count"];
		$plate["status_code"] = $value["status_code"];
		$plate["created_date"] = $value["created_date"];
		$json["plates"] = $plate;
	}
} else {
	$json["result"]["code"] = "0001";
	$json["result"]["value"] = "no plates";
}

$db_handler->disconnect($conn);

// output json
echo json_encode($json);

?>