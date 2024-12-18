<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/basic_mng/pur_etprs_mng/PurEtprsListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new PurEtprsListDAO();

//매입업체 가져오기
$param = array();
$param["pur_prdt"] = $fb->form("val");

$rs = $dao->selectPurManu($conn, $param);
$html = makeOptionHtml($rs, "extnl_etprs_seqno", "manu_name", "업체(전체)");

echo $html;
$conn->close();
?>
