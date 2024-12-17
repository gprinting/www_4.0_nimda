<?php
/**
 * Created by PhpStorm.
 * User: dpdev01
 * Date: 2018-06-11
 * Time: 10:08
 */

include_once($_SERVER["DOCUMENT_ROOT"] . "/services/accept/db_handler.php");
include_once($_SERVER["DOCUMENT_ROOT"] . "/services/accept/accept_type.php");

$token = $_POST["token"];
$item_ids_s = $_POST["item_ids"];
reset($_POST);

$item_ids = explode('|', $item_ids_s);

$json = array();

$db_handler = new db_handler();
$conn = $db_handler->connect();

foreach ($item_ids as $item_id) {
    $accept_id = substr($item_id, 0, strlen($item_id) - 3);
    $accept_index = substr($item_id, -3);

    $accept_type = $db_handler->get_accept_type($conn, $accept_id);
    if (accept_type::is_auto_type((string) $accept_type) === true) {
        $db_handler->add_accept_event($conn, $accept_id, $accept_index, "32");
        $db_handler->update_accept_item_with_status($conn, $accept_id, $accept_index, "22");
    } else {
        $db_handler->add_accept_event($conn, $accept_id, $accept_index, "12");
        $db_handler->update_accept_item_with_status($conn, $accept_id, $accept_index, "40");

		$work_finished = true;
		$status_list = $db_handler->get_accept_status_list_for_accept_items($conn, $accept_id);
		foreach ($status_list as $status) {
			if ($status["status"] !== "40") {
				$work_finished = false;
				break;
			}
		}

		if ($work_finished === true) {
			$db_handler->update_accept_work_with_accept_result($conn, $accept_id, "1", "");

			// tell accept-finishing to order system
		}
    }
}

$json["result"]["code"] = "0000";
$json["result"]["value"] = "succeeded";

$db_handler->disconnect($conn);

// output json
echo json_encode($json);

?>