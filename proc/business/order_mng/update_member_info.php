<?
/*
 * Copyright (c) 2017 Nexmotion, Inc.
 * All rights reserved.
 *
 * 회원정보 수정
 *
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2017/04/27 엄준현 생성
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

$member_seqno     = $fb["seqno"];
$office_nick      = $fb["office_nick"];
$loan_limit_price = $fb["loan_limit_price"];

$param = array();
$param["member_seqno"] = $member_seqno;

$loan_limit_price = $util->rmComma($loan_limit_price);

$conn->StartTrans();

if (!empty($office_nick)) {
    $param["office_nick"] = $office_nick;

    $ret = $dao->updateMember($conn, $param);
    if (!$ret) {
        $ret = "회원정보 수정이 실패했습니다.";
        goto ERR;
    }
}

if (!empty($loan_limit_price)) {
    $param["loan_limit_price"] = $loan_limit_price;

    $ret = $dao->updateExcptMember($conn, $param);
    if (!$ret) {
        $ret = "예외회원정보 수정이 실패했습니다.";
        goto ERR;
    }
}

goto END;

ERR:
    $conn->FailTrans();
    $conn->RollbackTrans();
    echo $ret;
END:
    $conn->CompleteTrans();
    $conn->Close();
?>
