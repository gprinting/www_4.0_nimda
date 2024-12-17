<?php
/**
 * Created by PhpStorm.
 * User: dpdev01
 * Date: 2018-07-17
 * Time: 17:01
 */

include_once($_SERVER["DOCUMENT_ROOT"] . "/services/accept/db_handler.php");
include_once($_SERVER["DOCUMENT_ROOT"] . "/services/accept/string_util.php");

$token = $_POST["token"];
$imp_method = $_POST["imp_method"];
$plate_class = $_POST["plate_class"];
$plate_size = $_POST["plate_size"];
$paper_name = $_POST["paper_name"];
$front_side_color_count = $_POST["front_side_color_count"];
$back_side_color_count = $_POST["back_side_color_count"];
$print_method = $_POST["print_method"];
$quantity = $_POST["quantity"];
$print_house = $_POST["print_house"];
$memo = $_POST["memo"];
$item_ids = explode('|', $_POST["item_ids"]);
$paper_order = $_POST["paper_order"];
$paper_mill = $_POST["paper_mill"];
$paper_grain = $_POST["paper_grain"];
reset($_POST);

$json = array();

$db_handler = new db_handler();
$conn = $db_handler->connect();

$plate_id = $db_handler->get_last_plate_id($conn, $imp_method);
if (string_util::IsNullOrEmptyString($plate_id) == true) {
	if ($imp_method === "COMMERCIAL") {
		$plate_id = substr(date("Ymd"), 2) . "_9001";
	}
} else {
	if ($imp_method === "COMMERCIAL") {
		$parts = explode('_', $plate_id);
		$parts[1] = ((string) (((int) $parts[1]) + 1));
		$plate_id = $parts[0] . "_" . $parts[1];
	}
}

$print_method_desc = "";
switch ($print_method) {
	case "0":
		$print_method_desc = ((int) $back_side_color_count > 0 ? "양면" : "단면");
		break;
	case "1":
		$print_method_desc = "돈땡";
		break;
	case "2":
		$print_method_desc = "구와이돈땡";
		break;
	case "3":
		$print_method_desc = "홍각";
		break;
}
$plate_title = $plate_size . "_" . $print_method_desc . ($front_side_color_count + $back_side_color_count) . "도" . "_" . $print_house;

if ($db_handler->add_plate($conn, $plate_id, $plate_size, $paper_name, $quantity, $front_side_color_count, $back_side_color_count, $imp_method, $plate_class, $plate_title, $print_house, $memo) === true) {
	$json["result"]["code"] = "0000";
	$json["result"]["value"] = "succeeded";
	$json["plate"]["id"] = $plate_id;

	if ($db_handler->update_plate_items_with_plate_id($conn, $item_ids, $plate_id) == true) {
		if ($paper_order === "1") {
			if ($db_handler->add_paper_order($conn, $paper_mill, $paper_info, $plate_size, $plate_size, $paper_grain, $quantity, $print_house, $sequence, "") === true) {
				$json["result"]["code"] = "0000";
				$json["result"]["value"] = "succeeded";
			} else {
				$json["result"]["code"] = "0003";
				$json["result"]["value"] = "failed to add paper order";
			}
		}
	} else {
		$json["result"]["code"] = "0002";
		$json["result"]["value"] = "failed to update plate items";
	}
} else {
	$json["result"]["code"] = "0001";
	$json["result"]["value"] = "failed to add plate";
}

$db_handler->disconnect($conn);

// output json
echo json_encode($json);

?>