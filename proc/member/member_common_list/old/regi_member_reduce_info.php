<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/member/member_mng/MemberCommonListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$memberCommonListDAO = new MemberCommonListDAO();
$check = 1;

$conn->StartTrans();

//회원 탈퇴신청
$param = array();
$param["member_seqno"] = $fb->form("seqno");

$rs = $memberCommonListDAO->updateMemberWithdraw($conn, $param);

if (!$rs) {
    $check = 0; 
} 

//회원탈퇴 입력
$param = array();
$param["table"] = "member_withdraw";
$param["col"]["withdraw_code"] = $fb->form("withdraw_code");
$param["col"]["withdraw_dvs"] = "강제탈퇴";
$param["col"]["reason"] = $fb->form("reason");
$param["col"]["withdraw_date"] = date("Y-m-d H:i:s");
$param["col"]["member_seqno"] = $fb->form("seqno");

$rs = $memberCommonListDAO->insertData($conn, $param);

if (!$rs) {
    $check = 0; 
}

//회원 쿠폰 삭제
$param = array();
$param["table"] = "cp_issue";
$param["prk"] = "member_seqno";
$param["prkVal"] = $fb->form("seqno");

$rs = $memberCommonListDAO->deleteData($conn, $param);

if (!$rs) {
    $check = 0;
}

//회원 포인트 내역 삭제
$param = array();
$param["table"] = "member_point_history";
$param["prk"] = "member_seqno";
$param["prkVal"] = $fb->form("seqno");

$rs = $memberCommonListDAO->deleteData($conn, $param);

if (!$rs) {
    $check = 0;
}

//회원 포인트 요청 삭제
$param = array();
$param["table"] = "member_point_req";
$param["prk"] = "member_seqno";
$param["prkVal"] = $fb->form("seqno");

$rs = $memberCommonListDAO->deleteData($conn, $param);

if (!$rs) {
    $check = 0;
}

//회원에게 부여 된 가상 계좌 반환
$param = array();
$param["member_seqno"] = $fb->form("seqno");

$rs = $memberCommonListDAO->updateVirtBaAdmin($conn, $param);

if (!$rs) {
    $check = 0;
}

echo $check;
$conn->CompleteTrans();
$conn->close();
?>
