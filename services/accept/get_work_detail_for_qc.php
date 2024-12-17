<?php
/**
 * Created by PhpStorm.
 * User: dpdev01
 * Date: 2018-06-13
 * Time: 15:06
 */

include_once($_SERVER["DOCUMENT_ROOT"] . "/services/accept/db_handler.php");

$token = $_GET["token"];
$accept_id = $_GET["accept_id"];
reset($_GET);

$json = array();

$db_handler = new db_handler();
$conn = $db_handler->connect();

$infos = $db_handler->get_order_info_from_accept_id_for_qc($conn, $accept_id);
if (count($infos) !== 0) {
    $json["result"]["code"] = "0000";
    $json["result"]["value"] = "succeeded";
    $json["order"]["id"] = $infos["order_id"];
    $json["order"]["title"] = $infos["order_title"];
    $json["order"]["memo"] = $infos["cust_memo"];
    $json["order"]["file_path"] = $infos["file_path"] . $infos["file_name"];
} else {
    $json["result"]["code"] = "0001";
    $json["result"]["value"] = "no data";
}

$db_handler->disconnect($conn);

// output json
echo json_encode($json);

?>