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

$state = $fb->form("state");
$year = $fb->form("year");
$mon = $fb->form("mon");
$seqno = $fb->form("seqno");


//선택계산서상태변경
$param = array();
$param["new_state"] = $state;
$param["year"] = $year;
$param["mon"] = $mon;
//$param["public_state"] = $state;
$param["now_state"] = "대기";
//$param["member_seqno"] = $seqno;

$seqnos = explode(',', $seqno);
foreach($seqnos as $seqno1) {
    $param["member_seqno"] = $seqno1;
    $rs = $dao->updatePublicState($conn, $param);
}
//$rs = $dao->updatePublicState($conn, $param);

if (!$rs) {
    $check = 0;
}

$conn->CompleteTrans();
$conn->Close();
echo $check;
?>
