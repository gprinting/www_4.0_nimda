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
$param["order_common_seqno"] = $fb->form("order_common_seqno");

$rs = $dao->selectOrderDetailBrochureList($conn, $param);

$list = makeOrderDetailBrochureListHtml($rs, $param);

echo $list;
$conn->close();
?>
