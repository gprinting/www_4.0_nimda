<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/calcul_mng/tab/SalesTabListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new SalesTabListDAO();
$check = 1;
$conn->StartTrans();

$member_seqno = $fb->form("member_seqno");
$year = $fb->form("year");
$mon = $fb->form("mon");
$public_state = $fb->form("public_state");

$param = array();
$param["new_state"] = $public_state;
$param["year"] = $year;
$param["mon"] = $mon;
//$param["public_state"] = $public_state;
$param["member_seqno"] = $member_seqno;

if($public_state == "대기") {
    $param["now_state"] = "완료";
}

if($public_state == "완료") {
    $param["now_state"] = "대기";
}
$rs = $dao->updatePublicState($conn, $param);

if (!$rs) {
    $check = 0;
}

$conn->CompleteTrans();
$conn->Close();
echo $check;
?>
