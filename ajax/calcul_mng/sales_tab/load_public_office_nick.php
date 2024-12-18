<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/calcul_mng/tab/SalesTabListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new SalesTabListDAO();

$sell_site   = $fb->form("sell_site");
$office_nick = $fb->form("search_val");

if (!$sell_site) {
    $sell_site = $fb->session("sell_site");
}

$param = array();
$param["sell_site"] = $sell_site;
$param["office_nick"] = $office_nick;

$rs = $dao->selectOfficeName($conn, $param);

$arr = array();
$arr["opt"] = "office_nick";
$arr["opt_val"] = "member_seqno";
$arr["func"] = "loadMemberSeq";

echo makeSearchListSub($rs, $arr);

$conn->close();
?>
