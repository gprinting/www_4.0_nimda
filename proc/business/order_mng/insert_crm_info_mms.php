<?
/*
 * Copyright (c) 2017 Nexmotion, Inc.
 * All rights reserved.
 *
 * CRM정보 문자보내기
 *
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2017/05/31 이청산 생성 
 * 2017/07/06 엄준현 수정(핸드폰 번호 공백시 에러처리)
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

//$conn->debug = 1;

//회원 일련번호
//$member_seqno = $fb["member_seqno"];
//제목
$subject      = $fb["subject"];
//수신자 전화번호
$cell_num     = $fb["cell_num"];
//발신자 전화번호
$callback     = "02-2260-9000";
//전송할 내용
$msg          = $fb["msg"];
//구분(CRM영업/수금)
$dvs          = $fb["dvs"];
//일련번호(CRM정보)
$seqno        = $fb["seqno"];

if (empty($cell_num)) {
    $ret = "수신자 핸드폰 번호가 없습니다.";
    goto ERR;
}

$param = array();
$param["subject"]  = $subject;
$param["phone"]    = $cell_num;
$param["callback"] = $callback;
$param["msg"]      = $msg;

$conn->StartTrans();

$ret = $dao->insertMms($conn, $param);

if (!$ret) {
    $ret = "문자 테이블 입력에 실패했습니다.";
    goto ERR;
}

if ($dvs) {
    $param["seqno"] = $seqno;
    if ($dvs == "crm_business") {
        $ret = $dao->updateCrmBusinessMmsCount($conn, $param);
    } else if ($dvs == "crm_collect") {
        $ret = $dao->updateCrmCollectMmsCount($conn, $param);
    }

    if (!$ret) {
        $ret = "문자 건수 갱신에 실패했습니다.";
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
