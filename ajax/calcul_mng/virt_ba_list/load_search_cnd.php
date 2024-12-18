<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/calcul_mng/virt_ba_mng/VirtBaListDAO.inc");
$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$virtDAO = new VirtBaListDAO();

//검색어
$search = $fb->form("search_str");
//판매채널
$sell_site = $fb->form("sell_site");
//검색 구분
$dvs = $fb->form("search_dvs");

$param = array();
$param["search"] = $search;
$param["cpn_admin_seqno"] = $sell_site;

$func = "editMember";
$result = $virtDAO->selectMemberSeqList($conn, $param);

$buff = makeSearchCndList($result, $func);

echo $buff;
$conn->close();
?>
