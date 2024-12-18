<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/member/member_mng/DlvrListDAO.inc");
$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dlvrDAO = new DlvrListDAO();

//주소 검색어
$search = $fb->form("search");
//메인 서브 구분
$type = $fb->form("type");

$param = array();
$param["search"] = $search;
$param["type"] = $type;

$result = $dlvrDAO->selectDlvrFriend($conn, $param);

echo makeDlvrFriend($result);
$conn->close();
?>
