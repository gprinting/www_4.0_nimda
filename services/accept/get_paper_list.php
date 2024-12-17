<?php
/**
 * Created by PhpStorm.
 * User: dpdev01
 * Date: 2018-07-01
 * Time: 16:36
 */

include_once($_SERVER["DOCUMENT_ROOT"] . "/services/accept/db_handler.php");

$token = $_GET["token"];
reset($_GET);

$json = array();

$db_handler = new db_handler();
$conn = $db_handler->connect();

$papers = $db_handler->get_all_papers($conn);
if ($order_id !== "") {
	$json["result"]["code"] = "0000";
	$json["result"]["value"] = "succeeded";
	$json["papers"] = $papers;
} else {
	$json["result"]["code"] = "0001";
	$json["result"]["value"] = "no papers";
}

$db_handler->disconnect($conn);

// output json
echo json_encode($json);

?>