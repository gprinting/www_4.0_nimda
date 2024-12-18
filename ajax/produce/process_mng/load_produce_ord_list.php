<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/produce/typset_mng/ProcessMngDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new ProcessMngDAO();

$date = $fb->form("date");;
//$date = "2016-09-29";

$param = array();
//$param["ord_dvs"] = $fb->form("ord_dvs");
$param["date"] = $date;

$html = makeProduceOrd($conn, $dao, $param);

echo $html;
$conn->close();
?>
