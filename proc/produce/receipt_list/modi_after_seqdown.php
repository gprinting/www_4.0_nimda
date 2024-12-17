<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/produce/receipt_mng/ReceiptListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new ReceiptListDAO();
$check = 1;

$order_detail_dvs_num = $fb->form("order_detail_dvs_num");
$seq = intval($fb->form("seq"));

$conn->StartTrans();

//순서 변경으로 인하여 바껴야 되는 컬럼 일련번호 구함
$param = array();
$param["table"] = "order_after_history";
$param["col"] = "order_after_history_seqno";
$param["where"]["order_detail_dvs_num"] = $order_detail_dvs_num;
$param["where"]["seq"] = $seq + 1;

$sel_rs = $dao->selectData($conn, $param);

$before_seqno = $sel_rs->fields["order_after_history_seqno"];

//순서변경 할 일련번호 구함
$param = array();
$param["table"] = "order_after_history";
$param["col"] = "order_after_history_seqno";
$param["where"]["order_detail_dvs_num"] = $order_detail_dvs_num;
$param["where"]["seq"] = $seq;

$sel_rs = $dao->selectData($conn, $param);

$after_seqno = $sel_rs->fields["order_after_history_seqno"];

//이전 seq(순서) 바꿈
$param = array();
$param["table"] = "order_after_history";
$param["col"]["seq"] = $seq;
$param["prk"] = "order_after_history_seqno";
$param["prkVal"] = $before_seqno;

$rs = $dao->updateData($conn, $param);

if (!$rs) {
    $check = 0;
}

//이벤트가 발생한 seq(순서) 바꿈
$param = array();
$param["table"] = "order_after_history";
$param["col"]["seq"] = $seq + 1;
$param["prk"] = "order_after_history_seqno";
$param["prkVal"] = $after_seqno;

$rs = $dao->updateData($conn, $param);

if (!$rs) {
    $check = 0;
}

$conn->CompleteTrans();
$conn->Close();
echo $check;
?>
