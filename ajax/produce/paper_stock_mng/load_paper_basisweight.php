<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . '/com/nexmotion/job/nimda/produce/paper_stock_mng/PaperStockMngDAO.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new PaperStockMngDAO();

//기본검색정보 : 종이평량
$param = array();
$param["name"] = $fb->form("paperName");
$param["dvs"] = $fb->form("paperDvs");
$param["color"] = $fb->form("paperColor");
$paperRs = $dao->selectPaperBasisweight($conn, $param);
$opt_basisweight = "<option value=\"\">평량(전체)</option>";
while ($paperRs && !$paperRs->EOF) {
    $opt_basisweight .= "<option value=\"". $paperRs->fields["basisweight"] ."\">" . $paperRs->fields["basisweight"] . "</option>";
    $paperRs->moveNext();
}

echo $opt_basisweight;
$conn->close();
?>
