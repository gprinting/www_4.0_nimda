<?php
/**
 * Created by PhpStorm.
 * User: dpdev01
 * Date: 2018-06-09
 * Time: 16:39
 */

include_once($_SERVER["DOCUMENT_ROOT"] . "/services/accept/db_handler.php");

$token = $_GET["token"];
reset($_GET);

$json = array();

$db_handler = new db_handler();
$conn = $db_handler->connect();

$items = $db_handler->get_accept_items_in_prepress($conn);

$json["result"]["code"] = "0000";
$json["result"]["value"] = "succeeded";

foreach ($items as $value) {
    $item = array();
    $item["accept"]["id"] = $value["accept_id"];
    $item["accept"]["index"] = $value["accept_index"];
    $item["accept"]["type"] = $value["accept_typ"];
    $item["category"]["id"] = $value["cate_sortcode"];
    $item["prepressed_date"] = $value["created"];
    $json["items"][] = $item;
}

$db_handler->disconnect($conn);

// output json
echo json_encode($json);

?>