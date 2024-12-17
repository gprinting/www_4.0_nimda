<?
/*
 * Copyright (c) 2017 Nexmotion, Inc.
 * All rights reserved.
 *
 * 회원 메모 검색
 *
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2017/08/25 이청산 생성
 *=============================================================================
 */
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/business/order_mng/OrderMngDAO.inc");
include_once(INC_PATH . "/common_lib/CommonUtil.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new OrderMngDAO();
$util = new CommonUtil();

$fb = $fb->getForm();

$order_num = $fb["order_num"];

$param = array();
$param["order_num"] = $order_num;

//$conn->debug = 1;

$rs     = $dao->selectOrderCustMemo($conn, $param);
$fields = $rs->fields;

$memo = $fields["cust_memo"];

if ($memo == "") {
    $memo = "등록된 메모가 없습니다.";
} else if ($rs->EOF) {
    $memo = "등록된 메모가 없습니다.";
}

$json  = '{';
$json .=  "\"memo_cont\" : \"%s\"";
$json .= '}';

FIN:
echo sprintf($json, $memo);
$conn->Close();

?>
