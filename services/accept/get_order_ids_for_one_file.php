<?php
/**
 * Created by PhpStorm.
 * User: dpdev01
 * Date: 2018-06-21
 * Time: 17:53
 */

include_once($_SERVER["DOCUMENT_ROOT"] . "/services/accept/db_handler.php");

$token = $_GET["token"];
$order_id = $_GET["order_id"];
reset($_GET);

$json = array();

$db_handler = new db_handler();
$conn = $db_handler->connect();

$order_ids = $db_handler->get_order_ids_in_one_file_group($conn, $order_id);

if (count($order_ids) !== 0) {
    $json["result"]["code"] = "0000";
    $json["result"]["value"] = "succeeded";

    $group_id = "";
    foreach ($order_ids as $value) {
        $order = array();
        $order["order"]["id"] = $value["order_id"];
        $json["orders"][] = $order;

        $group_id = $value["group_id"];
    }
    $json["group"]["id"] = $group_id ;
} else {
    $json["result"]["code"] = "0001";
    $json["result"]["value"] = "no data";
}

$db_handler->disconnect($conn);

// output json
echo json_encode($json);

?>