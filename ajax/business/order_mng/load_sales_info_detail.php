<?php
/*
 * Copyright (c) 2017 Nexmotion, Inc.
 * All rights reserved.
 *
 * 매출정보 리스트 확장시 상세정보 html 생성
 *
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2017/06/27 이청산 생성
 *=============================================================================
 */
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/business/order_mng/OrderMngDAO.inc");
include_once(INC_PATH . '/common_define/prdt_default_info.inc');
include_once(INC_PATH . '/common_lib/CommonUtil.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new OrderMngDAO();

$fb = $fb->getForm();

$detail_num = $fb["detail_num"];

$param = array();
$param["detail_num"] = $detail_num;

//$conn->debug = 1;

$conn->Close();
exit;

?>
