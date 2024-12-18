<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/business/claim_mng/ClaimListDAO.inc");
include_once(INC_PATH . "/com/nexmotion/doc/nimda/business/claim_mng/ClaimInfo.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new ClaimListDAO();

$seqno = $fb->form("seqno");

$param = array();
$param["dvs"] = "select";
$param["order_claim_seqno"] = $seqno;

$rs = $dao->selectClaimView($conn, $param);

$param = array();
$param["table"] = "extnl_etprs";
$param["col"] = "extnl_etprs_seqno, cpn_name";

$extnl_etprs_rs = $dao->selectData($conn, $param);
$extnl_etprs_html = makeOptionHtml($extnl_etprs_rs, "extnl_etprs_seqno", "cpn_name", "회사(전체)");

$param = array();
$param["table"] = "order_claim_file";
$param["col"] = "origin_file_name";
$param["where"]["order_claim_seqno"] = $seqno;

$order_claim_file_rs = $dao->selectData($conn, $param);

if ($order_claim_file_rs->EOF == 1) {
    $order_claim_file = "";
} else {
    $order_claim_file .= $order_claim_file_rs->fields["origin_file_name"];     
}

$sample_origin_file_name = "";
if ($rs->fields["sample_origin_file_name"]) {
    $sample_origin_file_name  = "<a href=\"/common/claim_file_down.inc?seqno=" . $seqno . "\">";
    $sample_origin_file_name .= $rs->fields["sample_origin_file_name"] . "</a>";

} else {
    $sample_origin_file_name = "첨부파일 없음";
}

$param = array();
$param["order_claim_seqno"] = $seqno;
$param["order_info_html"] = $dao->selectOrderInfoNonePop($conn, $rs->fields["order_common_seqno"]);
$param["title"] = $rs->fields["title"];
$param["occur_price"] = number_format($rs->fields["occur_price"]);
$param["sample_origin_file_name"] = $sample_origin_file_name;
$param["cust_cont"] = $rs->fields["cust_cont"];
$param["empl_name"] = $rs->fields["empl_name"];
$param["dvs_detail"] = $rs->fields["dvs_detail"];
$param["mng_cont"] = $rs->fields["mng_cont"];
$param["refund_prepay"] = number_format($rs->fields["refund_prepay"]);
$param["refund_money"] = number_format($rs->fields["refund_money"]);
$param["cust_burden_price"] = number_format($rs->fields["cust_burden_price"]);
$param["outsource_burden_price"] = number_format($rs->fields["outsource_burden_price"]);
$param["count"] = $rs->fields["count"];
$param["extnl_etprs_html"] = $extnl_etprs_html;
$param["order_claim_file"] = $order_claim_file;

echo makeClaimContHtml($param) . "♪" . $rs->fields["dvs"]
     . "♪" . $rs->fields["extnl_etprs_seqno"]
     . "♪" . $rs->fields["agree_yn"]
     . "♪" . $rs->fields["order_yn"];
$conn->Close();
?>
