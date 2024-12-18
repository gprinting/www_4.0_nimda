<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . '/com/nexmotion/job/nimda/basic_mng/prdc_prdt_mng/PrintMngDAO.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$printDAO = new PrintMngDAO();

//검색어
$search = $fb->form("search_str");

$param = array();
$param["search"] = $search;

$result = $printDAO->selectPrdcPrintName($conn, $param);

$arr = [];
$arr["col"] = "name";
$arr["val"] = "name";
$arr["type"] = "name";

$buff = makeSearchList($result, $arr);

echo $buff;
$conn->close();
?>
