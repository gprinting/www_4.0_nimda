<?
/*
 * Copyright (c) 2017 Nexmotion, Inc.
 * All rights reserved.
 *
 * 에누리액 등록
 *
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2017/07/12 이청산 생성 
 * 2017/07/27 엄준현 수정 
 *=============================================================================
 */
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/business/order_mng/OrderMngDAO.inc");
include_once(INC_PATH . "/common_lib/CommonUtil.inc");
include_once(INC_PATH . "/define/nimda/order_mng_define.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new OrderMngDAO();
$util = new CommonUtil();

$fb = $fb->getForm();

//$conn->debug = 1;

//회원 일련번호
$member_seqno = $fb["member_seqno"];
//상세내용
$cont         = $fb["cont"];
//입금액
$depo_price   = $fb["depo_price"];

$param = array();
$param["member_seqno"] = $member_seqno;
// 기존 잔액을 불러옴
$pre_bal = $dao->selectPrevBal($conn, $param);

$param["input_typ"] = $util->selectInputType("DC");
$param["cont"]      = $cont;
$param["pay_price"] = '0';

$total = ((intval($pre_bal) + intval($depo_price)));
$param["depo_price"] = $depo_price;
$param["pre_bal"]    = $pre_bal;
$param["total"]      = $total;

$conn->StartTrans();

$ret = $dao->insertDiscountViewData($conn, $param);

if (!$ret) {
    $ret = "입력에 실패했습니다.";
    goto ERR;
}

//member 테이블 선입금 업데이트
$ret = $dao->updateSalesDepoToMember($conn, $param);

if (!$ret) {
    $ret = "업데이트에 실패했습니다.";
    goto ERR;
}

// day_sales_stats 집계 데이터 수정
unset($param);
$depo_price *= -1;
$param["member_seqno"] = $member_seqno;
$param["input_date"]   = date("Y-m-d");
$stats_rs = $dao->selectDaySalesStatsList($conn, $param)->fields;

$sale_price      = intval($stats_rs["sale_price"]) + $depo_price;
$net_sales_price = intval($stats_rs["net_sales_price"]) + $depo_price;

$param["sale_price"]      = $sale_price;
$param["net_sales_price"] = $net_sales_price;

$ret = $dao->modiSaleDaySalesStats($conn, $param);

if (!$ret) {
    $ret = "업데이트에 실패했습니다.";
    goto ERR;
}

goto END;

ERR:
    $conn->FailTrans();
    $conn->RollbackTrans();
    echo $ret;

END:
    $conn->CompleteTrans();
    $conn->Close();
?>
