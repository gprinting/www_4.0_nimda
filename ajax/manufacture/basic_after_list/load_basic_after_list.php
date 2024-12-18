<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/manufacture/after_mng/BasicAfterListDAO.inc");
include_once(INC_PATH . '/com/nexmotion/common/util/nimda/pageLib.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new BasicAfterListDAO();

//한페이지에 출력할 게시물 갯수
$list_num = $fb->form("showPage");

//현재 페이지
$page = $fb->form("page");
$detail = str_replace("(판)","",$fb->form("after"));
//리스트 보여주는 갯수 설정
if (!$fb->form("showPage")) {
    $list_num = 30;
}

//블록 갯수
$scrnum = 5;

// 페이지가 없으면 1 페이지
if (!$page) {
    $page = 1;
}

$s_num = $list_num * ($page-1);

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
$param["s_num"] = $s_num;
$param["list_num"] = $list_num;
$param["preset_cate"] = $fb->form("preset_cate");
$param["typset_num"] = $fb->form("typset_num");
$param["state"] = $fb->form("state");
$param["extnl_etprs_seqno"] = $fb->form("extnl_etprs_seqno");
$param["date_cnd"] = $fb->form("date_cnd");
$param["from"] = $from;
$param["to"] = $to;
$param["dvs"] = "SEQ";
$param["detail"] = $detail;
$param["state"] = "2620";

$rs = $dao->selectBasicAfterList($conn, $param);
$param["mode"] = "before";
$list1 = makeBasicAfterListHtml($rs, $param);
$conn->debug = 0;

$param["state"] = "2680";
$param["mode"] = "after";
$rs = $dao->selectBasicAfterList($conn, $param);
$list2 = makeBasicAfterListHtml($rs, $param);
//$paging = mkDotAjaxPage($rsCount, $page, $scrnum, $list_num, "movePage");

echo $list1 . "♪" . $list2;
$conn->close();
?>
