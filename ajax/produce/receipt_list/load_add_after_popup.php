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

//주문상세구분번호
$order_detail_dvs_num = $fb->form("order_detail_dvs_num");

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

$after_name = $after_rs->fields["after_name"];
$depth1 = $after_rs->fields["depth1"];
$depth2 = $after_rs->fields["depth2"];
$depth3 = $after_rs->fields["depth3"];

$tot_after_name = "";
if ($after_name) {
    $tot_after_name .= $after_name;
}
if ($depth1) {
    $tot_after_name .= "-". $depth1;
}
if ($depth2) {
    $tot_after_name .= "-". $depth2;
}
if ($depth3) {
    if ($depth3 == "-") {
        $tot_after_name .= "";
    } else {
        $tot_after_name .= "-". $depth3;
    }
}

$title = $order_rs->fields["title"] . " ";
if ($after_name) {
    $title .= $after_name;
}
if ($depth1) {
    $title .= " ". $depth1;
} else {
    $depth1 = "-";
}
if ($depth2) {
    $title .= " ". $depth2;
} else {
    $depth2 = "-";
}
if ($depth3) {
    $title .= " ". $depth3;
} else {
    $depth3 = "-";
}

$param = array();
$param["table"] = "extnl_etprs";
$param["col"] = "manu_name, extnl_etprs_seqno";
$param["where"]["pur_prdt"] = "후공정";
$param["order"] = "manu_name";

$etprs_rs = $dao->selectData($conn, $param);
$etprs_html = makeOptionHtml($etprs_rs, "extnl_etprs_seqno", "manu_name", "", "N");

//추가 후공정 정보 기존에 등록 한 경우
$param = array();
$param["table"] = "after_op";
$param["col"] = "op_typ, op_typ_detail, extnl_brand_seqno, memo, after_op_seqno";
$param["where"]["order_after_history_seqno"] = $after_seqno;

$sel_rs = $dao->selectData($conn, $param);

//수주처 일련번호
$param = array();
$param["table"] = "extnl_brand";
$param["col"] = "extnl_etprs_seqno";
$param["where"]["extnl_brand_seqno"] = $sel_rs->fields["extnl_brand_seqno"];

$manu_rs = $dao->selectData($conn, $param);

$param = array();
$param["table"] = "after_op_work_file";
$param["col"] = "after_op_work_file_seqno, origin_file_name, size";
$param["where"]["after_op_seqno"] = $sel_rs->fields["after_op_seqno"];

$after_file_rs = $dao->selectData($conn, $param);

if ($manu_rs->fields["extnl_etprs_seqno"]) {

    //브랜드
    $param = array();
    $param["table"] = "extnl_brand";
    $param["col"] = "extnl_brand_seqno ,name";
    $param["where"]["extnl_etprs_seqno"] = $manu_rs->fields["extnl_etprs_seqno"];

    $rs = $dao->selectData($conn, $param);

    $option_html = "\n<option value=\"%s\">%s</option>";
    $brand_html = "";

    while ($rs && !$rs->EOF) {

        $brand_html .= sprintf($option_html
                , $rs->fields["extnl_brand_seqno"]
                , $rs->fields["name"]);

        $rs->moveNext();
    }
}

$param = array();
$param["title"] = $title;
$param["amt"] = $order_rs->fields["amt"];
$param["amt_unit_dvs"] = $order_rs->fields["amt_unit_dvs"];
$param["seq"] = $after_rs->fields["seq"];
$param["tot_after_name"] = $tot_after_name;
$param["after_name"] = $after_name;
$param["depth1"] = $depth1;
$param["depth2"] = $depth2;
$param["depth3"] = $depth3;
$param["order_common_seqno"] = $seqno;
$param["after_seqno"] = $after_seqno;
$param["brand_html"] = $brand_html;
$param["etprs_html"] = $etprs_html;
$param["order_detail_dvs_num"] = $order_detail_dvs_num;

//기존에 등록 되어 있을 경우
$param["memo"] = $sel_rs->fields["memo"];
$param["op_typ_detail"] = $sel_rs->fields["op_typ_detail"];
$param["after_op_work_file_seqno"] = $after_file_rs->fields["after_op_work_file_seqno"];
$param["origin_file_name"] = $after_file_rs->fields["origin_file_name"];
$param["size"] = $after_file_rs->fields["size"];

echo receiptAfterPopup($param) . "♪" . 
     $sel_rs->fields["op_typ"] . "♪" . 
     $manu_rs->fields["extnl_etprs_seqno"] . "♪" . 
     $sel_rs->fields["extnl_brand_seqno"];
$conn->close();
?>
