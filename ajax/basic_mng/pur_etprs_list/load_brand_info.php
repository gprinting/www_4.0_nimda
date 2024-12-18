<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/common_define/common_info.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/basic_mng/pur_etprs_mng/PurEtprsListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new PurEtprsListDAO();

$seqno = $fb->form("seqno");

$param = array();
$param["table"] = "extnl_brand";
$param["col"] = "name";
$param["where"]["extnl_brand_seqno"] = $seqno;

$rs = $dao->selectData($conn, $param);

echo $rs->fields["name"];
$conn->close();
?>
