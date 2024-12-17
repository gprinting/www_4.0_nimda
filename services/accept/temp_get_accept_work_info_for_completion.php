<?php
/**
 * Created by PhpStorm.
 * User: dpdev01
 * Date: 2018-06-26
 * Time: 11:21
 */

include_once($_SERVER["DOCUMENT_ROOT"] . "/services/accept/db_handler.php");

$token = $_GET["token"];
$accept_id = $_GET["accept_id"];
reset($_GET);

$json = array();

$db_handler = new db_handler();
$conn = $db_handler->connect();

$infos = $db_handler->get_accept_work_detail($conn, $accept_id);

$json["result"]["code"] = "0000";
$json["result"]["value"] = "succeeded";
$json["order"]["id"] = $infos["order_id"];
$json["accept"]["id"] = $infos["accept_id"];
$json["accept"]["type"] = $infos["accept_type"];
$json["accept"]["worker"]["id"] = $infos["worker_id"];
$json["accept"]["started_date"] = $infos["started_date"];
$json["accept"]["finished_date"] = $infos["finished_date"];

$db_handler->disconnect($conn);

// output json
echo json_encode($json);

?>