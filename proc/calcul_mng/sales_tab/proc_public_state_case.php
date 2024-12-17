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

//선택계산서상태변경
$param = array();
$param["new_dvs"] = $fb->form("new_state");
$param["member_seqno"] = $fb->form("member_seqno");
$param["order_num"] = $fb->form("order_num");

$rs = $dao->updatePublicStateCase($conn, $param);


if (!$rs) {
    $check = 0;
}

$conn->CompleteTrans();
$conn->Close();
echo $check;
?>
