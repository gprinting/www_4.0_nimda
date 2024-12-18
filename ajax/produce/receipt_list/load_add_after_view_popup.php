<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/doc/nimda/produce/receipt_mng/ReceiptListDOC.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/produce/receipt_mng/ReceiptListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new ReceiptListDAO();

//주문통합리스트 일련번호
$seqno = $fb->form("seqno");

//주문 후공정 내역 일련번호
$after_seqno = $fb->form("after_seqno");

//주문
$param = array();
$param["order_common_seqno"] = $seqno;

$order_rs = $dao->selectReceiptView($conn, $param);

//후공정 
$param = array();
$param["table"] = "order_after_history";
$param["col"] = "after_name, depth1, depth2, depth3, seq";
$param["where"]["order_after_history_seqno"] = $after_seqno;

$after_rs = $dao->selectData($conn, $param);

$after_name = "";
if ($after_rs->fields["after_name"]) {
    $after_name .= $after_rs->fields["after_name"];
}
if ($after_rs->fields["depth1"]) {
    $after_name .= "-". $after_rs->fields["depth1"];
}
if ($after_rs->fields["depth2"]) {
    $after_name .= "-". $after_rs->fields["depth2"];
}
if ($after_rs->fields["depth3"]) {
    if ($after_rs->fields["depth3"] == "-") {
        $after_name .= "";
    } else {
        $after_name .= "-". $after_rs->fields["depth3"];
    }
}

$title = $order_rs->fields["title"] . " ";
if ($after_rs->fields["after_name"]) {
    $title .= $after_rs->fields["after_name"];
}
if ($after_rs->fields["depth1"]) {
    $title .= " ". $after_rs->fields["depth1"];
}
if ($after_rs->fields["depth2"]) {
    $title .= " ". $after_rs->fields["depth2"];
}
if ($after_rs->fields["depth3"]) {
    $title .= " ". $after_rs->fields["depth3"];
}

$param = array();
$param["table"] = "after_op";
$param["col"] = "memo, op_typ, op_typ_detail, extnl_brand_seqno, after_op_seqno";
$param["where"]["order_after_history_seqno"] = $after_seqno;

$after_op_rs = $dao->selectData($conn, $param);

$param = array();
$param["table"] = "extnl_brand";
$param["col"] = "extnl_etprs_seqno";
$param["where"]["extnl_brand_seqno"] = $after_op_rs->fields["extnl_brand_seqno"];

$extnl_brand_rs = $dao->selectData($conn, $param);

$param = array();
$param["table"] = "extnl_etprs";
$param["col"] = "manu_name";
$param["where"]["extnl_etprs_seqno"] = $extnl_brand_rs->fields["extnl_etprs_seqno"];

$etprs_rs = $dao->selectData($conn, $param);

$param = array();
$param["table"] = "after_op_work_file";
$param["col"] = "after_op_work_file_seqno, origin_file_name";
$param["where"]["after_op_seqno"] = $after_op_rs->fields["after_op_seqno"];

$after_op_work_file_rs = $dao->selectData($conn, $param);

$param = array();
$param["title"] = $title;
$param["amt"] = $order_rs->fields["amt"];
$param["amt_unit_dvs"] = $order_rs->fields["amt_unit_dvs"];
$param["seq"] = $after_rs->fields["seq"];
$param["memo"] = $after_op_rs->fields["memo"];
$param["op_typ"] = $after_op_rs->fields["op_typ"];
$param["op_typ_detail"] = $after_op_rs->fields["op_typ_detail"];
$param["manu_name"] = $etprs_rs->fields["manu_name"];
$param["after_op_work_file_seqno"] = $after_op_work_file_rs->fields["after_op_work_file_seqno"];
$param["origin_file_name"] = $after_op_work_file_rs->fields["origin_file_name"];
$param["after_name"] = $after_name;
$param["order_common_seqno"] = $seqno;
$param["after_seqno"] = $after_seqno;
$param["state"] = $fb->form("state");

echo receiptAfterViewPopup($param);
$conn->close();
?>
