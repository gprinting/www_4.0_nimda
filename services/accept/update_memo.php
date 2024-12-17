<?php
/**
 * Created by PhpStorm.
 * User: dpdev01
 * Date: 2018-07-24
 * Time: 14:03
 */

include_once($_SERVER["DOCUMENT_ROOT"] . "/services/accept/db_handler.php");
include_once($_SERVER["DOCUMENT_ROOT"] . "/services/accept/string_util.php");

$token = $_POST["token"];
$accept_id = $_POST["accept_id"];
$accept_index = $_POST["accept_index"];
$memo = $_POST["memo"];
reset($_POST);

$json = array();

$db_handler = new db_handler();
$conn = $db_handler->connect();

if (string_util::IsNullOrEmptyString($accept_index) === true) {
	$db_handler->update_accept_work_with_memo($conn, $accept_id, $memo);
} else {
	$db_handler->update_accept_item_with_memo($conn, $accept_id, $accept_index, $memo);
}

$json["result"]["code"] = "0000";
$json["result"]["value"] = "succeeded";

$db_handler->disconnect($conn);

// output json
echo json_encode($json);

?>