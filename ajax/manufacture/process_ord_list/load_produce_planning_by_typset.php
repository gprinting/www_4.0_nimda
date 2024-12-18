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
$param["dlvr_info"] = "택배";
$param["dvs"] = "전단";

$rs = $dao->selectProduceListByTypset($conn, $param);
$rs->fields["dlvr_add_info"] = "롯데택배";
$html2 = makeProduceListByTypsetHtml($rs, $param);

echo $html2;
$conn->close();
?>
