<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/member/member_mng/DlvrListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dlvrDAO = new DlvrListDAO();

$param = array();
//sort 할 컬럼명
$param["sort"] = $fb->form("sort_col");
//sort type (ex:DESC, ASC)
$param["sort_type"] = $fb->form("sort_type");

$result = $dlvrDAO->selectDlvrMainList($conn, $param);

$main_list = makeDlvrMainList($result);

echo $main_list;

$conn->close();
?>
