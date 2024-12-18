<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/calcul_mng/tab/PurTabListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new PurTabListDAO();

$sell_site   = $fb->form("sell_site");
$manu_name = $fb->form("search_val");

if (!$sell_site) {
    $sell_site = $fb->session("sell_site");
}

$param = array();
$param["sell_site"]   = $sell_site;
$param["manu_name"] = $manu_name;

$rs = $dao->selectEtprsInfo($conn, $param);

echo makeSearchManuList($rs);
$conn->Close();
?>
