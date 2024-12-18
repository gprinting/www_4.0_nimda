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
$param["order_detail_num"] = explode('|', $fb->form("order_detail_num"));
$param["state"] = 3220;
/*
foreach($arr_order_num as $order_num) {
    $param["order_detail_num"] .= "'" . $order_num . "',";
}

$param["order_detail_num"] = substr($param["order_detail_num"], 0, -1);
*/

$rs = $dao->updateOrderState($conn, $param);

if($rs != null) {
    echo "1";
} else {
    echo "0";
}

$conn->close();

?>