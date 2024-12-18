<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . '/com/nexmotion/job/nimda/calcul_mng/settle/IncomeDataDAO.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$incomeDAO = new IncomeDataDAO();

//검색어
$search = $fb->form("search_str");
//판매채널
$sell_site = $fb->form("sell_site");

$param = array();
$param["sell_site"] = $sell_site;
$param["search"] = $search;

$result = $incomeDAO->selectOfficeNickList($conn, $param);

//리스트 셋팅
$param = array();
$param["opt"] = "office_nick";
$param["opt_val"] = "member_seqno";
$param["func"] = "nick";
$buff = makeSearchListSub($result, $param);

echo $buff;
$conn->close();
?>
