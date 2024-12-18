<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/calcul_mng/tab/SalesTabListDAO.inc");
include_once(INC_PATH . "/com/nexmotion/doc/nimda/calcul_mng/tab/SalesTabListDOC.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new SalesTabListDAO();

$seqno = $fb->form("seqno");
$total = $fb->form("total");

$param = array();
$param["seqno"] = $seqno;
$param["total"] = $total;

$html = getPublicDivPopup($param);
echo $html;
?>
