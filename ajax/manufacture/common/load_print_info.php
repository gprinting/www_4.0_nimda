<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/manufacture/typset_mng/TypsetListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new TypsetListDAO();

$param = array();
$param["print_seqno"] = $fb->form("seqno");

$rs = $dao->selectPrintInfoApply($conn, $param);

echo $rs->fields["print_name"] . "♪" . 
$rs->fields["manu_name"] . "♪" .
$rs->fields["affil"] . "♪" .
$rs->fields["wid_size"] . "♪" .
$rs->fields["vert_size"] . "♪" .
$rs->fields["extnl_brand_seqno"] . "♪" .
$rs->fields["print_seqno"];

$conn->close();
?>
