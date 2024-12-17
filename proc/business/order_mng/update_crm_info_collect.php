<?
define("INC_PATH", $_SERVER["INC"]);
/*
 * Copyright (c) 2017 Nexmotion, Inc.
 * All rights reserved.
 *
 * CRM정보 수금탭 정보 수정
 *
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2017/07/21 이청산 생성
 *=============================================================================
 */
$doc_root = INC_PATH;

include_once($doc_root . "/com/nexmotion/common/entity/FormBean.inc");
include_once($doc_root . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once($doc_root . "/com/nexmotion/job/nimda/business/order_mng/OrderMngDAO.inc");
include_once($doc_root . "/common_lib/CommonUtil.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

//$conn->debug = 1;

$fb = new FormBean();
$dao = new OrderMngDAO();
$util = new CommonUtil();

//세션명이 다를 시 수정할 수 없음
$session = $fb->getSession();
$fb = $fb->getForm();

//접속자
$now_name                   = $session["name"];
//상담담당자
$empl_name                  = $fb["empl_name"];

/* 타인 수정 방지
if ($now_name != $empl_name) {
    $ret = "다른 사람이 수정할 수 없습니다.";
    goto ERR;
}
*/

//회원 일련번호
$crm_collect_info_seqno     = $fb["crm_collect_info_seqno"];
//메모
$memo                       = $fb["memo"];
//결제종류
$loan_pay_promi_dvs         = $fb["loan_pay_promi_dvs"];
//결제약속일
$loan_pay_promi_date        = $fb["loan_pay_promi_date"];
//결제 약속금액
$loan_pay_promi_price       = $fb["loan_pay_promi_price"];

$param = array();
$param["crm_collect_info_seqno"] = $crm_collect_info_seqno;
$param["memo"]                   = $memo;
$param["loan_pay_promi_dvs"]     = $loan_pay_promi_dvs;
$param["loan_pay_promi_date"]    = $loan_pay_promi_date;
$param["loan_pay_promi_price"]   = $loan_pay_promi_price;

$conn->StartTrans();

$ret = $dao->updateCrmInfoCollect($conn, $param);

if (!$ret) {
    $ret = "CRM정보 수금정보 수정이 실패했습니다.";
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
