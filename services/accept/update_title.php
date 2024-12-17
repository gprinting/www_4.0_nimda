<?php
/**
 * Created by PhpStorm.
 * User: dpdev01
 * Date: 2018-07-24
 * Time: 13:40
 */

include_once($_SERVER["DOCUMENT_ROOT"] . "/services/accept/db_handler.php");
include_once($_SERVER["DOCUMENT_ROOT"] . "/services/accept/string_util.php");

$token = $_POST["token"];
$accept_id = $_POST["accept_id"];
$accept_index = $_POST["accept_index"];
$title = $_POST["title"];
reset($_POST);

$json = array();

$db_handler = new db_handler();
$conn = $db_handler->connect();

if (string_util::IsNullOrEmptyString($accept_index) === true) {
	$db_handler->update_accept_work_with_title($conn, $accept_id, $title);
} else {
	$db_handler->update_accept_item_with_title($conn, $accept_id, $accept_index, $title);
}

$json["result"]["code"] = "0000";
$json["result"]["value"] = "succeeded";

$db_handler->disconnect($conn);

// output json
echo json_encode($json);

?>