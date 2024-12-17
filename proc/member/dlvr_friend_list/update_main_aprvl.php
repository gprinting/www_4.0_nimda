<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/member/member_mng/DlvrListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dlvrDAO = new DlvrListDAO();
$conn->StartTrans();
$check = 1;

//배송친구 메인 seqno
$seqno = $fb->form("seqno");
//승인 or 거절 여부
$type = $fb->form("type");
//회원 seqno
$member_seqno = $fb->form("member_seqno");

if ($type == "2") {

    $param = array();
    $param["table"] = "dlvr_friend_sub";
    $param["prk"] = "member_seqno";
    $param["prkVal"] = $member_seqno;
    $result = $dlvrDAO->deleteData($conn, $param);
    if (!$result) $check = 0;

    $param = array();
    $param["member_seqno"] = $member_seqno;
    $param["dlvr_friend_main"] = "Y";
    $result = $dlvrDAO->updateMainFriend($conn, $param);
    if (!$result) $check = 0;

}
$param = array();
$param["table"] = "dlvr_friend_main";
$param["col"]["state"] = $type;
$param["prk"] = "dlvr_friend_main_seqno";
$param["prkVal"] = $seqno;
$result = $dlvrDAO->updateData($conn, $param);
if (!$result) $check = 0;

if ($check == 1) {
    echo "수정 하였습니다.";
} else {
    echo "수정에 실패하였습니다.";
}

$conn->CompleteTrans();
$conn->close();
?>
