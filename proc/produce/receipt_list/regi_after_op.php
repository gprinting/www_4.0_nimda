<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/define/nimda/common_config.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/produce/receipt_mng/ReceiptListDAO.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/file/FileAttachDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$fileDAO = new FileAttachDAO();
$dao = new ReceiptListDAO();
$check = 1;

$order_common_seqno = $fb->form("order_common_seqno");

$conn->StartTrans();

$param = array();
$param["table"] = "after_op";
$param["col"] = "after_op_seqno";
$param["where"]["order_after_history_seqno"] = $fb->form("after_seqno");

$sel_rs = $dao->selectData($conn, $param);

$param = array();
$param["table"] = "after_op";
$param["col"]["op_name"] = $fb->form("title");
$param["col"]["seq"] = $fb->form("seq");
$param["col"]["after_name"] = $fb->form("after_name");
$param["col"]["depth1"] = $fb->form("depth1");
$param["col"]["depth2"] = $fb->form("depth2");
$param["col"]["depth3"] = $fb->form("depth3");
$param["col"]["amt"] = $fb->form("amt");
$param["col"]["amt_unit"] = $fb->form("amt_unit_dvs");
$param["col"]["memo"] = $fb->form("memo");
$param["col"]["op_typ"] = $fb->form("op_typ");
$param["col"]["op_typ_detail"] = $fb->form("op_typ_detail");
$param["col"]["state"] = OrderStatus::STATUS_PROC["후공정"]["준비"];
$param["col"]["basic_yn"] = "N";
$param["col"]["regi_date"] = date("Y-m-d H:i:s");
$param["col"]["order_common_seqno"] = $order_common_seqno;
$param["col"]["order_after_history_seqno"] = $fb->form("after_seqno");
$param["col"]["extnl_brand_seqno"] = $fb->form("extnl_brand_seqno");
$param["col"]["order_detail_dvs_num"] = $fb->form("order_detail_dvs_num");

$param["prk"] = "order_after_history_seqno";
$param["prkVal"] = $fb->form("after_seqno");

$rs = $dao->updateData($conn, $param);

$after_op_seqno = $sel_rs->fields["after_op_seqno"];   

if (!$rs) {
    $check = 0;
}

$conn->CompleteTrans();
$conn->Close();
echo $check;
?>
