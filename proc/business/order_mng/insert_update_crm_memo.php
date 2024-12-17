<?
define("INC_PATH", $_SERVER["INC"]);
/*
 * Copyright (c) 2017 Nexmotion, Inc.
 * All rights reserved.
 *
 * CRM정보 영업탭 메모 등록/수정
 *
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2017/07/18 이청산 생성
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

$session = $fb->getSession();
$fb = $fb->getForm();

//영업-수금 구분
$dvs                        = $fb["dvs"];
//등록-삭제구분
$funcDvs                    = $fb["funcDvs"];
//메모-일련번호
$memo_seqno                 = $fb["memo_seqno"];
//CRM_영업_테이블 일련번호
$crm_biz_info_seqno         = $fb["crm_biz_info_seqno"];
//CRM_수금_테이블 일련번호
$crm_collect_info_seqno     = $fb["crm_collect_info_seqno"];
//메모 일자
$memo_date                  = $fb["memo_date"];
//메모 내용
$memo_cont                  = $fb["memo_cont"];

$param = array();
$param["crm_biz_info_seqno"]     = $crm_biz_info_seqno;
$param["crm_collect_info_seqno"] = $crm_collect_info_seqno;
$param["memo_seqno"]             = $memo_seqno;
$param["memo_date"]              = $memo_date;
$param["memo_cont"]              = $memo_cont;

$conn->StartTrans();

if ($funcDvs == "insert") {
    if ($dvs == "business") {
        $ret = $dao->insertCrmMemoBusiness($conn, $param);
    } else if ($dvs == "collect") {
        $ret = $dao->insertCrmMemoCollect($conn, $param);
    }

    if (!$ret) {
        $ret = "CRM메모 입력이 실패했습니다.";
        goto ERR;
    } 
} else if ($funcDvs == "update") {
    if ($dvs == "business") {
        $ret = $dao->updateCrmMemoBusiness($conn, $param);
    } else if ($dvs == "collect") {
        $ret = $dao->updateCrmMemoCollect($conn, $param);
    }

    if (!$ret) {
        $ret = "CRM메모 입력이 실패했습니다.";
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
