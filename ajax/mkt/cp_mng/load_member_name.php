<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/mkt/mkt_mng/CpMngDAO.inc");
$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$cpDAO = new CpMngDAO();

//검색어
$search = $fb->form("search_str");
//회사 관리 일련번호
$cpn_seqno = $fb->form("cpn_admin_seqno");

$param = array();
$param["search"] = $search;
$param["cpn_seqno"] = $cpn_seqno;

$result = $cpDAO->selectMemberNickList($conn, $param);

$arr = array();
$arr["opt"] = "office_nick";
$arr["opt_val"] = "member_seqno";
$arr["func"] = "nick";

$buff = makeSearchListSub($result, $arr);
echo $buff;

$conn->close();
?>
