#! /usr/local/bin/php
<?php
/**
 * @file update_member_prepay.php
 *
 * @brief 회원 결제내역에 입금내역 추가하면서 선입금액 수정
 */
include_once(dirname(__FILE__) . '/ConnectionPool.php');
include_once(dirname(__FILE__) . '/CommonDAO.php');
include_once('/home/sitemgr/inc/define/nimda/order_mng_define.inc');

$opt_arr = getopt("s:m:");

if (count($opt_arr) < 2) {
    echo "Usage : ./update_member_prepay_price.php -s[member_seqno] -m[card_money]\n";
    exit;
}

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$conn->debug = 1;

$query  = "SELECT prepay_price_card";
$query .= "  FROM member";
$query .= " WHERE member_seqno = " . $opt_arr['s'];

$prepay = intval($conn->Execute($query)->fields["prepay_price_card"]);
$exist_prepay = $prepay;

$prepay += intval($opt_arr["m"]);

$query  = "UPDATE member";
$query .= "   SET prepay_price_card = " . $prepay;
$query .= " WHERE member_seqno = " . $opt_arr['s'];

$conn->Execute($query);

$query  = "\n INSERT INTO member_pay_history (";
$query .= "\n      member_seqno";
$query .= "\n     ,deal_date";
$query .= "\n     ,dvs";
$query .= "\n     ,depo_price";
$query .= "\n     ,exist_prepay";
$query .= "\n     ,prepay_bal";
$query .= "\n     ,state";
$query .= "\n     ,deal_num";
$query .= "\n     ,input_typ";
$query .= "\n     ,pay_year";
$query .= "\n     ,pay_mon";
$query .= "\n ) VALUES (";
$query .= "\n      %s";
$query .= "\n     ,now()";
$query .= "\n     ,'입금'";
$query .= "\n     ,%s";
$query .= "\n     ,%s";
$query .= "\n     ,%s";
$query .= "\n     ,'-'";
$query .= "\n     ,'-'";
$query .= "\n     ,'102'";
$query .= "\n     ,%s";
$query .= "\n     ,%s";
$query .= "\n )";

$query  = sprintf($query, $opt_arr["s"]
			, $opt_arr["m"]
			, $exist_prepay
			, $prepay
			, date('Y')
			, date('m')
		 );

$conn->Execute($query);
