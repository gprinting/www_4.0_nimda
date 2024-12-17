<?
/*
 * Copyright (c) 2017 Nexmotion, Inc.
 * All rights reserved.
 *
 * 판매채널 변경시 부서정보 - 그 부서에 속한 직원 검색후
 * option html 생성해서 json으로 반환
 *
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2017/04/20 엄준현 생성
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

$cpn_admin_seqno = $fb->form("seqno");

$param = array();

// 팀
$param["cpn_admin_seqno"] = $cpn_admin_seqno;
$depar_info = $dao->selectReceiptDeparInfo($conn, $param);
$depar_html = $depar_info["html"];
// 직원
unset($param);
$param["depar_code"] = $depar_info["depar_code"];
$empl_html = $dao->selectEmplHtml($conn, $param);

$json = "{\"depar\" : \"%s\", \"empl\" : \"%s\"}";

echo sprintf($json, $util->convJsonStr($depar_html)
                  , $util->convJsonStr($empl_html));

$conn->Close();
?>
