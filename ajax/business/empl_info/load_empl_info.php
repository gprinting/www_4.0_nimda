<?
/*
 * Copyright (c) 2017 Nexmotion, Inc.
 * All rights reserved.
 *
 * 부서 상위 변경시 상위 부서에 속한 부서 검색 후
 * option html 생성해서 반환
 *
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2017/09/27 이청산 생성
 *=============================================================================
 */
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/business/empl_info/EmplInfoDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new EmplInfoDAO();

$depar_code = $fb->form("depar_code");

//$conn->debug=1;

$param = array();

$param["depar_code"] = $depar_code;
echo $dao->selectMidDeparHtml($conn, $param);

$conn->Close();
?>
