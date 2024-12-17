<?
define("INC_PATH", $_SERVER["INC"]);
/*
 * Copyright (c) 2017 Nexmotion, Inc.
 * All rights reserved.
 *
 * CRM정보 영업탭 직원기념일  수정
 *
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2017/05/29 이청산 생성
 *=============================================================================
 */

define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/business/order_mng/OrderMngDAO.inc");
include_once(INC_PATH . "/common_lib/CommonUtil.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

//$conn->debug = 1;

$fb = new FormBean();
$dao = new OrderMngDAO();
$util = new CommonUtil();

$fb = $fb->getForm();
$check = 1;

$member_seqno               = $fb["member_seqno"];
$cont_arr                   = $fb["cont"];
$empl_anniv_arr             = $fb["empl_anniv"];

$param = array();
$param["member_seqno"]      = $member_seqno;

$conn->StartTrans();

$ret = $dao->deleteCrmInfoEmplAnniv($conn, $param);

if (!$ret) {
    $check = 0;
    goto ERR;
}
$tot = count($empl_anniv_arr);

for ($i = 0; $i < $tot; $i++) {
    if (empty($cont_arr[$i]) ||
            empty($empl_anniv_arr[$i])) {
        continue;
    }

    $param["cont"] = $cont_arr[$i];
    $param["empl_anniv"] = $empl_anniv_arr[$i];

    $rs = $dao->insertCrmInfoEmplAnniv($conn, $param);
}
if (!$rs) {
    $check = 2;
    goto ERR;
}

goto END;

ERR:
    $conn->FailTrans();
    $conn->RollbackTrans();
    echo $check;

END:
    $conn->CompleteTrans();
    $conn->Close();
?>
