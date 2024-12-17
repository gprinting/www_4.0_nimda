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

$query  = "\n SELECT DISTINCT  A.affil";
$query .= "\n                 ,B.cate_sortcode";
$query .= "\n            FROM  prdt_stan AS A, cate_stan AS B";
$query .= "\n           WHERE A.prdt_stan_seqno = B.prdt_stan_seqno";
$query .= "\n        ORDER BY B.cate_sortcode";

$rs = $conn->Execute($query);

$arr = array();

while ($rs && !$rs->EOF) {
    $fields = $rs->fields;

    $arr[$fields["cate_sortcode"]][$fields["affil"]] = true;

    $rs->MoveNext();
}
unset($rs);

$query  = "\n UPDATE  cate_after";
$query .= "\n    SET  affil = '%s'";
$query .= "\n  WHERE  cate_sortcode = '%s'";

$arr_count = count($arr);

foreach ($arr as $cate_sortcode => $temp) {
    if (count($temp) < 2) {
        continue;
    }

    foreach ($temp as $affil => $val) {
        $q_str = sprintf($query, $affil
                               , $cate_sortcode);

        echo $q_str;
    }
}
?>
