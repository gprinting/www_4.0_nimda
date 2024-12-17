#! /usr/local/bin/php -f
<?
/**
 * @file insert_prdt_output_info.php
 *
 * @brief 상품_출력_정보 데이터 입력
 *
 * @detail csv 셀 순서는 아래와 같다
 * [0] => 46
 * [1] => 2절 8개판
 * [2] => 아그파판
 * [3] => 760*635
 */
include_once(dirname(__FILE__) . '/ConnectionPool.php');
include_once(dirname(__FILE__) . '/CommonDAO.php');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fd = fopen(dirname(__FILE__) . "/csv/prdt_output_info.csv", 'r');

if ($fd === false) {
    echo "fopen ERR\n";
    exit;
}

$arr = array();

$j = 0;
while (($data = fgetcsv($fd)) !== false) {
    $arr[$j++] = $data;
}
fclose($fd);

$arr_count = count($arr);

$dup_chk = array();

$sort_arr = array();

$j = 0;
for ($i = 0; $i < $arr_count; $i++) {
    $temp = $arr[$i];

    if (strpos($temp[3], '*') === false) {
        $temp[3] = "-*-";
    }

    $key = sprintf("%s|%s|%s|%s", $temp[0]
                                , $temp[1]
                                , $temp[2]
                                , $temp[3]);

    if ($dup_chk[$key] === null) {
        $dup_chk[$key] = true;

        $sort_arr[$j++] = $temp;
    }
}
unset($arr);

$arr_count = count($sort_arr);

$query  = "\n INSERT INTO prdt_output_info (";
$query .= "\n      output_name";
$query .= "\n     ,affil";
$query .= "\n     ,output_board_dvs";
$query .= "\n     ,size";
$query .= "\n     ,mpcode";
$query .= "\n ) VALUES ";

$values  = "\n ('%s', '%s', '%s', '%s', '%s'),";

// 쿼리 한단위당 500개 단위로 끊음
$chunk = ceil($arr_count / 500);

$j = 0;
$k = 1;
$q_str = '';

for ($i = 1; $i <= $chunk; $i++) {
    $limit = $i * 500;

    $q_str = $query;
    $str_tmp = '';

    while ($j < $limit) {
        $temp = $sort_arr[$j++];

        if (empty($temp)) {
            break;
        }

        $str_tmp .= sprintf($values, $temp[1]
                                   , $temp[0]
                                   , $temp[2]
                                   , $temp[3]
                                   , $k++);
    }

    $q_str .= substr($str_tmp, 0, -1);

    echo $q_str . "\n";

    //$conn->Execute($q_str);

    $j++;
}
?>
