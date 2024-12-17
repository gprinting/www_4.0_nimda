<?php
/**
 * Created by PhpStorm.
 * User: dpdev01
 * Date: 2018-07-01
 * Time: 18:39
 */

include_once($_SERVER["DOCUMENT_ROOT"] . "/services/accept/db_handler.php");

$token = $_POST["token"];
$paper_mill = $_POST["paper_mill"];
$paper_info = $_POST["paper_info"];
$paper_size_1 = $_POST["paper_size_1"];
$paper_size_2 = $_POST["paper_size_2"];
$paper_grain = $_POST["paper_grain"];
$quantity = $_POST["quantity"];
$print_house = $_POST["print_house"];
$sequence = $_POST["sequence"];
$memo = $_POST["memo"];
reset($_POST);

$json = array();

$db_handler = new db_handler();
$conn = $db_handler->connect();

if ($db_handler->add_paper_order($conn, $paper_mill, $paper_info, $paper_size_1, $paper_size_2, $paper_grain, $quantity, $print_house, $sequence, $memo) === true) {
	$json["result"]["code"] = "0000";
	$json["result"]["value"] = "succeeded";
	$json["order"]["id"] = $db_handler->get_last_paper_order_id($conn);
} else {
	$json["result"]["code"] = "0001";
	$json["result"]["value"] = "failed";
}

$db_handler->disconnect($conn);

// output json
echo json_encode($json);

?>