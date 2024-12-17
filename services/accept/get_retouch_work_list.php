<?php
/**
 * Created by PhpStorm.
 * User: dpdev01
 * Date: 2018-06-18
 * Time: 14:29
 */

include_once($_SERVER["DOCUMENT_ROOT"] . "/services/accept/db_handler.php");

$token = $_GET["token"];
$accept_id = $_GET["accept_id"];
reset($_GET);

$json = array();

$db_handler = new db_handler();
$conn = $db_handler->connect();

$works = $db_handler->get_accept_works_for_retouch($conn);

if (count($works) !== 0) {
    $json["result"]["code"] = "0000";
    $json["result"]["value"] = "succeeded";

    foreach ($works as $value) {
        $work = array();
        $work["accept"]["id"] = $value["accept_id"];
        $work["accept"]["type"] = $value["accept_type"];
        $work["order"]["id"] = $value["order_id"];
        $work["order"]["detail"] = $value["order_detail"];
        $work["category"]["id"] = $value["cate_code"];
        $work["customer"]["id"] = $value["customer_id"];
        $work["customer"]["name"] = $value["customer_name"];
        $work["bleed_size"]["width"] = $value["bleed_width"];
        $work["bleed_size"]["height"] = $value["bleed_height"];
        $work["side_count"] = ($value["side_dvs"] === "양면" ? 2 : 1);
        $work["item_count"] = $value["item_count"];
        $work["file_path"] = $value["file_path"] . $value["file_name"];
        $json["works"][] = $work;
    }
} else {
    $json["result"]["code"] = "0001";
    $json["result"]["value"] = "no data";
}

$db_handler->disconnect($conn);

// output json
echo json_encode($json);

?>