<?php
/**
 * Created by PhpStorm.
 * User: dpdev01
 * Date: 2018-06-20
 * Time: 18:55
 */

include_once($_SERVER["DOCUMENT_ROOT"] . "/services/accept/db_handler.php");

$token = $_POST["token"];
$order_seq = $_POST["order_seq"];
$group_num = $_POST["group_num"];
reset($_POST);

$json = array();

$db_handler = new db_handler();
$conn = $db_handler->connect();

$order_num = $db_handler->get_order_id_from_order_seqno($conn, $order_seq);
$db_handler->test_add_order_to_group($conn, $order_seq, $group_num, $order_num);

$json["result"]["code"] = "0000";
$json["result"]["value"] = "succeeded";

$db_handler->disconnect($conn);

// output json
echo json_encode($json);

?>