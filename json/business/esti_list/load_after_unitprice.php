<?php
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/business/esti_mng/EstiListDAO.inc");
include_once(INC_PATH . "/common_lib/CommonUtil.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new EstiListDAO();
$util = new CommonUtil();

$session = $fb->getSession();
$fb = $fb->getForm();

$mpcode_arr = $fb["mpcode_arr"];
$mpcode_str = $dao->arr2paramStr($conn, $mpcode_arr);

$param["mpcode"] = $mpcode_str;

$rs = $dao->selectCateAfterPrice($conn, $param);

$ret = [];
while ($rs && !$rs->EOF) {
    $fields = $rs->fields;
    $mpcode = $fields["mpcode"];
    $price  = $fields["sell_price"];

    $ret[$mpcode] = $price;

    $rs->MoveNext();
}

echo json_encode($ret);
$conn->Close();
