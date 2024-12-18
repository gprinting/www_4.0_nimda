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
$param["output_seqno"] = $fb->form("seqno");

$rs = $dao->selectOutputInfoApply($conn, $param);

echo $rs->fields["output_name"] . "♪" . 
$rs->fields["manu_name"] . "♪" .
$rs->fields["affil"] . "♪" .
$rs->fields["wid_size"] . "♪" .
$rs->fields["vert_size"] . "♪" .
$rs->fields["board"] . "♪" .
$rs->fields["extnl_brand_seqno"] . "♪" .
$rs->fields["output_seqno"];

$conn->close();
?>
