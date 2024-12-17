<?php
/**
 * Created by PhpStorm.
 * User: dpdev01
 * Date: 2018-06-20
 * Time: 14:51
 */

include_once($_SERVER["DOCUMENT_ROOT"] . "/services/accept/db_handler.php");
include_once($_SERVER["DOCUMENT_ROOT"] . "/services/accept/transport.php");

$token = $_POST["token"];
$accept_id = $_POST["accept_id"];
$result = $_POST["result"];
reset($_POST);

$json = array();

$db_handler = new db_handler();
$conn = $db_handler->connect();

$db_handler->update_accept_work_with_accept_result($conn, $accept_id, $result, "");
$db_handler->add_accept_event($conn, $accept_id, -1, "12");

//    $url = "http://devadmin.goodprinting.co.kr/services/accept/request_accept.php?token=1234&order_id='$order_id'";
//    transport::get_from_url($url, "POST");

$json["result"]["code"] = "0000";
$json["result"]["value"] = "succeeded";

$db_handler->disconnect($conn);

// output json
echo json_encode($json);

?>