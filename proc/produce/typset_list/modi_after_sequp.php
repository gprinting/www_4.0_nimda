<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/produce/typset_mng/TypsetListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new TypsetListDAO();
$check = 1;

$seqno = $fb->form("seqno");
$seq = intval($fb->form("seq"));

$conn->StartTrans();

//순서 변경으로 인하여 바껴야 되는 컬럼 일련번호 구함
$param = array();
$param["table"] = "after_op";
$param["col"] = "after_op_seqno";
$param["where"]["order_common_seqno"] = $seqno;
$param["where"]["seq"] = $seq - 1;

$sel_rs = $dao->selectData($conn, $param);

$before_seqno = $sel_rs->fields["after_op_seqno"];

//순서변경 할 일련번호 구함
$param = array();
$param["table"] = "after_op";
$param["col"] = "after_op_seqno";
$param["where"]["order_common_seqno"] = $seqno;
$param["where"]["seq"] = $seq;

$sel_rs = $dao->selectData($conn, $param);

$after_seqno = $sel_rs->fields["after_op_seqno"];

//이전 seq(순서) 바꿈
$param = array();
$param["table"] = "after_op";
$param["col"]["seq"] = $seq;
$param["prk"] = "after_op_seqno";
$param["prkVal"] = $before_seqno;

$rs = $dao->updateData($conn, $param);

if (!$rs) {
    $check = 0;
}

//이벤트가 발생한 seq(순서) 바꿈
$param = array();
$param["table"] = "after_op";
$param["col"]["seq"] = $seq - 1;
$param["prk"] = "after_op_seqno";
$param["prkVal"] = $after_seqno;

$rs = $dao->updateData($conn, $param);

if (!$rs) {
    $check = 0;
}

$conn->CompleteTrans();
$conn->Close();
echo $check;
?>
