<?
/*
 * Copyright (c) 2017 Nexmotion, Inc.
 * All rights reserved.
 *
 * CRM정보 영업탭 정보 중 직원 기념일  검색 후
 * json 생성 후 반환
 *
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2017/05/30 이청산 생성 
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

//회원 일련번호
$member_seqno = $fb["member_seqno"];

$param = array();
$param["member_seqno"] = $member_seqno;

//$conn->debug = 1;
//이청산 추가(2017.05.26)
$rs = $dao->selectCrmEmplAnniv($conn, $param);

//json 부분
$inner_form  = '{';
$inner_form .=   "\"seqno\"      : \"%s\""; // 일련번호 고유값
$inner_form .=  ",\"cont\"       : \"%s\""; // 직원명
$inner_form .=  ",\"empl_anniv\" : \"%s\""; // 직원 기념일
$inner_form .= '}';
$inner_form .= ',';

$json = '[';
$temp = '';
while ($rs && !$rs->EOF) {
    $fields = $rs->fields;

    $temp .= sprintf($inner_form, $fields["crm_biz_info_empl_seqno"]
                                , $fields["cont"]
                                , $fields["empl_anniv"]);
    $rs->MoveNext();
}
$json .= substr($temp, 0, -1);
$json .= ']';

echo $json;

$conn->Close();

?>
