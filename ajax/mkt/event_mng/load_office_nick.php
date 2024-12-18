<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/mkt/mkt_mng/EventMngDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$eventDAO = new EventMngDAO();

//검색조건(검색어)
$search = $fb->form("search_str");
//판매채널
$sell_site = $fb->form("sell_site");

$param = array();
$param["search"] = $search;
$param["cpn_seqno"] = $sell_site;

$result = $eventDAO->selectOfficeNickList($conn, $param);

$arr = [];
$arr["col"] = "office_nick";
$arr["val"] = "office_nick";
$arr["type"] = "name";

$buff = makeSearchList($result, $arr);
echo $buff;

$conn->close();
?>
