<?
/*
 * Copyright (c) 2017 Nexmotion, Inc.
 * All rights reserved.
 *
 * 운영체제 변경시 운영체제에 속한 프로그램 검색 후
 * option html 생성해서 반환
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

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new OrderMngDAO();

$oper_sys = $fb->form("oper_sys");

$param = array();

$param["oper_sys"] = $oper_sys;
$pro_typ_info = $dao->selectProTypInfo($conn, $param, "pro");
echo $pro_typ_info["html"];

$conn->Close();
?>
