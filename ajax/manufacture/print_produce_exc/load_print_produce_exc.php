<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/manufacture/print_mng/PrintProduceExcDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new PrintProduceExcDAO();

$date = $fb->form("date");

$from = $date . " 00:00:00";
$to = $date . " 23:59:59";

$param = array();
$param["from"] = $from;
$param["to"] = $to;
$param["extnl_etprs_seqno"] = $fb->form("extnl_etprs_seqno");

$rs = $dao->selectPrintProduceExc($conn, $param);
echo makePrintProduceExc($rs);
$conn->close();
?>
