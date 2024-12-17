#! /usr/local/bin/php -f
<?
/**
 * @file insert_bl_after_foil_press.php
 *
 * @brief 전단 박/형압/엠보싱 데이터 입력
 * 엠보싱은 박과 똑같은 가격을 입력한다
 *
 * @detail csv 셀 순서는 아래와 같다
 * [0]  => 수량
 * [1]  => 단면일 때 가격
 * [2]  => 양면같을 때 가격
 */
include_once(dirname(__FILE__) . '/ConnectionPool.php');
include_once(dirname(__FILE__) . '/CommonDAO.php');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$dao = new CommonDAO();

if (count($argv) < 3) {
    echo "Useage : ./duplicate_cate_after_price.php [foil/holo_foil/press/embossing] [csv_file_name]\n";
    exit;
}

$after_en  = $argv[1];
$file_name = $argv[2];

$foil_arr = array(
    "금박",
    "은박",
    "청박",
    "적박",
    "녹박",
    "먹박"
);

$dvs_arr = array();

$holo_foil_arr = array(
    "홀로그램 은펄",
    "홀로그램 별",
    "홀로그램 물방울"
);

$sortcode_arr = array(
    "003003001",
    "003003002",
    "003003003",
    "003003004"
);

$after_arr = null;

if ($after_en === "press") {
    // 형압
    $dvs_arr[] = "단면";

    $after_arr = array("형압");
} else if ($after_en === "foil") {
    // 박
    $dvs_arr[] = "단면";
    $dvs_arr[] = "양면";

    $after_arr = $foil_arr;
} else if ($after_en === "holo_foil") {
    // 홀로그램 박
    $dvs_arr[] = "단면";
    $dvs_arr[] = "양면";

    $after_arr = $holo_foil_arr;
} else {
    // 엠보싱
    $dvs_arr[] = "단면";
    $dvs_arr[] = "양면";

    $after_arr = array("엠보싱");
}

$fd = fopen(dirname(__FILE__) . "/csv/" . $file_name . ".csv", 'r');

if ($fd === false) {
    echo "fopen ERR\n";
    exit;
}

$j = 0;
while (($data = fgetcsv($fd)) !== false) {
    $arr[$j++] = $data;
}
fclose($fd);

$query  = "\n INSERT INTO after_foil_press_price (";
$query .= "\n     cate_sortcode ";
$query .= "\n    ,after_name";
$query .= "\n    ,dvs";
$query .= "\n    ,amt";
$query .= "\n    ,price";
$query .= "\n ) VALUES (";
$query .= "\n     '%s'";
$query .= "\n    ,'%s'";
$query .= "\n    ,'%s'";
$query .= "\n    ,'%s'";
$query .= "\n    ,'%s'";
$query .= "\n )";

$conn->debug = 1;
foreach ($arr as $data) {
    foreach ($sortcode_arr as $sortcode) {
        foreach ($after_arr as $after) {
            foreach ($dvs_arr as $dvs) {
                $price = 0;

                if ($dvs === "단면") {
                    $price = $data[1];
                } else if ($dvs === "양면") {
                    $price = $data[2];
                }

                $q_str = sprintf($query, $sortcode
                                       , $after
                                       , $dvs
                                       , $data[0]
                                       , $price);

                $conn->Execute($q_str);
            }
        }
    }
}
?>
