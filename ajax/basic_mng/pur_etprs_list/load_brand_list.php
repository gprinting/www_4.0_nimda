<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/common_define/common_info.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/basic_mng/pur_etprs_mng/PurEtprsListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$purDAO = new PurEtprsListDAO();

$param = array();
$param["table"] = "extnl_brand";
$param["col"] = "name, extnl_brand_seqno";
$param["where"]["extnl_etprs_seqno"] = $fb->form("etprs_seqno");

$result = $purDAO->selectData($conn, $param);

echo makeBrandList($result);
$conn->close();
?>
