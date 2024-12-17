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
 * 2017/07/19 이청산 생성
 *=============================================================================
 */

define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/business/order_mng/OrderMngDAO.inc");
include_once(INC_PATH . "/common_lib/CommonUtil.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$conn->debug = 1;

$fb = new FormBean();
$dao = new OrderMngDAO();
$util = new CommonUtil();

$session = $fb->getSession();
$fb = $fb->getForm();

//체크박스 체크된 값
$memo_chk                   = $fb["memo_chk"];
//체크박스 체크된 갯수
$memo_ea                    = $fb["memo_ea"];
//영업 - 수금 구분값
$dvs                        = $fb["dvs"];
$dvs = substr($dvs, 4);

$conn->StartTrans();

for ($i = 0; $i < $memo_ea; $i++) { 
    //$memo_seq = substr($memo_chk[$i], 13, 1);
    $memo_seq_arr = explode('_', $memo_chk[$i]) ;
    $memo_seq = $memo_seq_arr[3];
    if ($dvs == 'business') {
        $rs = $dao->deleteCrmBusinessMemoBySeqno($conn, $memo_seq);
    } else if ($dvs == 'collect') {
        $rs = $dao->deleteCrmCollectMemoBySeqno($conn, $memo_seq);
    }
}

if (!$rs) {
    $rs = "CRM메모 삭제가 실패했습니다.";
    goto ERR;
}

goto END;

ERR:
    $conn->FailTrans();
    $conn->RollbackTrans();
    echo $rs;

END:
    $conn->CompleteTrans();
    $conn->Close();
?>
