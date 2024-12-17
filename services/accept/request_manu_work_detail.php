<?php
/**
 * Created by PhpStorm.
 * User: dpdev01
 * Date: 2018-06-28
 * Time: 16:29
 */

include_once($_SERVER["DOCUMENT_ROOT"] . "/services/accept/db_handler.php");
include_once($_SERVER["DOCUMENT_ROOT"] . "/services/accept/string_util.php");
include_once($_SERVER["DOCUMENT_ROOT"] . "/services/accept/accept_type.php");

$token = $_GET["token"];
$accept_id = $_GET["accept_id"];
reset($_GET);

$json = array();

$db_handler = new db_handler();
$conn = $db_handler->connect();

$accept_info = $db_handler->get_accept_work($conn, $accept_id);
$order_info = $db_handler->get_order_detail($conn, $accept_info["order_id"]);

$db_handler->disconnect($conn);

$json["result"]["code"] = "0000";
$json["result"]["value"] = "succeeded";
$json["work"] = $order_info;
$json["work"]["accept"]["id"] = $accept_id;
$json["work"]["accept"]["type"] = $accept_info["accept_type"];
$json["work"]["accept"]["name"] = $accept_info["accept_title"];
$json["work"]["accept"]["memo"] = $accept_info["accept_memo"];
$json["work"]["accept"]["worker"]["id"] = $accept_info["worker_id"];
$json["work"]["order"]["id"] = $accept_info["order_id"];

$errors = explode('|', $accept_info["accept_report"]);
foreach($errors as $error) {
	$json["work"]["auto_result"][] = $error;
}

// output json
echo json_encode($json);

?>