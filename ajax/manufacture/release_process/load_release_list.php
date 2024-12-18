<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/manufacture/storage_mng/StorageMngDAO.inc");
include_once(INC_PATH . '/com/nexmotion/common/util/nimda/pageLib.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new StorageMngDAO();

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

$param["state"] = "3120";
$rs = $dao->selectReleaseList($conn, $param);
$list1 = makeReleaseListHtml($rs, $param);


$param["state"] = "3330";
$rs = $dao->selectReleaseList($conn, $param);
$list2 = makeReleaseListHtml($rs, $param);

$param["state"] = "3380";
$rs = $dao->selectReleaseList($conn, $param);
$list3 = makeReleaseListHtml($rs, $param);
//$paging = mkDotAjaxPage($rsCount, $page, $scrnum, $list_num, "movePage");

echo $list1 . "♪" . $list2 . "♪" . $list3;
$conn->close();
?>
