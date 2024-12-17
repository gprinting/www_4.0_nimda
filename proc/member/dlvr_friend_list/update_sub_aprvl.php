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

//메인 업체 회원 seqno
$member_seqno = $fb->form("member_seqno");
//배송 서브 업체 일련번호
$sub_seqno = $fb->form("sub_seqno");

if ($type == "2") {

    $param = array();
    $param["member_seqno"] = $member_seqno;
    $param["dlvr_friend_main"] = "N";
    $result = $dlvrDAO->updateMainFriend($conn, $param);
    if (!$result) $check = 0;

}

$param = array();
$param["member_seqno"] = $member_seqno;
$result = $dlvrDAO->selectMemberMainSeqno($conn, $param);
$dlvr_friend_main_seqno = $result->fields["dlvr_friend_main_seqno"];

//승인 or 거절 여부
$type = $fb->form("type");

$param = array();
$param["table"] = "dlvr_friend_sub";
$param["col"]["state"] = $type;
$param["col"]["dlvr_friend_main_seqno"] = $dlvr_friend_main_seqno;
$param["prk"] = "dlvr_friend_sub_seqno";
$param["prkVal"] = $sub_seqno;

$result = $dlvrDAO->updateData($conn, $param);

if ($result) {
    
    echo "수정 하였습니다.";

} else {

    echo "수정에 실패하였습니다.";
}

$conn->CompleteTrans();
$conn->close();
?>
