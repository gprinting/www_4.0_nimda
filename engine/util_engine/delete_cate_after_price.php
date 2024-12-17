#! /usr/local/bin/php -f
<?
/**
 * @file delete_cate_after_price.php
 *
 * @brief 카테고리 후공정 가격 삭제
 */
include_once(dirname(__FILE__) . '/ConnectionPool.php');
include_once(dirname(__FILE__) . '/CommonDAO.php');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

if (count($argv) < 2) {
    echo "Useage : ./delete_cate_after_price.php [sortcode]\n";
    exit;
}

$sortcode = $argv[1];

if (!isset($sortcode[8])) {
    echo "카테고리 소분류코드 입력\n";
    exit;
}

$query  = "\n SELECT B.cate_after_price_seqno AS seqno";
$query .= "\n FROM cate_after AS A, cate_after_price AS B";
$query .= "\n WHERE A.mpcode = B.cate_after_mpcode";
$query .= "\n AND A.cate_sortcode = '%s'";
$query  = sprintf($query, $sortcode);

$rs = $conn->Execute($query);

$query  = "\n DELETE FROM cate_after_price";
$query .= "\n WHERE cate_after_price_seqno = '%s';";

$tot = $rs->RecordCount();

$i = 1;
while ($rs && !$rs->EOF) {
    $seqno = $rs->fields["seqno"];

    $q_str = sprintf($query, $seqno);

    //echo $q_str;

    //$conn->Execute($q_str);

    echo $i++ . '/' . $tot . "\r";

    /*
    if (($i % 100) === 0) {
        sleep(1);
    }
    */

    $rs->MoveNext();
}
unset($rs);
?>
