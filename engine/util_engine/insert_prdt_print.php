#! /usr/local/bin/php -f
<?
/**
 * @file insert_prdt_print.php
 *
 * @brief 상품_인쇄 데이터 입력
 *
 * @detail csv 셀 순서는 아래와 같다
 * [0] => 코팅명함
 * [1] => 양면칼라8도
 * [2] => 디지털인쇄도수
 * [3] => 양면
 * [4] => 4
 * [5] => 0
 * [6] => 4
 * [7] => 0
 * [8] => 8
 */
include_once(dirname(__FILE__) . '/ConnectionPool.php');
include_once(dirname(__FILE__) . '/CommonDAO.php');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$dao = new CommonDAO();

$fd = fopen(dirname(__FILE__) . "/csv/prdt_cate_print.csv", 'r');

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
    $key = sprintf("%s|%s|%s|%s|%s|%s|%s|%s", $temp[0]
                                            , $temp[1]
                                            , $temp[2]
                                            , $temp[3]
                                            , $temp[4]
                                            , $temp[5]
                                            , $temp[6]
                                            , $temp[7]
                                            , $temp[8]);

    if ($dup_chk[$key] === null) {
        $dup_chk[$key] = true;

        $sort_arr[$j++] = $temp;
    }
}
unset($arr);
unset($dup_chk);

$arr_count = count($sort_arr);

$query  = "\n INSERT INTO prdt_print (";
$query .= "\n      sort";
$query .= "\n     ,print_name";
$query .= "\n     ,purp_dvs";
$query .= "\n     ,name";
$query .= "\n     ,beforeside_tmpt";
$query .= "\n     ,aftside_tmpt";
$query .= "\n     ,add_tmpt";
$query .= "\n     ,tot_tmpt";
$query .= "\n     ,output_board_amt";
$query .= "\n     ,side_dvs";
$query .= "\n ) VALUES ";

$values  = "\n ('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s'),";

// 쿼리 한단위당 500개 단위로 끊음
$chunk = ceil($arr_count / 500);

$j = 0;
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

        $sortcode_m = "009001";
        if ($temp[2] !== "디지털인쇄도수") {
            $sortcode_m = $dao->selectCateMid($conn, $temp[0]);
        }

        $rs = $dao->selectPrdtPrintInfo($conn, $sortcode_m);

        while ($rs && !$rs->EOF) {
            $fields = $rs->fields;

            $key = sprintf("%s|%s|%s|%s|%s", $temp[2]
                                           , $fields["print_name"]
                                           , $fields["purp_dvs"]
                                           , $temp[3]
                                           , $temp[1]);

            if ($dup_chk[$key] === null) {
                $dup_chk[$key] = true;

                $str_tmp .= sprintf($values, $temp[2]
                                           , $fields["print_name"]
                                           , $fields["purp_dvs"]
                                           , $temp[1]
                                           , $temp[4]
                                           , $temp[5]
                                           , $temp[6]
                                           , $temp[7]
                                           , $temp[8]
                                           , $temp[3]);
            }

            $rs->MoveNext();
        }

    }

    $q_str .= substr($str_tmp, 0, -1);

    echo $q_str;
    //$conn->Execute($q_str);

    $j++;
}
?>
