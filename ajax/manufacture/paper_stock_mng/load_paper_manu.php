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

$param = array();
$param["table"] = "paper";
$param["col"] = "DISTINCT(name)";
$param["where"]["extnl_brand_seqno"] = $extnl_brand_seqno;

//기본검색정보 : 종이종류
$paper_rs = $dao->selectData($conn, $param);
$paper_opt = "<option value=\"\">종이명(전체)</option>";
while ($paper_rs && !$paper_rs->EOF) {
    $paper_opt .= "<option value=\"". $paper_rs->fields["name"] ."\">" . $paper_rs->fields["name"] . "</option>";
    $paper_rs->moveNext();
}

echo $paper_opt;
$conn->close();
?>
