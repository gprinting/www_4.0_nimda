<?
/*
 * Copyright (c) 2017 Nexmotion, Inc.
 * All rights reserved.
 *
 * CRM정보 영업탭 정보 리스트 검색 후
 * json 생성 후 반환
 *
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2017/07/17 이청산 생성
 * 2017/07/21 이청산 수정
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

$crm_biz_seqno  = $fb["crm_biz_seqno"];
$crm_col_seqno  = $fb["crm_col_seqno"];
$mms_dvs        = $fb["mms_dvs"];

$param = array();
$param["crm_biz_seqno"]  = $crm_biz_seqno;
$param["crm_col_seqno"]  = $crm_col_seqno;

if ($mms_dvs == "crm_info_collect_mms") {
    $rs = $dao->selectCrmInfoCollectMemberInfo($conn, $param);
} else if ($mms_dvs == "crm_info_business_mms") {
    $rs = $dao->selectCrmInfoBusinessMemberInfo($conn, $param);
}

//$conn->debug = 1;

$fields = $rs->fields;

$json  = '{';
$json .=   "\"name\"             : \"%s\""; // 영업상담날짜
$json .=  ",\"cell\"             : \"%s\""; // 상담유형
$json .= '}';

echo sprintf($json, $fields["member_name"]
                  , $fields["cell_num"]);

$conn->Close();

?>
