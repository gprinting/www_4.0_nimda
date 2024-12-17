#! /usr/local/bin/php -f
<?
/**
 * @file update_cate_print_seq.php
 *
 * @brief 카테고리 인쇄 정렬순서 업데이트
 */
include_once(dirname(__FILE__) . '/ConnectionPool.php');
include_once(dirname(__FILE__) . '/CommonDAO.php');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fd = fopen(dirname(__FILE__) . "/csv/cate_print_seq.csv", 'r');

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

    $key = sprintf("%s|%s", $temp[0], $temp[1]);

    if ($dup_chk[$key] === null) {
        $dup_chk[$key] = true;


        $sort_arr[$j++] = $temp;
    }
}
unset($arr);

$query  = "\n UPDATE  cate_print";
$query .= "\n    SET  seq = '%s'";
$query .= "\n  WHERE  cate_print_seqno = '%s'";

$arr_count = count($sort_arr);

for ($i = 0; $i < $arr_count; $i++) {
    $temp = $sort_arr[$i];

    $q_str = sprintf($query, $temp[0], $temp[1]);

    echo $q_str;
    $conn->Execute($q_str);
}
?>
