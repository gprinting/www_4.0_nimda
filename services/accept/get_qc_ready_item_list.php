<?php
/**
 * Created by PhpStorm.
 * User: dpdev01
 * Date: 2018-06-13
 * Time: 14:18
 */

include_once($_SERVER["DOCUMENT_ROOT"] . "/services/accept/db_handler.php");

$token = $_GET["token"];
reset($_GET);

$json = array();

$db_handler = new db_handler();
$conn = $db_handler->connect();

$items = $db_handler->get_accept_items_with_qc_ready($conn);

$json["result"]["code"] = "0000";
$json["result"]["value"] = "succeeded";

foreach ($items as $value) {
    if ($value["accept_typ"] === "11" || $value["accept_typ"] === "12") {
        $item = array();
        $item["accept"]["id"] = $value["accept_id"];
        $item["accept"]["index"] = $value["accept_index"];
        $item["category"]["id"] = $value["cate_sortcode"];
        $item["customer"]["id"] = $value["customer_id"];
        $item["customer"]["name"] = $value["customer_name"];
        $item["side_count"] = ($value["side_dvs"] === "양면" ? 2 : 1);
        $item["item_count"] = $value["item_count"];
        $json["items"][] = $item;
    }
}

$db_handler->disconnect($conn);

// output json
echo json_encode($json);

?>