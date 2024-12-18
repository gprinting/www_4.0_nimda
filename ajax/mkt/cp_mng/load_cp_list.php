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

$param = array();
$param["cpn_seqno"] = $cpn_seqno;

$result = $cpDAO->selectCpList($conn, $param);
$list = makeCpList($result);

echo $list;

$conn->close();
?>
