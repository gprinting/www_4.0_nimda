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
$param["is_using"] = $fb->form("is_using");
$param["dlvr_area"] = $fb->form("dlvr_area");
$param["method"] = $fb->form("method");
$param["cost_by_case"] = $fb->form("cost_by_case");
$param["direct_dlvr_info_seqno"] = $fb->form("direct_dlvr_info_seqno");

$result = $mtraDAO->updateDirectDlvrDetail($conn,$param);
echo "1";

$conn->close();
?>
