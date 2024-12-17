<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/produce/produce_result/ProcessResultDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new ProcessResultDAO();
$check = 1;

$conn->StartTrans();

$param = array();
$param["table"] = "print_work_report";
$param["col"]["adjust_price"] = $fb->form("adjust_price");
$param["prk"] = "print_work_report_seqno";
$param["prkVal"] = $fb->form("seqno");

$rs = $dao->updateData($conn, $param);

if (!$rs) {
    $check = 0;
}

$conn->CompleteTrans();
$conn->Close();
echo $check;
?>
