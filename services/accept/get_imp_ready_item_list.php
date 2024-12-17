<?php
/**
 * Created by PhpStorm.
 * User: dpdev01
 * Date: 2018-07-20
 * Time: 11:54
 */

include_once($_SERVER["DOCUMENT_ROOT"] . "/services/accept/db_handler.php");
include_once($_SERVER["DOCUMENT_ROOT"] . "/services/accept/category_handler.php");

$token = $_GET["token"];
$farms = $_GET["farms"];
reset($_GET);

$json = array();

$db_handler = new db_handler();
$conn = $db_handler->connect();

if (strpos($farms, "COMMERCIAL") !== false) {
	$items = $db_handler->get_imp_ready_items_for_commercial($conn);
	if (count($items) == 0) {
		$json["result"]["code"] = "0001";
		$json["result"]["value"] = "no items";
	} else {
		$json["result"]["code"] = "0000";
		$json["result"]["value"] = "succeeded";

		foreach ($items as $value) {
			$farm_index = category_handler::get_farm_index($value["cate_code"], $value["paper_code"]);
			if ($farm_index === category_handler::farm_index_commercial) {
				$item = array();
				$item["item"]["id"] = $value["item_id"];
				$item["item"]["temp_no"] = $value["item_temp_no"];
				$item["category"]["id"] = $value["cate_code"];
				$item["category"]["name"] = $value["cate_name"];
				$item["customer"]["id"] = $value["customer_id"];
				$item["customer"]["name"] = $value["customer_name"];
				$item["size_name"] = $value["size_name"];
				$item["side_count"] = ($value["side_dvs"] === "단면" ? 1 : 2);
				$item["color_info"]["name"] = $value["color_name"];
				$color_arr = explode(" / ", $value["color_name"]);
				$item["color_info"]["front"] = substr($color_arr[0], -1);
				$item["color_info"]["back"] = substr($color_arr[1], -1);
				$item["quantity"] = $value["quantity"];
				switch ($value['quantity_unit']) {
					case "R":
						$item['quantity'] .= "R";
						break;
					case "권":
						$item['quantity'] .= "V";
						break;
					case "부":
						$item['quantity'] .= "C";
						break;
				}
				$item["paper_info"] = $value["paper_name"] . " " . $value["paper_color"] . " " . $value["paper_weight"];
				$item["ordered_date"] = $value["ordered_date"];
				$json["items"][] = $item;
			}
		}
	}
} else {
	$json["result"]["code"] = "0010";
	$json["result"]["value"] = "not-supported";
}

$db_handler->disconnect($conn);

// output json
echo json_encode($json);

?>