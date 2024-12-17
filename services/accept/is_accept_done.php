<?php
/**
 * Created by PhpStorm.
 * User: dpdev01
 * Date: 2018-06-14
 * Time: 14:06
 */

include_once($_SERVER["DOCUMENT_ROOT"] . "/services/accept/db_handler.php");

$token = $_GET["token"];
$accept_id = $_GET["accept_id"];
reset($_GET);

$json = array();

$db_handler = new db_handler();
$conn = $db_handler->connect();

$accept_result = $db_handler->get_accept_result($conn, $accept_id);
if ($accept_result == "1") {
    $done = true;
    $status_list = $db_handler->get_accept_status_list_for_accept_items($conn, $accept_id);
    foreach($status_list as $status) {
        if ($status !== "40") {
            $done = false;
            break;
        }
    }

    if ($done === true) {
        $json["result"]["code"] = "0000";
        $json["result"]["value"] = "succeeded";
    } else {
        $json["result"]["code"] = "0002";
        $json["result"]["value"] = "accept item is not done";
    }
} else {
    $json["result"]["code"] = "0001";
    $json["result"]["value"] = "accept result is failure";
}

$db_handler->disconnect($conn);

// output json
echo json_encode($json);

?>