<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . '/com/nexmotion/job/nimda/basic_mng/prdc_prdt_mng/OutputMngDAO.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$outputDAO = new OutputMngDAO();

//검색어
$search = $fb->form("search_str");
$manu_seqno  = $fb->form("manu_seqno");
$brand_seqno = $fb->form("brand_seqno");

$param = array();
$param["search"] = $search;
$param["etprs_seqno"] = $manu_seqno;
$param["brand_seqno"] = $brand_seqno;

$result = $outputDAO->selectPrdcOutputName($conn, $param);

$arr = [];
$arr["col"] = "name";
$arr["val"] = "name";
$arr["type"] = "name";

$buff = makeSearchList($result, $arr);

echo $buff;
$conn->close();
?>
