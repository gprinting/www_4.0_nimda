#! /usr/local/bin/php -f
<?
/**
 * @file insert_cate_stan.php
 *
 * @brief 카테고리_규격 데이터 입력
 *
 * @detail csv 셀 순서는 아래와 같다
 * [0] => 파스텔릭스
 * [1] => 90*50
 * [2] => 디지털명함사이즈
 * [3] => 투터치재단
 * [4] => 92
 * [5] => 52
 * [6] => 90
 * [7] => 50
 * [8] => -
 * [9] => -
 * [10] => -
 * [11] => -
 * [12] => 46
 * [13] => 2절 8개판
 * [14] => 아그파판
 * [15] => 760*635
 */
include_once(dirname(__FILE__) . '/ConnectionPool.php');
include_once(dirname(__FILE__) . '/CommonDAO.php');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$dao = new CommonDAO();

$fd = fopen(dirname(__FILE__) . "/csv/cate_stan.csv", 'r');

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
    $key = sprintf("%s|%s|%s|%s|%s", $temp[0]
                                   , $temp[1]
                                   , $temp[2]
                                   , $temp[3]
                                   , $temp[12]);

    if ($dup_chk[$key] === null) {
        $dup_chk[$key] = true;

        $sort_arr[$j++] = $temp;
    }
}
unset($arr);

$arr_count = count($sort_arr);

$query  = "\n INSERT INTO cate_stan (";
$query .= "\n      cate_sortcode";
$query .= "\n     ,mpcode";
$query .= "\n     ,prdt_stan_seqno";
$query .= "\n ) VALUES ";

$values = "\n ('%s', '%s', '%s'),";

// 쿼리 한단위당 500개 단위로 끊음
$chunk = ceil($arr_count / 500);

$j = 0;
$k = 0;
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

        $param = array();
        $param["cate_name"] = $temp[0];
        if ($temp[2] === "디지털명함사이즈") {
            $param["high_sortcode"] = "009001";
        }

        $cate_sortcode = $dao->selectCateName($conn, $param);
        $prdt_stan_seqno = $dao->selectPrdtStanSeqno($conn, array("sort"  => $temp[2],
                                                                  "typ"   => $temp[3],
                                                                  "affil" => $temp[12],
                                                                  "name"  => $temp[1]));

        $str_tmp .= sprintf($values, $cate_sortcode
                                   , ++$k
                                   , $prdt_stan_seqno);
    }

    $q_str .= substr($str_tmp, 0, -1);

    echo $q_str . "\n";
    //$conn->Execute($q_str);

    $j++;
}
?>
