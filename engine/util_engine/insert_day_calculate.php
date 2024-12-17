#! /usr/local/bin/php -f
<?php
/**
 * Created by PhpStorm.
 * User: 조현식
 * Date: 2016-11-30
 * Time: 오후 2:57
 * Contents: 정산정보 통계를 내준다
 */

include_once(dirname(__FILE__) . '/ConnectionPool.php');
include_once(dirname(__FILE__) . '/CommonDAO.php');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$today = date('Y-m-d',strtotime("today"));
//$today = "2016-12-09";

$conn->debug = 1;
$query  = "\n   SELECT B.member_seqno, B.member_name, SUM(pay_price) AS pay_price, SUM(depo_price) AS depo_price ";
$query .= "\n   FROM member_pay_history AS A ";
$query .= "\n   LEFT JOIN member AS B on A.member_seqno = B.member_seqno ";
$query .= "\n   WHERE (A.dvs = '사용' OR A.dvs = '입금') AND A.deal_date LIKE ('" . $today . "%') ";
$query .= "\n   GROUP BY A.member_seqno ";

$rs = $conn->Execute($query);

while($rs && !$rs->EOF) {
    $param = array();
    $param['member_seqno'] = $rs->fields["member_seqno"];
    $param['pay_price'] = $rs->fields["pay_price"];
    $param['depo_price'] = $rs->fields["depo_price"];

    $query2  = "\n   INSERT INTO day_settle (";
    $query2 .= "\n   member_seqno, update_date, sales_price, depo_price) ";
    $query2 .= "\n   VALUES('%s', '%s', '%s', '%s') ";
    $query2 .= "\n   ON DUPLICATE KEY UPDATE sales_price = '%s', depo_price = '%s' ";

    $query2 = sprintf($query2
        ,$param['member_seqno']
        ,$today
        ,$param['pay_price']
        ,$param['depo_price']
        ,$param['pay_price']
        ,$param['depo_price']);

    $conn->Execute($query2);

    $rs->MoveNext();
}


$query   = "\n   SELECT member_seqno, SUM(price) AS adjust_price ";
$query  .= "\n   FROM adjust ";
$query  .= "\n   WHERE deal_date = '%s'";
$query  .= "\n   GROUP BY member_seqno ";

$query = sprintf($query
        ,$today);

$rs = $conn->Execute($query);

while($rs && !$rs->EOF) {
    $param = array();
    $param["member_seqno"] = $rs->fields["member_seqno"];
    $param["adjust_price"] = $rs->fields["adjust_price"];

    $query2  = "\n   INSERT INTO day_settle (";
    $query2 .= "\n   member_seqno, update_date, adjust_price) ";
    $query2 .= "\n   VALUES('%s', '%s', '%s') ";
    $query2 .= "\n   ON DUPLICATE KEY UPDATE adjust_price = '%s'";

    $query2 = sprintf($query2
            ,$param["member_seqno"]
            ,$today
            ,$param["adjust_price"]
            ,$param["adjust_price"]);

    $conn->Execute($query2);

    $rs->MoveNext();
}

?>




























