<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . '/com/nexmotion/job/nimda/basic_mng/prdt_mng/PrdtBasicRegiDAO.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$prdtBasicRegiDAO = new PrdtBasicRegiDAO();

$param = array();
$param["name"] = $fb->form("print_name");

//인쇄도수 인쇄 용도구분
$tmpt_purp_dvs_rs = $prdtBasicRegiDAO->selectPrintInfo($conn, "PURP", $param);
$tmpt_purp_dvs_html = makeOptionHtml($tmpt_purp_dvs_rs, "", "purp_dvs", "", "N");

echo $tmpt_purp_dvs_html;
$conn->close();
?>
