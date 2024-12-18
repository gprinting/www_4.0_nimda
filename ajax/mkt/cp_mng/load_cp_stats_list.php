<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/mkt/mkt_mng/CpMngDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$cpDAO = new CpMngDAO();

//회사 관리 일련번호
$cpn_seqno = $fb->form("cpn_seqno");
$year = $fb->form("year");
$mon = $fb->form("mon");

$param = array();
$param["cpn_seqno"] = $cpn_seqno;
$param["year"] = $year;
$param["mon"] = $mon;
//$conn->debug = 1;
//mon_cp_use_stats 조회
$result = $cpDAO->selectCpStatsList($conn, $param);
$list = makeCpStatsList($result, $conn, $cpDAO);

echo $list;

$conn->close();
?>
