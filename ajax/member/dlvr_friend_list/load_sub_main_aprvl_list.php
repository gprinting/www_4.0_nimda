<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/member/member_mng/DlvrListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dlvrDAO = new DlvrListDAO();

$main_seqno = $fb->form("main_member_seqno");
$param = array();
//sort 할 컬럼명
$param["sort"] = $fb->form("sort_col");
//sort type (ex:DESC, ASC)
$param["sort_type"] = $fb->form("sort_type");
$param["search_nick"] = $fb->form("search_nick");

$result = $dlvrDAO->selectDlvrMainList($conn, $param);

$main_list = makeDlvrMainSelectList($result, $main_seqno);

echo $main_list;

$conn->close();
?>
