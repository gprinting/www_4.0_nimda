<?
define("INC_PATH", $_SERVER["INC"]);
/*
 * Copyright (c) 2017 Nexmotion, Inc.
 * All rights reserved.
 *
 * CRM정보 영업탭 정보 수정
 *
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2017/05/23 엄준현 생성
 * 2017/05/26 이청산 수정
 * 2017/07/17 이청산 수정
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
$crm_biz_info_seqno         = $fb["crm_biz_info_seqno"];
//영업상담일자
$cs_date                    = $fb["cs_date"];
//상담목적유형
$cs_indu                    = $fb["cs_indu"];
//상담약속일
$cs_promi_date              = $fb["cs_promi_date"];
//영업형식
$cs_indu                    = $fb["cs_indu"];
//관심분야
$interest_field             = $fb["interest_field"];
//관심상품
$interest_prdt              = $fb["interest_prdt"];
//예상매출
$expec_sales                = $fb["expec_sales"];
//관심아이템
$interest_item              = $fb["interest_item"];
//복수거래 여부
$plural_deal_yn             = $fb["plural_deal_yn"];
//영업 상담내용
$cs_cont                    = $fb["cs_cont"];
//영업 상담메모
$cs_memo                    = $fb["cs_memo"];

$param = array();
$param["crm_biz_info_seqno"] = $crm_biz_info_seqno;
$param["cs_date"]            = $cs_date;
$param["cs_indu"]            = $cs_indu;
$param["cs_promi_date"]      = $cs_promi_date;
$param["cs_type"]            = $cs_type;
$param["interest_field"]     = $interest_field;
$param["interest_prdt"]      = $interest_prdt;
$param["expec_sales"]        = $expec_sales;
$param["interest_item"]      = $interest_item;
$param["plural_deal_yn"]     = $plural_deal_yn;
$param["cs_cont"]            = $cs_cont;
$param["cs_memo"]            = $cs_memo;

$conn->StartTrans();

$ret = $dao->updateCrmInfoBusiness($conn, $param);

if (!$ret) {
    $ret = "CRM정보 영업정보 수정이 실패했습니다.";
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
