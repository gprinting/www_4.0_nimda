#! /usr/local/bin/php -f
<?
/**
 * @file update_after_affil.php
 *
 * @brief 카테고리 규격에 있는 계열로 후공정에 계열을 수정한다
 * 한 카테고리에 계열이 하나만 있는 경우에는 없음으로 처리한다
 */
include_once(dirname(__FILE__) . '/ConnectionPool.php');
include_once(dirname(__FILE__) . '/CommonDAO.php');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$MATCHING_ARR = array(
    "46|전절" => "전절",
    "46|2절" => "2절",
    "46|4절" => "4절",
    "46|8절" => "8절",
    "46|16절" => "16절",
    "46|24절" => "24절",
    "46|32절" => "32절",
    "46|48절" => "48절",
    "46|64절" => "64절",
    "국|전절" => "A1",
    "국|2절" => "A2",
    "국|4절" => "A3",
    "국|8절" => "A4",
    "국|16절" => "A5",
    "국|24절" => "A4 1/3",
    "국|32절" => "A6"
);

$query  = "\n SELECT  A.cate_after_seqno";
$query .= "\n        ,A.affil";
$query .= "\n        ,A.subpaper";
$query .= "\n   FROM  cate_after AS A";
$query .= "\n  WHERE A.subpaper IS NOT NULL && A.subpaper != ''";

$rs = $conn->Execute($query);

$arr = array();

while ($rs && !$rs->EOF) {
    $fields = $rs->fields;

    $arr[$fields["cate_after_seqno"]] = $fields["affil"] . '|' . $fields["subpaper"];

    $rs->MoveNext();
}
unset($rs);

$query  = "\n UPDATE  cate_after";
$query .= "\n    SET  size = '%s'";
$query .= "\n  WHERE  cate_after_seqno = '%s'";

$arr_count = count($arr);
$i = 1;

foreach ($arr as $seqno => $val) {
    $q_str = sprintf($query, $MATCHING_ARR[$val]
                           , $seqno);

    echo $q_str;
    //$conn->Execute($q_str);
}
?>
