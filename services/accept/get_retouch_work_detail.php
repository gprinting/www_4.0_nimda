<?php
/**
 * Created by PhpStorm.
 * User: dpdev01
 * Date: 2018-06-19
 * Time: 18:00
 */

include_once($_SERVER["DOCUMENT_ROOT"] . "/services/accept/db_handler.php");

$token = $_GET["token"];
$accept_id = $_GET["accept_id"];
reset($_GET);

$json = array();

$db_handler = new db_handler();
$conn = $db_handler->connect();

$curr_work_infos = $db_handler->get_accept_work($conn, $accept_id);
if ($curr_work_infos["accept_type"] === "31") {
    $prev_work_infos = $db_handler->get_prev_accept_work($conn, $curr_work_infos["order_id"]);

    $json["result"]["code"] = "0000";
    $json["result"]["value"] = "succeeded";
    $json["work"]["prev_accept_report"] = $prev_work_infos["accept_report"];
} else if ($curr_work_infos["accept_type"] === "32") {
}

$json["work"]["accept"]["type"] = $curr_work_infos["accept_type"];

$db_handler->disconnect($conn);

// output json
echo json_encode($json);

?>