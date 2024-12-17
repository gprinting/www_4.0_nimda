<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/business/claim_mng/ClaimListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new ClaimListDAO();
$check = 1;

$conn->StartTrans();
$claim_order_seqno = $fb->form("seqno");

$param = array();
$param["table"] = "order_claim"; 
$param["col"]["empl_seqno"] = $fb->session("empl_seqno");
$param["col"]["mng_cont"] = $fb->form("mng_cont");
$param["prk"] = "order_claim_seqno";
$param["prkVal"] = $claim_order_seqno;

$rs = $dao->updateData($conn, $param);

if (!$rs) {
    $check = 0;
}

$conn->CompleteTrans();
$conn->Close();
echo $check;
?>
