<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/common/NimdaCommonDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new NimdaCommonDAO();

//$sell_site   = $fb->form("sell_site");
//$office_nick = $fb->form("search_val");
$member_name = $fb->form("search_val");

/*
if (!$sell_site) {
    $sell_site = $fb->session("sell_site");
}
*/

$param = array();
//$param["sell_site"]   = $sell_site;
//$param["office_nick"] = $office_nick;
$param["member_name"] = $member_name;

//$conn->debug = 1;
$rs = $dao->selectOfficeID($conn, $param);

$arr = array();
$arr["opt"]  = "member_name";
$arr["id"]  = "id";
$arr["opt_val"]  = "member_seqno";
//$arr["view_name"]  = "full_name";
$arr["func"] = "name";

echo makeSearchListSub2($rs, $arr);
//echo makeSearchListConcat($rs, $arr);
$conn->Close();
?>
