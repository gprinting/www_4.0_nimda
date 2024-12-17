<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/produce/typset_mng/ProcessMngDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new ProcessMngDAO();
$check = 1;

$seqno = $fb->form("seqno");
$state_arr = $fb->session("state_arr");

$conn->StartTrans();

$param = array();
$param["table"] = "print_op";
$param["col"]["state"] = $state_arr["인쇄취소"];
$param["prk"] = "print_op_seqno";
$param["prkVal"] = $seqno;

$rs = $dao->updateData($conn, $param);

if (!$rs) {
    $check = 0;
}

$param = array();
$param["table"] = "print_work_report";
$param["col"]["worker_memo"] = "작업이 취소되었습니다.";
$param["prk"] = "print_op_seqno";
$param["prkVal"] = $seqno;

$rs = $dao->updateData($conn, $param);

if (!$rs) {
    $check = 0;
}

$conn->CompleteTrans();
$conn->Close();
echo $check;
?>
