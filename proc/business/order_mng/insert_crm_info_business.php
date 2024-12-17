<?
/*
 * Copyright (c) 2017 Nexmotion, Inc.
 * All rights reserved.
 *
 * CRM정보 영업탭 정보 수정
 *
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2017/07/13 이청산 생성
 *=============================================================================
 */
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/business/order_mng/OrderMngDAO.inc");
include_once(INC_PATH . "/common_lib/CommonUtil.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

//$conn->debug = 1;

$fb = new FormBean();
$dao = new OrderMngDAO();
$util = new CommonUtil();
$ret = 1;

$session = $fb->getSession();
$fb = $fb->getForm();

//회원 일련번호
$member_seqno               = $fb["member_seqno"];
//회원명
$member_name                = $fb["member_name"];
//영업상담일자
$cs_date                    = $fb["cs_date"];
//상담목적 유형
$cs_indu                    = $fb["cs_indu"];
//상담약속일
$cs_promi_date              = $fb["cs_promi_date"];
//영업형식
$cs_type                    = $fb["cs_type"];
//관심분야
$interest_field             = $fb["interest_field"];
//관심상품
$interest_prdt              = $fb["interest_prdt"];
//예상매출
$expec_sales                = $fb["expec_sales"];
//관심아이템
$interest_item              = $fb["interest_item"];
//중복 거래여부
$plural_deal_yn             = $fb["plural_deal_yn"];
//영업상담내용
$cs_cont                    = $fb["cs_cont"];
//영업상담메모
$cs_memo                    = $fb["cs_memo"];
//상담담당자
$empl_name                  = $session["name"];

$param = array();
$param["member_seqno"]   = $member_seqno;
$param["member_name"]    = $member_name;
$param["cs_date"]        = $cs_date;
$param["cs_indu"]        = $cs_indu;
$param["cs_promi_date"]  = $cs_promi_date;
$param["cs_type"]        = $cs_type;
$param["interest_field"] = $interest_field;
$param["interest_prdt"]  = $interest_prdt;
$param["expec_sales"]    = $expec_sales;
$param["interest_item"]  = $interest_item;
$param["plural_deal_yn"] = $plural_deal_yn;
$param["cs_cont"]        = $cs_cont;
$param["cs_memo"]        = $cs_memo;
$param["empl_name"]      = $empl_name;

$conn->StartTrans();

$ret = $dao->insertCrmInfoBusiness($conn, $param);

//복수거래기업 정보 등록을 위한 일련번호
$crm_biz_info_seqno = $conn->Insert_ID();

if (!$ret) {
    $ret = "0. CRM정보 입력이 실패했습니다.";
    goto ERR;
}

$json  = '{';
$json .=   "\"crm_biz_info_seqno\"  : \"%s\"";
$json .= '}';

goto END;

ERR:
    $conn->FailTrans();
    $conn->RollbackTrans();
    echo $ret;

END:
    $conn->CompleteTrans();
    echo sprintf($json, $crm_biz_info_seqno);
    $conn->Close();
?>
