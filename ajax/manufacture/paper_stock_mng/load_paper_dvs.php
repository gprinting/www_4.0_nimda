<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/manufacture/item_mng/PaperStockMngDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new PaperStockMngDAO();

$param = array();
$param["table"] = "extnl_brand";
$param["col"] = "extnl_brand_seqno";
$param["where"]["extnl_etprs_seqno"] = $fb->form("manu");

$extnl_brand_seqno = $dao->selectData($conn, $param)->fields["extnl_brand_seqno"];

//기본검색정보 : 종이색상
$param = array();
$param["table"] = "paper";
$param["col"] = "DISTINCT(color)";
$param["where"]["extnl_brand_seqno"] = $extnl_brand_seqno;
$param["where"]["name"] = $fb->form("name");
$param["where"]["dvs"] = $fb->form("dvs");

$paperRs = $dao->selectData($conn, $param);
$opt_color = "<option value=\"\">색상(전체)</option>";
while ($paperRs && !$paperRs->EOF) {
    $opt_color .= "<option value=\"". $paperRs->fields["color"] ."\">" . $paperRs->fields["color"] . "</option>";
    $paperRs->moveNext();
}

//기본검색정보 : 종이평량
$param = array();
$param["table"] = "paper";
$param["col"] = "DISTINCT CONCAT(basisweight,basisweight_unit) AS basisweight";
$param["where"]["extnl_brand_seqno"] = $extnl_brand_seqno;
$param["where"]["name"] = $fb->form("name");
$param["where"]["dvs"] = $fb->form("dvs");

$paperRs = $dao->selectData($conn, $param);
$opt_basisweight = "<option value=\"\">평량(전체)</option>";
while ($paperRs && !$paperRs->EOF) {
    $opt_basisweight .= "<option value=\"". $paperRs->fields["basisweight"] ."\">" . $paperRs->fields["basisweight"] . "</option>";
    $paperRs->moveNext();
}

echo $opt_color . "♪" . $opt_basisweight;
$conn->close();
?>
