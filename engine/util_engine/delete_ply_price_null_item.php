#! /usr/local/bin/php -f
<?
/**
 * @file delete_ply_price_null_item.php
 *
 * @brief 상품구성아이템 등록에서 항목 삭제하고 가격 테이블에서 안지워진 가격 삭제
 */
include_once(dirname(__FILE__) . '/ConnectionPool.php');
include_once(dirname(__FILE__) . '/CommonDAO.php');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

if (count($argv) < 2) {
    echo "Useage : ./delete_ply_price_null_item.php [new|exist] \n";
    exit;
}

$price_table = $argv[1];
//$item_table = "cate_paper";
//$item_table = "cate_print";
$item_table = "cate_stan";
//$price_mpcode_col = "cate_paper_mpcode";
//$price_mpcode_col = "cate_beforeside_print_mpcode";
$price_mpcode_col = "cate_stan_mpcode";

$query  = "\n SELECT a.price_seqno";
$query .= "\n FROM ply_price_gp_%s as a";
$query .= "\n LEFT OUTER JOIN %s as b";
$query .= "\n ON a.%s = b.mpcode";
$query .= "\n WHERE b.mpcode IS NULL";

$query  = sprintf($query, $price_table
                        , $item_table
                        , $price_mpcode_col);

$rs = $conn->Execute($query);

$seqno_arr = array();

while ($rs && !$rs->EOF) {
    $seqno_arr[] = $rs->fields["price_seqno"];

    $rs->MoveNext();
}
unset($rs);

$query  = "\n DELETE ";
$query .= "\n   FROM ply_price_gp_%s";
$query .= "\n  WHERE price_seqno = '%s'";

foreach ($seqno_arr as $seqno) {
    $q_str = sprintf($query, $price_table
                           , $seqno);

    echo $q_str;
    //$conn->Execute($q_str);
}
?>
