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

$opt_arr = getopt("s:m:t:");

if (count($opt_arr) < 3) {
    echo "Usage : ./update_member_prepay_price.php -s[member_seqno] -m[money] -t[type:m/c]\n";
    exit;
}

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$conn->debug = 1;

// type : m
if ($opt_arr['t'] == 'm') {

    $query  = "SELECT prepay_price_money";
    $query .= "      ,prepay_price_card ";
    $query .= "  FROM member";
    $query .= " WHERE member_seqno = " . $opt_arr['s'];
    
    $prepay_money = intval($conn->Execute($query)->fields["prepay_price_money"]);
    $prepay_card  = intval($conn->Execute($query)->fields["prepay_price_card"]);
    $exist_prepay = $prepay_money + $prepay_card;

    $prepay_money += intval($opt_arr["m"]);

    $new_prepay   = $prepay_money + $prepay_card;

    $query  = "UPDATE member";
    $query .= "   SET prepay_price_money = " . $prepay_money;
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
    $query .= "\n     ,'100'";
    $query .= "\n     ,%s";
    $query .= "\n     ,%s";
    $query .= "\n )";
    
    $query  = sprintf($query, $opt_arr["s"]
    			     ,$opt_arr["m"]
    			     ,$exist_prepay
    			     ,$new_prepay
    			     ,date('Y')
    			     ,date('m'));

// type : c
} else {

    $query  = "SELECT prepay_price_money";
    $query .= "      ,prepay_price_card ";
    $query .= "  FROM member";
    $query .= " WHERE member_seqno = " . $opt_arr['s'];
    
    $prepay_money = intval($conn->Execute($query)->fields["prepay_price_money"]);
    $prepay_card  = intval($conn->Execute($query)->fields["prepay_price_card"]);
    $exist_prepay = $prepay_money + $prepay_card;
    
    $prepay_card += intval($opt_arr["m"]);

    $new_prepay   = $prepay_money + $prepay_card;
    
    $query  = "UPDATE member";
    $query .= "   SET prepay_price_card = " . $prepay_card;
    $query .= " WHERE member_seqno = " . $opt_arr['s'];
    
    $conn->Execute($query);
    
    $query  = "\n INSERT INTO member_pay_history (";
    $query .= "\n      member_seqno";
    $query .= "\n     ,deal_date";
    $query .= "\n     ,dvs";
    $query .= "\n     ,card_depo_price";
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
    			     ,$opt_arr["m"]
    			     ,$exist_prepay
    			     ,$new_prepay
    			     ,date('Y')
    			     ,date('m'));

}

$conn->Execute($query);
