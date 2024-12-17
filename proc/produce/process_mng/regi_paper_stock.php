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

//개발 필요

$conn->StartTrans();

//조판번호 가져옴
$param = array();
$param["table"] = "print_op";
$param["col"] = "typset_num";
$param["where"]["print_op_seqno"] = $seqno;

$rs = $dao->selectData($conn, $param);

$typset_num = $rs->fields["typset_num"];

//종이 정보 가져옴
$param = array();
$param["table"] = "paper_op";
$param["col"] = "typset_num";
$param["where"]["print_op_seqno"] = $seqno;

$rs = $dao->updateData($conn, $param);

if (!$rs) {
    $check = 0;
}

$param = array();
$param["table"] = "print_op";
$param["col"]["state"] = $state;
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
