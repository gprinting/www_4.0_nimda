<?
/*
 * Copyright (c) 2017 Nexmotion, Inc.
 * All rights reserved.
 *
 * CRM정보 영업탭 정보 중 복수거래기업  검색 후
 * json 생성 후 반환
 *
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2017/05/30 이청산 생성 
 * 2017/07/19 이청산 수정
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

//CRM 영업 리스트 일련번호
$crm_biz_info_seqno = $fb["crm_biz_info_seqno"];

$param = array();
$param["crm_biz_info_seqno"] = $crm_biz_info_seqno;

//$conn->debug = 1;
//이청산 추가(2017.05.26)
$rs = $dao->selectCrmEtprsName($conn, $param);

//json 부분
$inner_form  = '{';
$inner_form .=  "\"crm_biz_etprs_seqno\" : \"%s\""; // 일련번호 고유값 
$inner_form .= ",\"etprs_name\"          : \"%s\""; // 복수기업명 
$inner_form .= '}';
$inner_form .= ',';

$json = '[';
$temp = '';
while ($rs && !$rs->EOF) {
    $fields = $rs->fields;
    
    $temp .= sprintf($inner_form, $fields["crm_biz_etprs_seqno"]
                                , $fields["etprs_name"]);
    $rs->MoveNext();
}
$json .= substr($temp, 0, -1);
$json .= ']';

echo $json;

$conn->Close();

?>
