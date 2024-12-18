<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/manufacture/typset_mng/TypsetFormatDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new TypsetFormatDAO();

$el = $fb->form("el");
$seqno = $fb->form("seqno");

$param = array();
$param["table"] = "basic_produce_paper";
$param["col"] = "purp, paper_seqno, 
    extnl_etprs_seqno, grain";
$param["where"]["typset_format_seqno"] = $seqno;

$rs = $dao->selectData($conn, $param);

if ($rs->EOF == 1) {
    $process_yn = "N";
} else {
    $process_yn = "Y";
}

$param = array();
$param["table"] = "extnl_etprs";
$param["col"] = "pur_prdt";
$param["where"]["extnl_etprs_seqno"] = $rs->fields["extnl_etprs_seqno"];

$pur_rs = $dao->selectData($conn, $param);

$param = array();
$param["table"] = "extnl_etprs";
$param["col"] = "extnl_etprs_seqno, manu_name";
$param["where"]["pur_prdt"] = $pur_rs->fields["pur_prdt"];

$sel_rs = $dao->selectData($conn, $param);

$html = makeOptionHtml($sel_rs, "extnl_etprs_seqno", "manu_name", "업체(전체)");

echo $html . "♪" . $rs->fields["purp"] 
. "♪" . $rs->fields["paper_seqno"] 
. "♪" . $pur_rs->fields["pur_prdt"] 
. "♪" . $rs->fields["extnl_etprs_seqno"] 
. "♪" . $rs->fields["grain"]
. "♪" . $process_yn;

$conn->Close();
?>
