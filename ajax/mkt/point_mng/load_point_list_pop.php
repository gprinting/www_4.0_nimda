<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/doc/nimda/mkt/mkt_mng/pintMngDOC.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/mkt/mkt_mng/PointMngDAO.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/common/FrontCommonDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new FrontCommonDAO();

$admin = $fb->getSession()["id"];
//$custom = $fb->form("seqno");
$pointDAO = new PointMngDAO();

$conn->StartTrans();

$param = array();
$param["s_num"] = $s_num;
$param["list_num"] = $list_num;
$param["search_dvs"] = $fb->form("search_dvs");
$param["keyword"] = $fb->form("keyword");
$param["version"] = $fb->form("version");
$param["date_from"] = $fb->form("date_from");
$param["date_to"] = $fb->form("date_to");
$param["member_seqno"] = $fb->form("seqno");

$rs = $dao->selectMemberConInfo($conn, "SEQ", $param["member_seqno"]);

echo makeOeventList($rs);

//월별 포인트 통계
/*$param = Array();
$param["table"] = "con_list";
$param["col"] = "*";
$param["where"]["con_custom"] = $custom;

$result = $pointDAO->selectData($conn, $param);

 */
?>
