<?php
/**
 * Created by PhpStorm.
 * User: dpdev01
 * Date: 2018-06-15
 * Time: 10:45
 */

include_once($_SERVER["DOCUMENT_ROOT"] . "/services/accept/db_handler.php");

$token = $_GET["token"];
$order_seqno = $_GET["order_seqno"];
reset($_GET);

$json = array();

$db_handler = new db_handler();
$conn = $db_handler->connect();

$order_id = $db_handler->get_order_id_from_order_seqno($conn, $order_seqno);
if ($order_id !== "") {
    $json["result"]["code"] = "0000";
    $json["result"]["value"] = "succeeded";
    $json["order"]["id"] = $order_id;
} else {
    $json["result"]["code"] = "0001";
    $json["result"]["value"] = "no order";
}

$db_handler->disconnect($conn);

// output json
echo json_encode($json);

?>