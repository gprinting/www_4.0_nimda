<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . '/com/nexmotion/job/nimda/produce/paper_stock_mng/PaperStockMngDAO.inc');
include_once(INC_PATH . '/com/nexmotion/common/util/nimda/pageLib.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new PaperStockMngDAO();

$param = array();
$param["seq"] = $fb->form("seq");
$rs = $dao->selectPaperStockMngDetailView($conn, $param);

$ret = array();
$ret["regi_date"] = $rs->fields["modi_date"];
$ret["manu"] = $rs->fields["manu"];
$ret["paper_name"] = $rs->fields["paper_name"];
$ret["paper_dvs"] = $rs->fields["paper_dvs"];
$ret["paper_color"] = $rs->fields["paper_color"];
$ret["paper_basisweight"] = $rs->fields["paper_basisweight"];
$ret["realstock_amt"] = $rs->fields["realstock_amt"];
$ret["adjust_reason"] = $rs->fields["adjust_reason"];

echo json_encode($ret);
$conn->close();
?>
