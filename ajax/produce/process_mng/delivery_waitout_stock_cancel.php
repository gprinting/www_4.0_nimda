<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/produce/typset_mng/ProcessMngDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new ProcessMngDAO();

$param = array();
$param["order_detail_num"] = "'" . $fb->form("order_detail_num") . "'";
$param["state"] = 3220;

$rs = $dao->updateOrderState($conn, $param);

if($rs != null) {
    echo "1";
} else {
    echo "0";
}

$conn->close();

?>