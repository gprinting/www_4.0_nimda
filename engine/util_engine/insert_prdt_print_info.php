#! /usr/local/bin/php -f
<?
/**
 * @file insert_prdt_print_info.php
 *
 * @brief 상품_인쇄_정보 데이터 입력
 *
 * @detail csv 셀 순서는 아래와 같다
 * [0] => 카테고리 중분류
 * [1] => 인쇄명(카테고리중분류_계열 조합)
 * [2] => 계산방식
 * [3] => X
 * [4] => X
 * [5] => X
 * [6] => X
 * [7] => 용도구분
 */
include_once(dirname(__FILE__) . '/ConnectionPool.php');
include_once(dirname(__FILE__) . '/CommonDAO.php');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$dao = new CommonDAO();

$fd = fopen(dirname(__FILE__) . "/csv/prdt_print_info.csv", 'r');

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

    $key = sprintf("%s|%s|%s|%s", $temp[0]
                                , $temp[1]
                                , $temp[2]
                                , $temp[7]);

    if ($dup_chk[$key] === null) {
        $dup_chk[$key] = true;

        if ($temp[2] === "계산형") {
            $temp[1] = $temp[0] . '_' . "46";
            $temp[8] = "46";
            $sort_arr[$j++] = $temp;

            $temp[1] = $temp[0] . '_' . "국";
            $temp[8] = "국";
            $sort_arr[$j++] = $temp;
        } else {
            $temp[1] = $temp[0] . '_' . "확정형";
            $sort_arr[$j++] = $temp;
        }


    }
}
unset($arr);

$arr_count = count($sort_arr);

$query  = "\n INSERT INTO prdt_print_info (";
$query .= "\n      print_name";
$query .= "\n     ,purp_dvs";
$query .= "\n     ,cate_sortcode";
$query .= "\n     ,affil";
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

        $cate_sortcode = $dao->selectCateName($conn,
                                              array("cate_name"  => $temp[0],
                                                    "cate_level" => '2'));

        $str_tmp .= sprintf($values, $temp[1]
                                   , $temp[7]
                                   , $cate_sortcode
                                   , $temp[8]
                                   , $k++);
    }

    $q_str .= substr($str_tmp, 0, -1);

    echo $q_str . "\n";
    //$conn->Execute($q_str);

    $j++;
}
?>
