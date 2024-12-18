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
$param["direct_dlvr_info_seqno"] = $fb->form("direct_dlvr_info_seqno");

$result = $mtraDAO->selectDirectDlvrDetail($conn,$param);

$json = array();
$json["dlvr_area"] = $result->fields['dlvr_area'];
$json["dlvr_hour"] = $result->fields['dlvr_hour'];
$json["mng"] = $result->fields['mng'];
$json["car_name"] = $result->fields['car_name'];
$json["car_number"] = $result->fields['car_number'];
$json["is_using"] = $result->fields['is_using'];
$json["method"] = $result->fields['method'];
$json["cost_by_case"] = $result->fields['cost_by_case'];
$json["vehi_num"] = $result->fields['vehi_num'];
$json["insert_datetime"] = explode(" ",$result->fields['insert_datetime'])[0];

echo json_encode($json);

$conn->close();
?>
