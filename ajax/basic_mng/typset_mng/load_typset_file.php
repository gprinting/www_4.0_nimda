<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . '/com/nexmotion/job/nimda/basic_mng/prdc_prdt_mng/TypsetMngDAO.inc');
include_once(INC_PATH . "/com/nexmotion/common/util/nimda/pageLib.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$typsetDAO = new TypsetMngDAO();

//검색어
$search = $fb->form("search_str");

$param = array();
$param["search"] = $search;

$result = $typsetDAO->selectTypsetFile($conn, $param);

$arr = [];
$arr["col"] = "origin_file_name";
$arr["val"] = "origin_file_name";
$arr["type"] = "file";

$buff = makeSearchList($result, $arr);

echo $buff;
$conn->close();
?>
