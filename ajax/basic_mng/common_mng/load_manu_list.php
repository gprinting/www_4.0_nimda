<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . '/com/nexmotion/job/nimda/common/BasicMngCommonDAO.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$basicDAO = new BasicMngCommonDAO();

//제조사 검색어
$search_str = $fb->form("search_str");
//제조사 판매품목
$pur_prdt = $fb->form("pur_prdt");

$param = Array();
$param["pur_prdt"] = $pur_prdt;
$param["search"] = $search_str;
$result = $basicDAO->selectPrdcManu($conn, $param);

$arr = [];
$arr["col"] = "extnl_etprs_seqno";
$arr["val"] = "manu_name";
$arr["type"] = "manu";

$buff = makeSearchList($result, $arr);

echo $buff;
$conn->close();
?>
