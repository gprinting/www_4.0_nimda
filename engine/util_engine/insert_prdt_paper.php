#! /usr/local/bin/php -f
<?
/**
 * @file insert_prdt_paper.php
 *
 * @brief 상품_종이 데이터 입력
 *
 * @detail csv 셀 순서는 아래와 같다
 * [0] => 이름
 * [1] => 구분
 * [2] => 색상
 * [3] => 평량
 * [4] => 느낌
 * [5] => 분류
 * [6] => 평량단위
 * [7] => 계열
 * [8] => 사이즈
 * [9] => 기준단위
 */
include_once(dirname(__FILE__) . '/ConnectionPool.php');
include_once(dirname(__FILE__) . '/CommonDAO.php');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fd = fopen(dirname(__FILE__) . "/csv/prdt_paper.csv", 'r');

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
$special_arr = array();

$j = 0;
$k = 0;
for ($i = 0; $i < $arr_count; $i++) {
    $temp = $arr[$i];

    if (empty($temp[0])) {
        continue;
    }

    if (empty($temp[1])) {
        $temp[1] = '-';
    }

    $key = sprintf("%s|%s|%s|%s|%s|%s|%s", $temp[0]
                                         , $temp[1]
                                         , $temp[2]
                                         , $temp[3]
                                         , $temp[5]
                                         , $temp[6]
                                         , $temp[9]);

    if ($dup_chk[$key] === null) {
        $dup_chk[$key] = true;

        $sort_arr[$j++] = $temp;
    }

    $key = sprintf("%s|%s|%s|%s|%s|%s|%s|%s|%s", $temp[0]
                                               , $temp[1]
                                               , $temp[2]
                                               , $temp[3]
                                               , $temp[5]
                                               , $temp[6]
                                               , $temp[7]
                                               , $temp[8]
                                               , $temp[9]);

    if ($temp[7] === '별' && $dup_chk[$key] === null) {
        $dup_chk[$key] = true;
        
        $special_arr[$k++] = $temp;
    }
}
unset($dup_chk);
unset($arr);

$arr_count = count($sort_arr);

// 종이_설명 입력
$query  = "\n INSERT INTO paper_dscr (";
$query .= "\n      name";
$query .= "\n     ,dvs";
$query .= "\n     ,paper_sense";
$query .= "\n ) VALUES ";

$values  = "\n ('%s', '%s', '%s'),";

$dscr_arr = array();

$j = 0;
$str_tmp = '';

for ($i = 0; $i < $arr_count; $i++) {
    $temp = $sort_arr[$i];

    $key = sprintf("%s|%s", $temp[0]
                          , $temp[1]);

    if ($dup_chk[$key] === null) {
        $dup_chk[$key] = true;

        $str_tmp .= sprintf($values, $temp[0], $temp[1], $temp[4]);
    } else {
        continue;
    }
}

$q_str = $query . substr($str_tmp, 0, -1);

echo $q_str . "\n\n/*--------------------------*/\n";
//$conn->Execute($q_str);

// 상품_종이 입력
$query  = "\n INSERT INTO prdt_paper (";
$query .= "\n      sort";
$query .= "\n     ,name";
$query .= "\n     ,dvs";
$query .= "\n     ,color";
$query .= "\n     ,basisweight";
$query .= "\n     ,basisweight_unit";
$query .= "\n     ,affil";
$query .= "\n     ,size";
$query .= "\n     ,search_check";
$query .= "\n     ,crtr_unit";
$query .= "\n     ,mpcode";
$query .= "\n ) VALUES ";

$values  = "\n ('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s'),";

// 쿼리 한단위당 500개 단위로 끊음
$chunk = ceil($arr_count / 500);

$j = 0;
$l = 1; // mpcode
$q_str = '';

$affil_arr = array(
    "0" => array("affil" => "46",
                 "size"  => "1091*788"),
    "1" => array("affil" => "국",
                 "size"  => "939*636")
);

for ($i = 1; $i <= $chunk; $i++) {
    $limit = $i * 500;

    $q_str = $query;
    $str_tmp = '';

    while ($j < $limit) {
        $temp = $sort_arr[$j++];

        if (empty($temp)) {
            break;
        }

        for ($k = 0; $k < 2; $k++) {
            $affil = $affil_arr[$k]["affil"];
            $size  = $affil_arr[$k]["size"];

            $str_tmp .= sprintf($values, $temp[5]
                                       , $temp[0]
                                       , $temp[1]
                                       , $temp[2]
                                       , $temp[3]
                                       , 'g'
                                       , $affil
                                       , $size
                                       , sprintf("%s|%s|%s|%sg", $temp[0]
                                                               , $temp[1] 
                                                               , $temp[2]
                                                               , $temp[3])
                                       , 'R'
                                       , $l++);
        }
    }

    $q_str .= substr($str_tmp, 0, -1);

    echo $q_str . "\n";
    //$conn->Execute($q_str);
}

echo "\n/*--------------------------*/\n";

// 별계열 종이 쿼리 생성
$arr_count = count($special_arr);

$q_str = $query;
$str_tmp = '';

for ($i = 0; $i < $arr_count; $i++) {
    $temp = $special_arr[$i];

    $str_tmp .= sprintf($values, $temp[5]
                               , $temp[0]
                               , $temp[1]
                               , $temp[2]
                               , $temp[3]
                               , 'g'
                               , $temp[7]
                               , $temp[8]
                               , sprintf("%s|%s|%s|%sg", $temp[0]
                                                       , $temp[1] 
                                                       , $temp[2]
                                                       , $temp[3])
                               , 'R'
                               , $l++);
}

$q_str .= substr($str_tmp, 0, -1);

echo $q_str . "\n";
//$conn->Execute($q_str);
?>
