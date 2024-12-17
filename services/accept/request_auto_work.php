<?php
/**
 * Created by PhpStorm.
 * User: dpdev01
 * Date: 2018-06-08
 * Time: 18:01
 */

include_once($_SERVER["DOCUMENT_ROOT"] . "/services/accept/db_handler.php");

$token = $_GET["token"];
reset($_GET);

$json = array();

$db_handler = new db_handler();
$conn = $db_handler->connect();

$accept_info = $db_handler->get_ready_accept_info_for_auto($conn);
if (count($accept_info) !== 0) {
    if ($db_handler->update_accept_work_with_worker_id($conn, $accept_info["accept_id"], "auto01") === true) {

        $db_handler->add_accept_event($conn, $accept_info["accept_id"], -1, "11");

        $order_infos = $db_handler->get_order_info_from_accept_id($conn, $accept_info["accept_id"]);

        $json["result"]["code"] = "0000";
        $json["result"]["value"] = "succeeded";
        $json["work"]["accept"]["id"] = $accept_info["accept_id"];
        $json["work"]["accept"]["type"] = $accept_info["accept_type"];
        $json["work"]["order"]["id"] = $order_infos["order_id"];
        $json["work"]["order"]["name"] = $order_infos["order_title"];
        $json["work"]["category"]["id"] = $order_infos["cate_code"];
        $json["work"]["category"]["name"] = $order_infos["cate_name"];
        $json["work"]["item_count"] = $order_infos["item_count"];
        $json["work"]["bleed_size"]["width"] = $order_infos["bleed_width"];
        $json["work"]["bleed_size"]["height"] = $order_infos["bleed_height"];
        $json["work"]["trim_size"]["width"] = $order_infos["trim_width"];
        $json["work"]["trim_size"]["height"] = $order_infos["trim_height"];
        $json["work"]["regularity"] = ($order_infos["stan_name"] === "비규격" ? 0 : 1);
        $json["work"]["side_count"] = ($order_infos["side_dvs"] === "양면" ? 2 : 1);
        $json["work"]["file_path"] = $order_infos["file_path"] . $order_infos["file_name"];

        // add items
//        $db_handler->create_accept_items($conn, $accept_info["accept_id"], (int) $order_infos["item_count"], "11");
        $db_handler->update_accept_items_with_status($conn, $accept_info["accept_id"], "11");
    } else {
        $json["result"]["code"] = "0002";
        $json["result"]["value"] = "failed to update worker_id";
    }
} else {
    $json["result"]["code"] = "0001";
    $json["result"]["value"] = "no ready-work";
}

$db_handler->disconnect($conn);

// output json
echo json_encode($json);

?>