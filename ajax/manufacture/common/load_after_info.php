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
$param["after_seqno"] = $fb->form("seqno");

$rs = $dao->selectAfterInfoApply($conn, $param);

echo $rs->fields["after_name"] . "♪" . 
$rs->fields["manu_name"] . "♪" .
$rs->fields["extnl_brand_seqno"] . "♪" .
$rs->fields["depth1"] . "♪" .
$rs->fields["depth2"] . "♪" .
$rs->fields["depth3"] . "♪" .
$rs->fields["after_seqno"];

$conn->close();
?>
