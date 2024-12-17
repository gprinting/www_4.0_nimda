<?php
/**
 * Created by PhpStorm.
 * User: dpdev01
 * Date: 2018-07-17
 * Time: 11:19
 */

include_once($_SERVER["DOCUMENT_ROOT"] . "/services/accept/db_handler.php");

$token = $_GET["token"];
$id = $_GET["id"];
reset($_GET);

$json = array();

$db_handler = new db_handler();
$conn = $db_handler->connect();

$infos = $db_handler->get_worker_info($conn, $id);
if (count($infos) !== 0) {
	$json["result"]["code"] = "0000";
	$json["result"]["value"] = "succeeded";
	$json["worker"]["id"] = $infos["worker_id"];
	$json["worker"]["name"] = $infos["worker_name"];
	$json["worker"]["dept"] = $infos["dept_code"];
} else {
	$json["result"]["code"] = "0001";
	$json["result"]["value"] = "no infos";
}

$db_handler->disconnect($conn);

// output json
echo json_encode($json);

?>