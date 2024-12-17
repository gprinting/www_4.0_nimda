<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/calcul_mng/virt_ba_mng/VirtBaListDAO.inc");
$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$virtDAO = new VirtBaListDAO();
$conn->StartTrans();

echo $buff;

$conn->CompleteTrans();
$conn->close();
?>
