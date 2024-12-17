<?php
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/business/esti_mng/EstiListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new EstiListDAO();

$param = [];
$param["state"]      = $fb->session("state_arr")["견적대기"];
$param["esti_mng"]   = '';
$param["esti_seqno"] = $fb->form("esti_seqno");

$conn->StartTrans();

$ret = $dao->updateEstiState($conn, $param);

if (!$ret || $conn->HasFailedTrans()) {
    $conn->FailTrans();
    $conn->RollbackTrans();
    goto ERR;
}

$param["origin_price"] = '0';
$param["sale_rate"]    = '0';
$param["sale_price"]   = '0';
$param["esti_price"]   = '0';
$param["vat"]          = '0';
$param["order_price"]  = '0';
$param["memo"]         = '';

$ret = $dao->updateEstiPrice($conn, $param);

if (!$ret || $conn->HasFailedTrans()) {
    $conn->FailTrans();
    $conn->RollbackTrans();
    goto ERR;
}

$conn->CompleteTrans();
goto END;

ERR:
    $conn->Close();
    echo "-1";
    exit;
END:
    $conn->Close();
    echo "1";
    exit;
