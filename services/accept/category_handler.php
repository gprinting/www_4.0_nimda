<?php
/**
 * Created by PhpStorm.
 * User: dpdev01
 * Date: 2018-06-07
 * Time: 14:22
 */

class category_handler {
    const farm_index_none = 0;
    const farm_index_normal = 1;
    const farm_index_real = 2;
    const farm_index_commercial = 3;

    public static function can_be_accepted_automatically($cate_code, $paper_code) {
        $automatic = false;

        $code_part1 = substr($cate_code, 0, 3);
        $code_part2 = substr($cate_code, 3, 3);
        $code_part3 = substr($cate_code, 6, 3);

        switch ($code_part1) {
            case "003":
                if ($code_part2 === "001" || $code_part2 === "002") {
                    $automatic = true;
                }
                break;
            case "004":
                if ($code_part2 === "001" || $code_part2 === "002") {
                    $automatic = true;
                } else if ($code_part2 === "003") {
                    if ($code_part3 === "001")
                        $automatic = true;
                }
                break;
            case "005":
                if ($code_part2 === "001" || $code_part2 === "003") {
                    if ($paper_code == "243")
                        $automatic = true;
                }
                break;
        }

        return $automatic;
    }

    public static function get_farm_index($cate_code, $paper_code) {
        $index = category_handler::farm_index_none;

        $code_part1 = substr($cate_code, 0, 3);
        $code_part2 = substr($cate_code, 3, 3);
        $code_part3 = substr($cate_code, 6, 3);

		switch ($code_part1) {
			case "001":
				$index = category_handler::farm_index_commercial;
				break;
			case "002":
                $index = category_handler::farm_index_real;
				break;
			case "003":
                $index = category_handler::farm_index_normal;
				break;
			case "004":
                $index = category_handler::farm_index_normal;
				break;
			case "005":
				if ($code_part2 === "001" || $code_part2 === "003") {
					if ($paper_code == "243")
						$index = category_handler::farm_index_normal;
					else
						$index = category_handler::farm_index_commercial;
				} else {
					$index = category_handler::farm_index_commercial;
				}
				break;
			case "006":
                $index = category_handler::farm_index_commercial;
				break;
			case "007":
				$index = category_handler::farm_index_normal;
				break;
			case "008":
				$index = category_handler::farm_index_normal;
				break;
			case "009":
				$index = category_handler::farm_index_normal;
				break;
		}

        return $index;
    }
}

?>