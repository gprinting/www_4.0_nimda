<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/mkt/mkt_mng/EventMngDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$eventDAO = new EventMngDAO();

//요즘바빠요 리스트 그리기
$result = $eventDAO->selectNowadaysList($conn);
$list = makeNowadaysList($result);

echo $list;

$conn->close();
?>
