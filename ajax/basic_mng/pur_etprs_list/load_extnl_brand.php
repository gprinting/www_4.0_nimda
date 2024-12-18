<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/basic_mng/pur_etprs_mng/PurEtprsListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$purDAO = new PurEtprsListDAO();

//매입업체
$etprs_seqno = $fb->form("etprs_seqno");

//매입브랜드 가져오기
$param = array();
$param["extnl_etprs_seqno"] = $etprs_seqno;

$result = $purDAO->selectPurBrand($conn, $param);
$buff = makeOptSeqSetting($result, "name", "extnl_brand");

echo $buff;
$conn->close();
?>
