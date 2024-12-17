#! /usr/local/bin/php -f
<?
/**
 * @file update_prdt_stan.php
 *
 * @brief 상품_규격 데이터 수정
 *
 * @detail csv 셀 순서는 아래와 같다
 * [0] => 90*50
 * [1] => 디지털명함사이즈
 * [2] => 투터치재단
 * [3] => 92
 * [4] => 52
 * [5] => 90
 * [6] => 50
 * [7] => -
 * [8] => -
 * [9] => -
 * [10] => -
 * [11] => 46
 * [12] => 2절 8개판
 * [13] => 아그파판
 * [14] => 760*635
 */
include_once(dirname(__FILE__) . '/ConnectionPool.php');
include_once(dirname(__FILE__) . '/CommonDAO.php');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fd = fopen(dirname(__FILE__) . "/csv/prdt_stan.csv", 'r');

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
    $key = sprintf("%s|%s|%s|%s|%s|%s|%s|%s|%s|%s|%s|%s|%s|%s|%s", $temp[0]
                                                                 , $temp[1]
                                                                 , $temp[2]
                                                                 , $temp[3]
                                                                 , $temp[4]
                                                                 , $temp[5]
                                                                 , $temp[6]
                                                                 , $temp[7]
                                                                 , $temp[8]
                                                                 , $temp[9]
                                                                 , $temp[10]
                                                                 , $temp[11]
                                                                 , $temp[12]
                                                                 , $temp[13]
                                                                 , $temp[14]);

    if ($dup_chk[$key] === null) {
        $dup_chk[$key] = true;

        $sort_arr[$j++] = $temp;
    }
}
unset($arr);

$arr_count = count($sort_arr);

$query  = "\n UPDATE  prdt_stan";
$query .= "\n    SET  cut_wid_size  = '%s'";
$query .= "\n        ,cut_vert_size = '%s'";
$query .= "\n  WHERE  sort = '%s'";
$query .= "\n    AND  name = '%s';";


// 쿼리 한단위당 500개 단위로 끊음
$chunk = ceil($arr_count / 500);

$j = 0;
$q_str = '';

for ($i = 1; $i <= $chunk; $i++) {
    $limit = $i * 500;

    $str_tmp = '';

    while ($j < $limit) {
        $temp = $sort_arr[$j++];

        if (empty($temp)) {
            break;
        }

        $str_tmp .= sprintf($query, $temp[5]
                                  , $temp[6]
                                  , $temp[1]
                                  , $temp[0]);
    }

    $q_str .= substr($str_tmp, 0, -1);

    echo $q_str . "\n";
    //$conn->Execute($q_str);

    $j++;
}
?>
