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
 * 2017/06/08 이청산 생성
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

//일련번호
$member_seqno = $fb["member_seqno"];

$param = array();
$param["member_seqno"] = $member_seqno;

//$conn->debug = 1;
$rs = $dao->checkExcptMember($conn, $param);
$fields = $rs->fields;

//json 부분
$json  = '{';
$json .=   "\"member_typ\"             : \"%s\""; // 상담유도
$json .= '}';

echo sprintf($json, $fields["member_typ"]);

$conn->Close();

?>
