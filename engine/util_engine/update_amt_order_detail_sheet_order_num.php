#! /usr/local/bin/php -f
<?php
/**
 * @file update_amt_order_detail_sheet_order_num.php
 *
 * @brief 수량 주문 상세 낱장에 주문정보 update
 */
include_once(dirname(__FILE__) . '/ConnectionPool.php');
include_once(dirname(__FILE__) . '/CommonDAO.php');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$query  = "\n select distinct a.amt_order_detail_sheet_seqno as seqno, d.order_detail_dvs_num as order_num from";
$query .= "\n amt_order_detail_sheet as a,";
$query .= "\n sheet_typset as b,";
$query .= "\n order_detail_count_file as c,";
$query .= "\n order_detail as d";
$query .= "\n where";
$query .= "\n a.sheet_typset_seqno = b.sheet_typset_seqno";
$query .= "\n and a.order_detail_count_file_seqno = c.order_detail_count_file_seqno";
$query .= "\n and c.order_detail_seqno = d.order_detail_seqno";

$rs = $conn->Execute($query);

$sort_arr = [];

while ($rs && !$rs->EOF) {
    $fields = $rs->fields;
    $sort_arr[$fields["seqno"]] = substr($fields["order_num"], 1, -2);

    $rs->MoveNext();
}

$query = "\nupdate amt_order_detail_sheet set order_num='%s' where amt_order_detail_sheet_seqno='%s'";

foreach ($sort_arr as $seqno => $order_num) {
    $q_str = sprintf($query, $order_num, $seqno);

    $conn->Execute($q_str);

    echo $q_str;
}
?>
