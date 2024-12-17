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

//회원 배송지 삭제
$param = array();
$param["table"] = "member_dlvr";
$param["prk"] = "member_dlvr_seqno";
$param["prkVal"] = $fb->form("seqno");

$rs = $memberCommonListDAO->deleteData($conn, $param);

if (!$rs) {
    $check = 0;
}

echo $check;
$conn->CompleteTrans();
$conn->close();
?>
