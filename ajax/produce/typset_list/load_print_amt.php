<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/produce/typset_mng/TypsetListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new TypsetListDAO();

$param = array();
$param["table"] = "sheet_typset";
$param["col"] = "typset_num";
$param["where"]["sheet_typset_seqno"] = $fb->form("seqno");

$rs = $dao->selectData($conn, $param);

$typset_num = $rs->fields["typset_num"];

$param = array();
$param["typset_num"] = $typset_num;
$rs = $dao->selectPrintDirectionsView($conn, $param);

echo $rs->fields["amt"] . "♪" . 
     $rs->fields["amt_unit"];

$conn->close();
?>
