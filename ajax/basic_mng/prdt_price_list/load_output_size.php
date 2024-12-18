<?
define("INC_PATH", $_SERVER["INC"]);
/*
 * Copyright (c) 2016 Nexmotion, Inc.
 * All rights reserved.
 * 
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2016/09/26 엄준현 추가
 *=============================================================================
 *
 */
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . '/com/nexmotion/job/nimda/basic_mng/prdt_price_mng/PrdtPriceListDAO.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new PrdtPriceListDAO();

$cate_sortcode = $fb->form("cate_sortcode");
$typ           = $fb->form("typ");

if (empty($typ)) {
    echo '';
    exit;
}

$param = array();
$param["cate_sortcode"] = $cate_sortcode;
$param["typ"]           = $typ;

$size = $dao->selectCateSizeHtml($conn, $param);

echo $size;
$conn->close();
?>
