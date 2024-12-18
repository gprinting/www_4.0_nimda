<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . '/com/nexmotion/job/nimda/calcul_mng/cashbook/CashbookRegiDAO.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$cashbookDAO = new CashbookRegiDAO();

//검색어
$search = $fb->form("search_str");
//판매채널
$sell_site = $fb->form("sell_site");
//클릭시 실행되는 함수명
$func = $fb->form("func");

$param = array();
$param["sell_site"] = $sell_site;
$param["search"] = $search;

$result = $cashbookDAO->selectOfficeNickList($conn, $param);

$buff = makeSearchNickList($result, $func);

echo $buff;
$conn->close();
?>
