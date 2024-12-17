<?
/*
 * Copyright (c) 2017 Nexmotion, Inc.
 * All rights reserved.
 *
 * CRM정보 영업탭 정보 검색 후
 * json 생성 후 반환
 *
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2017/05/23 엄준현 생성
 * 2017/05/29 이청산 수정
 * 2017/07/13 이청산 수정
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

//글 일련번호
$crm_biz_info_seqno = $fb["crm_biz_info_seqno"];

$param = array();
$param["crm_biz_info_seqno"] = $crm_biz_info_seqno;

//$conn->debug = 1;
//이청산 추가(2017.05.26)
$crm_info_business = $dao->selectCrmInfoBusiness($conn, $param);
//$crm_empl_anniv = $dao->selectCrmEmplAnniv($conn, $param);
$fields = $crm_info_business->fields;

//json 부분
$json  = '{';
$json .=   "\"cs_date\"             : \"%s\""; // 영업상담날짜
$json .=  ",\"cs_indu\"             : \"%s\""; // 상담유형
$json .=  ",\"cs_promi_date\"       : \"%s\""; // 상담일자
$json .=  ",\"cs_type\"             : \"%s\""; // 영업형식
$json .=  ",\"interest_field\"      : \"%s\""; // 관심분야
$json .=  ",\"interest_prdt\"       : \"%s\""; // 관심상품
$json .=  ",\"expec_sales\"         : \"%s\""; // 예상매출
$json .=  ",\"interest_item\"       : \"%s\""; // 관심아이템
$json .=  ",\"plural_deal_yn\"      : \"%s\""; // 복수거래 여부
$json .=  ",\"cs_cont\"             : \"%s\""; // 영업상담내용
$json .=  ",\"cs_memo\"             : \"%s\""; // 영업상담메모 
$json .=  ",\"empl_name\"           : \"%s\""; // 담당자 
$json .=  ",\"member_seqno\"        : \"%s\""; // 회원 일련번호 
$json .= '}';

/* 170529 직원 기념일은 따로 처리하도록 변경되어 주석처리 
$json_empl  = '[';
$temp = '';
while ($crm_empl_anniv && !$crm_empl_anniv->EOF) {
    $temp .= '"';
    $temp .= $crm_empl_anniv->fields["empl_anniv"];
    $temp .= '"';
    $temp .= ',';
    $crm_empl_anniv->MoveNext();
}
$json_empl .= ']';
*/
echo sprintf($json, $fields["cs_date"]
                  , $fields["cs_indu"]
                  , $fields["cs_promi_date"]
                  , $fields["cs_type"]
                  , $fields["interest_field"]
                  , $fields["interest_prdt"]
                  , $fields["expec_sales"]
                  , $fields["interest_item"]
                  , $fields["plural_deal_yn"]
                  , $fields["cs_cont"]
                  , $fields["cs_memo"]
                  , $fields["empl_name"]
                  , $fields["member_seqno"]);

$conn->Close();

?>
