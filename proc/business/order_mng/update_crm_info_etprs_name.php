<?
/*
 * Copyright (c) 2017 Nexmotion, Inc.
 * All rights reserved.
 *
 * CRM정보 영업탭 중복거래기업 수정
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

$fb = new FormBean();
$dao = new OrderMngDAO();
$util = new CommonUtil();

$conn->debug = 1;

$fb = $fb->getForm();
$check = 1;

$crm_biz_info_seqno          = $fb["crm_biz_info_seqno"];
$etprs_name_arr              = $fb["etprs_name"];

$param = array();
$param["crm_biz_info_seqno"] = $crm_biz_info_seqno;

$conn->StartTrans();

$ret = $dao->deleteCrmInfoEtprsName($conn, $param);

if (!$ret) {
    $check = 0;
    goto ERR;
}
$tot = count($etprs_name_arr);

for ($i = 0; $i < $tot; $i++) {
    $etprs_name = $etprs_name_arr[$i];

    if (empty($etprs_name)) {
        continue;
    }

    $param["etprs_name"] = $etprs_name;
    
    $rs = $dao->insertCrmInfoEtprsName($conn, $param);
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
