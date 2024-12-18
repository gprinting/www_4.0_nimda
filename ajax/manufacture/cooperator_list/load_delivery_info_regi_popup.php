<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/common_lib/CommonUtil.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/manufacture/cooperator_mng/CooperatorListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new CooperatorListDAO();
$util = new CommonUtil();

$order_detail_seqno = $fb->form("seqno");

$param = array();
$param["table"] = "order_detail";
$param["col"] = "order_common_seqno, state";
$param["where"]["order_detail_seqno"] = $order_detail_seqno;

$rs = $dao->selectData($conn, $param);
$order_common_seqno = $rs->fields["order_common_seqno"];
$state = $rs->fields["state"];

$param = array();
$param["table"] = "order_dlvr";
$param["col"] = "invo_cpn, invo_num";
$param["where"]["order_common_seqno"] = $order_common_seqno;

$rs = $dao->selectData($conn, $param);

$invo_cpn = $rs->fields["invo_cpn"];
$invo_num = $rs->fields["invo_num"];

$param = array();
$param["table"] = "order_dlvr";
$param["col"] = "name, tel_num, cell_num, zipcode, addr, addr_detail";
$param["where"]["order_common_seqno"] = $order_common_seqno;
$param["where"]["tsrs_dvs"] = "수신";

$sel_rs = $dao->selectData($conn, $param);

$param = array();
$param["order_detail_seqno"] = $order_detail_seqno;
$param["order_common_seqno"] = $order_common_seqno;
$param["invo_cpn"] = $invo_cpn;
$param["invo_num"] = $invo_num;
$param["name"] = $sel_rs->fields["name"];
$param["zipcode"] = $sel_rs->fields["zipcode"];
$param["addr"] = $sel_rs->fields["addr"];
$param["addr_detail"] = $sel_rs->fields["addr_detail"];
$param["tel_num"] = $sel_rs->fields["tel_num"];
$param["cell_num"] = $sel_rs->fields["cell_num"];

if ($state == $util->status2statusCode("배송중")) {
    $param["btn"] = "<button type=\"button\" class=\"btn btn-sm btn-success\" onclick=\"modiDeliveryinfo();\">수정</button>";
} else {
    $param["btn"] = "<button type=\"button\" class=\"btn btn-sm btn-success\" onclick=\"deliveryProcess();\">배송처리</button>";
}

echo getDeliveryInfoRegiPopup($param);
$conn->close();
?>
