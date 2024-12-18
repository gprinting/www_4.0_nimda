<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . '/com/nexmotion/job/nimda/produce/paper_stock_mng/PaperStockMngDAO.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new PaperStockMngDAO();

//기본검색정보 : 종이색상
$param = array();
$param["name"] = $fb->form("paperName");
$param["dvs"] = $fb->form("paperDvs");
$paperRs = $dao->selectPaperColor($conn, $param);
$opt_color = "<option value=\"\">색상(전체)</option>";
while ($paperRs && !$paperRs->EOF) {
    $opt_color .= "<option value=\"". $paperRs->fields["color"] ."\">" . $paperRs->fields["color"] . "</option>";
    $paperRs->moveNext();
}


//기본검색정보 : 종이평량
$param = array();
$param["name"] = $fb->form("paperName");
$param["dvs"] = $fb->form("paperDvs");
$paperRs = $dao->selectPaperBasisweight($conn, $param);
$opt_basisweight = "<option value=\"\">평량(전체)</option>";
while ($paperRs && !$paperRs->EOF) {
    $opt_basisweight .= "<option value=\"". $paperRs->fields["basisweight"] ."\">" . $paperRs->fields["basisweight"] . "</option>";
    $paperRs->moveNext();
}

echo $opt_color . "♪" . $opt_basisweight;
$conn->close();
?>
