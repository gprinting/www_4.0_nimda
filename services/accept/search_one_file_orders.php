<?php
/**
 * Created by PhpStorm.
 * User: dpdev01
 * Date: 2018-06-20
 * Time: 18:13
 */

include_once($_SERVER["DOCUMENT_ROOT"] . "/services/accept/db_handler.php");

$token = $_GET["token"];
$order_seq = $_GET["order_seq"];
reset($_GET);

$json = array();

$db_handler = new db_handler();
$conn = $db_handler->connect();

$order_seqs = $db_handler->get_order_seqs_for_one_file($conn, $order_seq);

$db_handler->disconnect($conn);

$json["result"]["code"] = "0000";
$json["result"]["value"] = "succeeded";

foreach ($order_seqs as $value) {
    $json["seqs"][] = $value;
}

// output json
echo json_encode($json);

?>