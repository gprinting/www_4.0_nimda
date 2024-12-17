<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/member/member_mng/QuiescenceListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$quiescenceListDAO = new QuiescenceListDAO();
$check = 1;

//회원 휴면처리
$param = array();
$param["seqno"] = $fb->form("seqno");

$rs = $quiescenceListDAO->updateMemberQuiescence($conn, $param);

if (!$rs) {
    goto ERR;
}

$seqno = explode(',', $fb->form("seqno"));

$i = 0;
foreach ($seqno as $key=>$value) {
    $conn->StartTrans();

    //회원탈퇴 입력
    $param = array();
    $param["table"] = "member_withdraw";
    $param["col"]["withdraw_code"] = $fb->form("withdraw_code");
    $param["col"]["withdraw_dvs"] = "탈퇴대기/휴면";
    $param["col"]["reason"] = "마지막 로그인 날짜가 1년이 넘게 되어 휴면처리 합니다.";
    $param["col"]["withdraw_date"] = date("Y-m-d H:i:s");
    $param["col"]["withdraw_code"] = "14";
    $param["col"]["member_seqno"] = $seqno[$i];

    $rs = $quiescenceListDAO->insertData($conn, $param);

    if ($conn->HasFailedTrans() === true || $rs === false) {
        goto ERR;
    }

    //회원 쿠폰 삭제
    $param = array();
    $param["table"] = "cp_issue";
    $param["prk"] = "member_seqno";
    $param["prkVal"] = $seqno[$i];

    $rs = $quiescenceListDAO->deleteData($conn, $param);

    if ($conn->HasFailedTrans() === true || $rs === false) {
        goto ERR;
    }

    //회원 포인트 내역 삭제
    $param = array();
    $param["table"] = "member_point_history";
    $param["prk"] = "member_seqno";
    $param["prkVal"] = $seqno[$i];

    $rs = $quiescenceListDAO->deleteData($conn, $param);

    if ($conn->HasFailedTrans() === true || $rs === false) {
        goto ERR;
    }

    //회원 포인트 요청 삭제
    $param = array();
    $param["table"] = "member_point_req";
    $param["prk"] = "member_seqno";
    $param["prkVal"] = $seqno[$i];

    $rs = $quiescenceListDAO->deleteData($conn, $param);

    if ($conn->HasFailedTrans() === true || $rs === false) {
        goto ERR;
    }

    $i++;

    $conn->CompleteTrans();
}

$conn->CompleteTrans();
$conn->Close();
echo $check;
exit;

ERR:
    $check = 0;
    $conn->FailTrans();
    $conn->RollbackTrans();
    $conn->Close();

    echo $check;
?>
