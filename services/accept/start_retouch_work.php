<?php
/**
 * Created by PhpStorm.
 * User: dpdev01
 * Date: 2018-06-21
 * Time: 14:08
 */

include_once($_SERVER["DOCUMENT_ROOT"] . "/services/accept/db_handler.php");
include_once($_SERVER["DOCUMENT_ROOT"] . "/services/accept/transport.php");

$token = $_POST["token"];
$accept_id = $_POST["accept_id"];
reset($_POST);

$json = array();

$db_handler = new db_handler();
$conn = $db_handler->connect();

$db_handler->update_accept_work_with_worker_id($conn, $accept_id, $token);
$db_handler->add_accept_event($conn, $accept_id, -1, "11");

$json["result"]["code"] = "0000";
$json["result"]["value"] = "succeeded";

$db_handler->disconnect($conn);

// output json
echo json_encode($json);

?>