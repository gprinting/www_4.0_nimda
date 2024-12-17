<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/member/member_mng/ReduceListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$reduceListDAO = new ReduceListDAO();

$check = 1;

$conn->StartTrans();

$param = array();
$param["member_seqno"] = $fb->form("seqno");

$rs = $reduceListDAO->updateMemberRestore($conn, $param);

if (!$rs) {
    $check = 0; 
} 

//회원 탈퇴 테이블 회원 삭제 -> 복원
$param = array();
$param["table"] = "member_withdraw";
$param["prk"] = "member_seqno";
$param["prkVal"] = explode(',', $fb->form("seqno"));

$rs = $reduceListDAO->deleteMultiData($conn, $param);

if (!$rs) {
    $check = 0;
}

echo $check;
$conn->CompleteTrans();
$conn->close();
?>
