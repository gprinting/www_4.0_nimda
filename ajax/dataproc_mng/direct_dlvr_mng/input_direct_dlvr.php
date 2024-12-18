<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/dataproc_mng/set/MtraDscrMngDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$mtraDAO = new MtraDscrMngDAO();
$param = array();
$param["mng"] = $fb->form("mng");
$param["vehi_num"] = $fb->form("vehi_num");
$param["car_number"] = $fb->form("car_number");
$param["dlvr_area"] = $fb->form("dlvr_area");
$conn->debug = 1;
$result = $mtraDAO->inputDirectDlvr($conn,$param);
echo "1";

$conn->close();
?>
