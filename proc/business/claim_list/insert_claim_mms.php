<?
/*
 * Copyright (c) 2017 Nexmotion, Inc.
 * All rights reserved.
 *
 * 클레임관리 문자보내기
 *
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2017/08/10 이청산 생성 
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
$subject      = "[디프린팅 입니다.]\n";
//수신자 전화번호
$cell_num     = $fb["cell_num"];
//발신자 전화번호
$callback     = "02-2260-9000";
//전송할 내용
$msg          = $fb["msg"];

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
    $ret = "입력에 실패했습니다.";
    goto ERR;
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
