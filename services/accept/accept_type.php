<?php
/**
 * Created by PhpStorm.
 * User: dpdev01
 * Date: 2018-06-26
 * Time: 18:20
 */

class accept_type {
    const none = "00";
    const auto_default = "10";
    const auto_normal = "11";
    const auto_retry = "12";
    const manu_default = "20";
    const manu_normal_ibm = "21";
    const manu_normal_mac = "22";
    const manu_normal_retry = "23";
    const manu_real_ibm = "24";
    const manu_real_mac = "25";
    const manu_real_retry = "26";
    const manu_commercial_ibm = "27";
    const manu_commercial_mac = "28";
    const manu_commercial_retry = "29";
    const retouch_default = "30";
    const retouch_auto_failed = "31";
    const retouch_one_file = "32";

    public static function is_auto_type($type) {
        $auto = false;
		switch ($type) {
			case "10":
			case "11":
			case "12":
				$auto = true;
				break;
		}
        return $auto;
    }
}

?>