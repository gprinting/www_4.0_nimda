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

$now_state = $fb->form("now_state");

//현금영수증상태변경
$param = array();
$param["new_dvs"] = $fb->form("new_state");
$param["member_seqno"] = $fb->form("member_seqno");
$param["year"] = $fb->form("year");
$param["mon"] = $fb->form("month");
$param["state"] = $fb->form("new_state");
$param["order_num"] = $fb->form("order_num");
$param["detail_dvs"] = $fb->form("detail_dvs");
$param["kind"] = $fb->form("kind");

if($param["state"] == null) {
    $param["state"] = "완료";
}

$rs = $dao->updatePublicStateNewIssue($conn, $param);

if (!$rs) {
    $check = 0;
}

$conn->CompleteTrans();
$conn->Close();
echo $check;
?>
