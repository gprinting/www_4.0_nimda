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
$param["ord_dvs"] = $fb->form("ord_dvs");
$param["date"] = $fb->form("date");

$html = makeProduceOrd($conn, $dao, $param);

$html2 = makeTotalList($conn, $dao, $param);

echo $html . $html2;
$conn->close();
?>
