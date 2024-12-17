<?php
/**
 * Created by PhpStorm.
 * User: dpdev01
 * Date: 2018-06-11
 * Time: 18:13
 */

include_once($_SERVER["DOCUMENT_ROOT"] . "/services/accept/db_handler.php");

$token = $_POST["token"];
$item_ids_s = $_POST["item_ids"];
reset($_POST);

$item_ids = explode('|', $item_ids_s);

$json = array();

$db_handler = new db_handler();
$conn = $db_handler->connect();

foreach($item_ids as $item_id) {
    $accept_id = substr($item_id, 0, strlen($string) - 3);
    $accept_index = substr($item_id, -3);
    $db_handler->add_accept_event($conn, $accept_id, $accept_index, "33");
    $db_handler->update_accept_item_with_status($conn, $accept_id, $accept_index, "31");
}

$json["result"]["code"] = "0000";
$json["result"]["value"] = "succeeded";

$db_handler->disconnect($conn);

// output json
echo json_encode($json);

?>