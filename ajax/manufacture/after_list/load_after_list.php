<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/manufacture/after_mng/AfterListDAO.inc");
include_once(INC_PATH . '/com/nexmotion/common/util/nimda/pageLib.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new AfterListDAO();


$from_date = $fb->form("date_from");
$from_time = "";
$to_date = $fb->form("date_to");
$to_time = "";

if ($from_date) {
    $from_time = $fb->form("time_from");
    $from = $from_date . " 00:00:00";
}

if ($to_date) {
    $to_time = " " . $fb->form("time_to");
    $to =  $to_date . " 23:59:59";
}

$param = array();
$param["cate_sortcode"] = $fb->form("cate_sortcode");
$param["extnl_etprs_seqno"] = $fb->form("extnl_etprs_seqno");
$param["after"] = $fb->form("after");
$param["date_cnd"] = $fb->form("date_cnd");
$param["from"] = $from;
$param["to"] = $to;
$param["dvs"] = "SEQ";

$param["state"] = "2720";
$rs = $dao->selectAfterList($conn, $param);
$list1 = makeAfterListHtml($rs, $param);

$param["state"] = "2780";
$rs = $dao->selectAfterList($conn, $param);
$list2 = makeAfterListHtml($rs, $param);
//$paging = mkDotAjaxPage($rsCount, $page, $scrnum, $list_num, "movePage");

echo $list1 . "♪" . $list2;
$conn->close();
?>
