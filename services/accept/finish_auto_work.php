<?php
/**
 * Created by PhpStorm.
 * User: dpdev01
 * Date: 2018-06-09
 * Time: 14:20
 */

include_once($_SERVER["DOCUMENT_ROOT"] . "/services/accept/db_handler.php");
include_once($_SERVER["DOCUMENT_ROOT"] . "/services/accept/transport.php");

$token = $_POST["token"];
$accept_id = $_POST["accept_id"];
$result = $_POST["result"];
$report = $_POST["report"];
reset($_POST);

$json = array();

$db_handler = new db_handler();
$conn = $db_handler->connect();

$db_handler->update_accept_work_with_accept_result($conn, $accept_id, $result, $report);

if ($result === "1") {
    $db_handler->add_accept_event($conn, $accept_id, -1, "31");

    // update accept items with status -> 21
    $db_handler->update_accept_items_with_status($conn, $accept_id, "21");

} else {
    $db_handler->add_accept_event($conn, $accept_id, -1, "12");

    // update accept items with status -> 40
    $db_handler->update_accept_items_with_status($conn, $accept_id, "40");

//    $url = "http://devadmin.goodprinting.co.kr/services/accept/request_accept.php?token=1234&order_id='$order_id'";
//    transport::get_from_url($url, "POST");
}

$json["result"]["code"] = "0000";
$json["result"]["value"] = "succeeded";

$db_handler->disconnect($conn);

// output json
echo json_encode($json);

?>