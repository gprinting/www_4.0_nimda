<?php
/**
 * Created by PhpStorm.
 * User: dpdev01
 * Date: 2018-07-16
 * Time: 17:14
 */

include_once($_SERVER["DOCUMENT_ROOT"] . "/services/accept/db_handler.php");

$token = $_GET["token"];
$statuses_s = $_GET["statuses"];
reset($_GET);

$statuses = explode('|', $statuses_s);

$json = array();

$db_handler = new db_handler();
$conn = $db_handler->connect();

$items = $db_handler->get_accept_items($conn, $statuses);

$json["result"]["code"] = "0000";
$json["result"]["value"] = "succeeded";

foreach ($items as $value) {
	$item = array();
	$item["id"] = $value["accept_id"] . sprintf("%03d", $value["accept_index"]);
	$item["type"] = $value["accept_typ"];
	$item["date"]["started"] = $value["started_date"];
	$item["status"]["code"] = $value["status_code"];
	$json["items"][] = $item;
}

$db_handler->disconnect($conn);

// output json
echo json_encode($json);

?>