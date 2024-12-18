<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/mkt/mkt_mng/MktAprvlMngDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$mktDAO = new MktAprvlMngDAO();

//검색조건(검색어)
$search = $fb->form("search_str");
//판매채널
$sell_site = $fb->form("sell_site");

$param = array();
$param["search"] = $search;
$param["sell_site"] = $sell_site;

$result = $mktDAO->selectOfficeNickList($conn, $param);

$arr = array();
$arr["opt"] = "office_nick";
$arr["opt_val"] = "member_seqno";
$arr["func"] = "name";

$buff = makeSearchListSub($result, $arr);
echo $buff;

$conn->close();
?>
