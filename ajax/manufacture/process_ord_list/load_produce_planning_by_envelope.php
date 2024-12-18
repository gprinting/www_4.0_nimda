<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/manufacture/typset_mng/ProcessOrdListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new ProcessOrdListDAO();

$param = array();
$param["print_etprs"] = $fb->form("print_etprs");
$param["date"] = $fb->form("date");
$param["after"] = $fb->form("after");

//$conn->debug = 1;
$rs = $dao->selectProduceListByEnvelope($conn, $param);
$html2 = makeProduceListByEnvelopeHtml($rs);

echo $html2;
$conn->close();
?>
