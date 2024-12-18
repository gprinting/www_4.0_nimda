<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . '/com/nexmotion/job/nimda/produce/paper_stock_mng/PaperStockMngDAO.inc');
include_once(INC_PATH . '/com/nexmotion/job/nimda/produce/raw_materials_stock_mng/RawMaterialStockMngDAO.inc');
include_once(INC_PATH . '/com/nexmotion/common/util/nimda/pageLib.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new RawMaterialStockMngDAO();

$param = array();
$param["seq"] = $fb->form("seq");
$rs = $dao->selectMtraStockMngDetailView($conn, $param);

$ret = array();
$ret["regi_date"] = $rs->fields["modi_date"];
$ret["manu"] = $rs->fields["manu"];
$ret["name"] = $rs->fields["name"];
$ret["stock_amt"] = $rs->fields["stock_amt"];
$ret["realstock_amt"] = $rs->fields["realstock_amt"];
$ret["adjust_reason"] = $rs->fields["adjust_reason"];


echo json_encode($ret);
$conn->close();
?>
