<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . '/com/nexmotion/common/util/nimda/pageLib.inc');
include_once(INC_PATH . '/com/nexmotion/job/nimda/produce/paper_stock_mng/PaperStockMngDAO.inc');
include_once(INC_PATH . "/com/nexmotion/doc/nimda/produce/paper_stock_mng/PaperStockMngDOC.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new PaperStockMngDAO();

$today = date("Y.m.d");

$param = array();
$param["today"] = $today;
$param["paper_name"] = $fb->form("paperName");
$param["paper_dvs"] = $fb->form("paperDvs");
$param["paper_color"] = $fb->form("paperColor");
$param["paper_basisweight"] = $fb->form("paperBasisweight");
$param["manu"] = $fb->form("manu");

$param["save"] = "<button type=\"button\" class=\"btn btn-sm btn-success\" onclick=\"regiPaperStockDetail();\">저장</button>";

$list = makeStockMngPop($param);

echo $list;
$conn->close();
?>
