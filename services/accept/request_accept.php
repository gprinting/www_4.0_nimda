<?php
/**
 * Created by PhpStorm.
 * User: dpdev01
 * Date: 2018-06-04
 * Time: 13:57
 */

include_once($_SERVER["DOCUMENT_ROOT"] . "/services/accept/db_handler.php");
include_once($_SERVER["DOCUMENT_ROOT"] . "/services/accept/category_handler.php");
include_once($_SERVER["DOCUMENT_ROOT"] . "/services/accept/accept_type.php");
include_once($_SERVER["DOCUMENT_ROOT"] . "/services/accept/string_util.php");

$token = $_POST["token"];
$order_id = $_POST["order_id"];
reset($_POST);

$cate = "";

$json = array();

if ($order_id === "") {
    $json["result"]["code"] = "0001";
    $json["result"]["value"] = "order id is omitted";
} else {
    $db_handler = new db_handler();
    $conn = $db_handler->connect();

    $accept_id = "";

    if ($db_handler->is_order_id_valid($conn, $order_id) === false) {
        $db_handler->disconnect($conn);

        $json["result"]["code"] = "0006";
        $json["result"]["value"] = "invaild order id";
        echo json_encode($json);

        return ;
    }

    $accept_type = accept_type::none;

    $order_infos = $db_handler->get_order_info_for_accept_branch($conn, $order_id);
    $farm_index = category_handler::get_farm_index($order_infos["cate_code"], $order_infos["paper_code"]);

    $last_work_info = $db_handler->get_last_accept_work($conn, $order_id);
    if (count($last_work_info) === 0) {  // new
        // check order whether this order is IBM order or not
        $ext = substr($order_infos["file_name"], -3);
        if (strtolower($ext) !== "sit") {
            // check order whether this order has file shared with other orders
            // go to retouch
			if ($accept_type === accept_type::none) {
				if ($db_handler->get_order_count_in_one_file_group($conn, $order_id) > 1)
					$accept_type = accept_type::retouch_one_file;
			}

            if ($accept_type === accept_type::none) {
                if ($farm_index === category_handler::farm_index_commercial)
                    $accept_type = accept_type::manu_commercial_ibm;
            }

			if ($accept_type === accept_type::none) {
				if (string_util::IsNullOrEmptyString($infos["dp_image"]) === false) {
					if ($farm_index === category_handler::farm_index_normal)
						$accept_type = accept_type::manu_normal_ibm;
					else if ($farm_index === category_handler::farm_index_real)
						$accept_type = accept_type::manu_real_ibm;
				}
			}

			if ($accept_type === accept_type::none) {
                $customer_info = $db_handler->get_customer_info($conn, $order_id);
                if (strtolower($customer_info["manual_yn"]) === "y") {
                    if ($farm_index === category_handler::farm_index_normal)
                        $accept_type = accept_type::manu_normal_ibm;
                    else if ($farm_index === category_handler::farm_index_real)
                        $accept_type = accept_type::manu_real_ibm;
                }
            }

            if ($accept_type === accept_type::none) {
                if (string_util::IsNullOrEmptyString($infos["finish"]) === false) {
                    foreach ($infos["finish"] as $finish) {
                        if ($finish !== "귀도리") {
                            if ($farm_index === category_handler::farm_index_normal)
                                $accept_type = accept_type::manu_normal_ibm;
                            else if ($farm_index === category_handler::farm_index_real)
                                $accept_type = accept_type::manu_real_ibm;
                            break;
                        }
                    }
                }
            }

            if ($accept_type === accept_type::none) {
                // check category
                $automatic = category_handler::can_be_accepted_automatically($order_infos["cate_code"], $order_infos["paper_code"]);
                if ($automatic === true) {
                    $accept_type = accept_type::auto_normal;
                } else {
                    if ($farm_index === category_handler::farm_index_normal)
                        $accept_type = accept_type::manu_normal_ibm;
                    else if ($farm_index === category_handler::farm_index_real)
                        $accept_type = accept_type::manu_real_ibm;
                }
            }

            $accept_id = $db_handler->create_accept_work($conn, $order_id, $accept_type, $order_infos["order_title"]);
            if ($accept_id !== "") {
				if ($accept_type !== accept_type::retouch_one_file) {
					$db_handler->create_accept_items($conn, $accept_id, (int)$order_infos["item_count"], "00");
				}

                if ($db_handler->add_accept_event($conn, $accept_id, -1, "01") === true) {
                    $json["result"]["code"] = "0000";
                    $json["result"]["value"] = "succeeded";
                } else {
                    $json["result"]["code"] = "0002";
                    $json["result"]["value"] = "failed";
                }
            } else {
                $json["result"]["code"] = "0003";
                $json["result"]["value"] = "failed";
            }
        } else {    // mac
            if ($farm_index === category_handler::farm_index_normal)
                $accept_type = accept_type::manu_normal_mac;
            else if ($farm_index === category_handler::farm_index_real)
                $accept_type = accept_type::manu_real_mac;
            else if ($farm_index === category_handler::farm_index_commercial)
                $accept_type = accept_type::manu_commercial_mac;

            $accept_id = $db_handler->create_accept_work($conn, $order_id, $accept_type, $order_infos["order_title"]);
            if ($accept_id !== "") {
				$db_handler->create_accept_items($conn, $accept_id, (int) $order_infos["item_count"], "00");

				if ($db_handler->add_accept_event($conn, $accept_id, -1, "01") === true) {
                    $json["result"]["code"] = "0000";
                    $json["result"]["value"] = "succeeded";
                } else {
                    $json["result"]["code"] = "0002";
                    $json["result"]["value"] = "failed";
                }
            } else {
                $json["result"]["code"] = "0003";
                $json["result"]["value"] = "failed";
            }
        }
    } else {    // retry
        if (string_util::IsNullOrEmptyString($last_work_info["accept_result"]) === true) {
            $json["result"]["code"] = "0005";
            $json["result"]["value"] = "already exist";
        } else {
            switch ($last_work_info["accept_type"]) {
                case accept_type::auto_normal:
                    $retouchable = false;
                    if ($last_work_info["accept_result"] === "0") {
                        $retouchable = true;
                        $results = explode('|', $last_work_info["accept_report"]);
                        foreach ($results as $result) {
                            switch ($result) {
                                case "0101":
                                case "0102":
                                case "0201":
                                case "0202":
                                case "0212":
                                case "0231":
                                case "0301":
                                case "0302":
                                case "0303":
                                case "0304":
                                case "0401":
                                case "0402":
                                case "0504":
                                case "0505":
                                case "0508":
                                    break;
                                default:
                                    $retouchable = false;
                                    break;
                            }

                            if ($retouchable === false) {
                                break;
                            }
                        }
                    }

                    if ($retouchable === true) {
                        $accept_type = accept_type::retouch_auto_failed;
                    } else {
                        if ($farm_index === category_handler::farm_index_normal)
                            $accept_type = accept_type::manu_normal_ibm;
                        else if ($farm_index === category_handler::farm_index_real)
                            $accept_type = accept_type::manu_real_ibm;
                        else if ($farm_index === category_handler::farm_index_commercial)
                            $accept_type = accept_type::manu_commercial_ibm;
                    }

                    $accept_id = $db_handler->create_accept_work($conn, $order_id, $accept_type, $order_infos["order_title"]);
                    if ($accept_id !== "") {
						if ($accept_type !== accept_type::retouch_auto_failed) {
							$db_handler->create_accept_items($conn, $accept_id, (int)$order_infos["item_count"], "00");
						}

						if ($db_handler->add_accept_event($conn, $accept_id, -1, "01") === true) {
                            $json["result"]["code"] = "0000";
                            $json["result"]["value"] = "succeeded";
                        } else {
                            $json["result"]["code"] = "0002";
                            $json["result"]["value"] = "failed";
                        }
                    } else {
                        $json["result"]["code"] = "0003";
                        $json["result"]["value"] = "failed";
                    }

                    break;

                case accept_type::auto_retry:
                    if ($farm_index === category_handler::farm_index_normal)
                        $accept_type = accept_type::manu_normal_ibm;
                    else if ($farm_index === category_handler::farm_index_real)
                        $accept_type = accept_type::manu_real_ibm;

                    $accept_id = $db_handler->create_accept_work($conn, $order_id, $accept_type, $order_infos["order_title"]);
                    if ($accept_id !== "") {
						$db_handler->create_accept_items($conn, $accept_id, (int) $order_infos["item_count"], "00");

						if ($db_handler->add_accept_event($conn, $accept_id, -1, "01") === true) {
                            $json["result"]["code"] = "0000";
                            $json["result"]["value"] = "succeeded";
                        } else {
                            $json["result"]["code"] = "0002";
                            $json["result"]["value"] = "failed";
                        }
                    } else {
                        $json["result"]["code"] = "0003";
                        $json["result"]["value"] = "failed";
                    }

                    break;

                case accept_type::retouch_auto_failed:
                case accept_type::retouch_one_file:
                    if ($last_work_info["accept_result"] === "1") {
                        if ($accept_type === accept_type::none) {
                            $customer_info = $db_handler->get_customer_info($conn, $order_id);
                            if (strtolower($customer_info["manual_yn"]) === "y") {
                                if ($farm_index === category_handler::farm_index_normal)
                                    $accept_type = accept_type::manu_normal_retry;
                                else if ($farm_index === category_handler::farm_index_real)
                                    $accept_type = accept_type::manu_real_retry;
                            }
                        }

                        if ($accept_type === accept_type::none) {
                            if (string_util::IsNullOrEmptyString($infos["finish"]) === false) {
                                foreach ($infos["finish"] as $finish) {
                                    if ($finish !== "귀도리") {
                                        if ($farm_index === category_handler::farm_index_normal)
                                            $accept_type = accept_type::manu_normal_retry;
                                        else if ($farm_index === category_handler::farm_index_real)
                                            $accept_type = accept_type::manu_real_retry;
                                        break;
                                    }
                                }
                            }
                        }

                        if ($accept_type === accept_type::none) {
                            // check category
                            $automatic = category_handler::can_be_accepted_automatically($order_infos["cate_code"], $order_infos["paper_code"]);
                            if ($automatic === true) {
                                $accept_type = accept_type::auto_retry;
                            } else {
                                if ($farm_index === category_handler::farm_index_normal)
                                    $accept_type = accept_type::manu_normal_retry;
                                else if ($farm_index === category_handler::farm_index_real)
                                    $accept_type = accept_type::manu_real_retry;
                            }
                        }
                    } else {
                        if ($farm_index === category_handler::farm_index_normal)
                            $accept_type = accept_type::manu_normal_ibm;
                        else if ($farm_index === category_handler::farm_index_real)
                            $accept_type = accept_type::manu_real_ibm;
                        else if ($farm_index === category_handler::farm_index_commercial)
                            $accept_type = accept_type::manu_commercial_ibm;
                    }

                    $accept_id = $db_handler->create_accept_work($conn, $order_id, $accept_type, $order_infos["order_title"]);
                    if ($accept_id !== "") {
						$db_handler->create_accept_items($conn, $accept_id, (int) $order_infos["item_count"], "00");

						if ($db_handler->add_accept_event($conn, $accept_id, -1, "01") === true) {
                            $json["result"]["code"] = "0000";
                            $json["result"]["value"] = "succeeded";
                        } else {
                            $json["result"]["code"] = "0002";
                            $json["result"]["value"] = "failed";
                        }
                    } else {
                        $json["result"]["code"] = "0003";
                        $json["result"]["value"] = "failed";
                    }

                    break;
            }
        }
    }

    $db_handler->disconnect($conn);

    // check result folder path
    if (string_util::IsNullOrEmptyString($accept_id) === false) {
        $year = "20" . substr($accept_id, 0, 2);
        $month = substr($accept_id, 2, 2);
        $day = substr($accept_id, 4, 2);
        $date = sprintf("%s/%s/%s/", $year, $month, $day);

        $result_folder_path = $_SERVER["SiteHome"] . "/ndrive/attach/gp/order_detail_count_file/" . $date;
        if(!is_dir($result_folder_path)) {
            $old = umask(0);
            mkdir($result_folder_path, 0777, true);
            umask($old);
        }

        $preview_folder_path = $_SERVER["SiteHome"] . "/ndrive/attach/gp/order_detail_count_preview_file/" . $date;
        if(!is_dir($preview_folder_path)) {
            $old = umask(0);
            mkdir($preview_folder_path, 0777, true);
            umask($old);
        }

        $altered_folder_path = $_SERVER["SiteHome"] . "/ndrive/attach/gp/altered_file/" . $date;
        if(!is_dir($altered_folder_path)) {
            $old = umask(0);
            mkdir($altered_folder_path, 0777, true);
            umask($old);
        }
    }
}

$json["result"]["ext"] = strtolower($ext);
$json["result"]["accept_type"] = $accept_type;
$json["result"]["farm_index"] = $farm_index;
// output json
echo json_encode($json);

?>