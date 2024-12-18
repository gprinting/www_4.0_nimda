<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/produce/typset_mng/ProcessMngDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new ProcessMngDAO();

$el = $fb->form("el");

$param = array();
$param["table"] = "output";
$param["col"] = "basic_price";
$param["where"]["extnl_brand_seqno"] = $extnl_brand_seqno;
$param["where"]["name"] = $rs->fields["output_name"];
$param["where"]["board"] = $rs->fields["board"];

$rs = $dao->selectData($conn, $param);

$basic_price = $rs->fields["basic_price"];
$price = number_format(intVal($basic_price) * $amt);

$conn->close();
?>
