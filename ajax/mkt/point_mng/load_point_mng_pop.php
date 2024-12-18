<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/doc/nimda/mkt/mkt_mng/pintMngDOC.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();

$admin = $fb->getSession()["id"];
$custom = $fb->form("seqno");

$conn->StartTrans();

echo getPurEtprsRegi($admin, $custom );
?>
