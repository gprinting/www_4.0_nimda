<?
/*
 * Copyright (c) 2017 Nexmotion, Inc.
 * All rights reserved.
 *
 * 명세서 출력에서 조회 버튼 클릭시 거래명세 검색해서
 * tr html 생성해서 반환
 *
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2017/05/01 엄준현 생성
 *=============================================================================
 */
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/business/order_mng/OrderMngDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new OrderMngDAO();

$fb = $fb->getForm();

$from = $fb["from"];
$to   = $fb["to"];
$member_seqno = $fb["seqno"];

$param = array();
$param["member_seqno"] = $member_seqno;
$param["from"] = $from;
$param["to"]   = $to;
//$conn->debug = 1;

$rs = $dao->selectOrderCommon($conn, $param);

$tr_form  = "<tr>";
$tr_form .=     "<td>%s</td>";
$tr_form .=     "<td>%s</td>";
$tr_form .=     "<td>%s</td>";
$tr_form .=     "<td>%s</td>";
$tr_form .=     "<td>%s</td>";
$tr_form .=     "<td>%s</td>";
$tr_form .=     "<td>%s</td>";
$tr_form .=     "<td>%s</td>";
$tr_form .=     "<td>%s</td>";
$tr_form .= "</tr>";

$tr = '';
$i = 1;
while ($rs && !$rs->EOF) {
    $fields = $rs->fields;

    $sell_price = doubleval($fields["sell_price"]);
    $sum_sale   = doubleval($fields["grade_sale_price"]) +
                  doubleval($fields["member_sale_price"]);

    $tr .= sprintf($tr_form, $i++
                           , explode(' ', $fields["depo_finish_date"])[0]
                           , $fields["title"]
                           , $fields["order_detail"]
                           , number_format($fields["amt"])
                           , $fields["count"]
                           , number_format($sell_price)
                           , number_format($sum_sale)
                           , number_format($sell_price + $sum_sale)
                           );

    $rs->MoveNext();
}

if (empty($tr)) {
    $tr = "<tr><td colspan='9'>검색결과가 없습니다.</td></tr>";
}

echo $tr;
$conn->Close();
?>
