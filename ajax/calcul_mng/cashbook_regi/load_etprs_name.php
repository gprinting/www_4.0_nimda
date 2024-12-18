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
//클릭시 실행되는 함수명
$func = $fb->form("func");

$param = array();
$param["table"] = "extnl_etprs";
$param["col"] = "manu_name, extnl_etprs_seqno";
$param["like"]["manu_name"] = $search;

$result = $cashbookDAO->selectData($conn, $param);

$buff = makeSearchEtprsList($result, $func);

echo $buff;
$conn->close();
?>
