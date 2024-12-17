<?
/*
 * Copyright (c) 2017 Nexmotion, Inc.
 * All rights reserved.
 *
 * CRM정보 수금탭 정보 입력 
 *
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2017/06/05 이청산 생성 
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

$session= $fb->getSession();
$fb = $fb->getForm();

//$conn->debug = 1;

//회원 일련번호
$member_seqno         = $fb["member_seqno"];
//회원명
$member_name          = $fb["member_name"];
//상담내용
$memo                 = $fb["memo"];
//여신한도
$loan_limit           = $fb["loan_limit"];
//한도소진금액
$loan_lack            = $fb["loan_lack"];
//결제종류
$loan_pay_promi_dvs   = $fb["loan_pay_promi_dvs"];
//결제 약속일
$loan_pay_promi_date  = $fb["loan_pay_promi_date"];
//결제 약속금액
$loan_pay_promi_price = $fb["loan_pay_promi_price"];
//처리여부
$handle_dvs           = $fb["handle_dvs"];
//처리일시
$handle_date          = $fb["handle_date"];

$param = array();
$param["member_seqno"]         = $member_seqno;
$param["member_name"]          = $member_name;
$param["memo"]                 = $memo;
$param["loan_limit"]           = $loan_limit;
$param["loan_lack"]            = $loan_lack;
$param["empl_name"]            = $session["name"];
$param["loan_pay_promi_dvs"]   = $loan_pay_promi_dvs;
$param["loan_pay_promi_date"]  = $loan_pay_promi_date;
$param["loan_pay_promi_price"] = $loan_pay_promi_price;
$param["handle_dvs"]           = $handle_dvs;
$param["handle_date"]          = $handle_date;

$conn->StartTrans();

$ret = $dao->insertCrmInfoCollectInfo($conn, $param);

if (!$ret) {
    $ret = "입력에 실패했습니다.";
    goto ERR;
}

// 초기 기획엔 여신한도  수정이 가능했으나 수정 못하도록 변경어 주석처리(17.07.21)
//$param["loan_collect_dvs"] = $loan_pay_promi_dvs;
//$rs = $dao->updateCrmInfoExcptMember($conn, $param);

/*if (!$rs) {
    $ret = "업데이트에 실패했습니다.";
    echo $rs;
}*/

goto END;

ERR:
    $conn->FailTrans();
    $conn->RollbackTrans();
    echo $ret;

END:
    $conn->CompleteTrans();
    $conn->Close();

?>
