#! /usr/local/bin/php -f
<?
/**
 * @file insert_cate_paper.php
 *
 * @brief 카테고리_종이 데이터 입력
 *
 * @detail csv 셀 순서는 아래와 같다
 * [0]  => 코팅명함
 * [1]  => 스노우지
 * [2]  => 
 * [3]  => 백색
 * [4]  => 250
 * [5]  => 은은한
 * [6]  => 일반용지
 * [7]  => g
 * [8]  => 46
 * [9]  => 1091*768
 * [10] => R
 */
include_once(dirname(__FILE__) . '/ConnectionPool.php');
include_once(dirname(__FILE__) . '/CommonDAO.php');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$dao = new CommonDAO();

$fd = fopen(dirname(__FILE__) . "/csv/cate_paper.csv", 'r');

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

    if (empty($temp[0]) || empty($temp[1])) {
        continue;
    }

    if (empty($temp[2])) {
        $temp[2] = '-';
    }

    $key = sprintf("%s|%s|%s|%s|%s|%s", $temp[0]
                                      , $temp[1]
                                      , $temp[2]
                                      , $temp[3]
                                      , $temp[4]
                                      , $temp[6]);

    if ($dup_chk[$key] === null) {
        $dup_chk[$key] = true;


        $sort_arr[$j++] = $temp;
    }
}
unset($arr);

$arr_count = count($sort_arr);

$query  = "\n INSERT INTO cate_paper (";
$query .= "\n      cate_sortcode";
$query .= "\n     ,name";
$query .= "\n     ,dvs";
$query .= "\n     ,color";
$query .= "\n     ,basisweight";
$query .= "\n     ,sort";
$query .= "\n     ,mpcode";
$query .= "\n ) VALUES ";

$values  = "\n ('%s', '%s', '%s', '%s', '%s', '%s', '%s'),";

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

        if (empty($temp[0])) {
            continue;
        }

        if (empty($temp[1])) {
            continue;
        }

        $cate_sortcode = $dao->selectCateName($conn,
                                              array("cate_name" => $temp[0]));

        $str_tmp .= sprintf($values, $cate_sortcode
                                   , $temp[1]
                                   , empty($temp[2]) ? '-' : $temp[2]
                                   , $temp[3]
                                   , $temp[4] . 'g'
                                   , $temp[6]
                                   , ++$k);
    }

    $q_str .= substr($str_tmp, 0, -1);

    echo $q_str . "\n";
    //$conn->Execute($q_str);
}
?>
