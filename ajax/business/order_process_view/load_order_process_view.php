<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/business/order_mng/OrderProcessViewDAO.inc");
include_once(INC_PATH . '/com/nexmotion/common/util/nimda/pageLib.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new OrderProcessViewDAO();

//한페이지에 출력할 게시물 갯수
$list_num = $fb->form("showPage"); 

//현재 페이지
$page = $fb->form("page");

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
    $from = $from_date . " " . $from_time . ":00:00";
}

if ($to_date) {
    $to_time = " " . $fb->form("time_to");
    $to =  $to_date . " " . $to_time . ":59:59";
}

$cate_top = $fb->form("cate_top");
$cate_mid = $fb->form("cate_mid");
$cate_bot = $fb->form("cate_bot");

$cate_sortcode = "";

if ($cate_top) {
    $cate_sortcode = $cate_top;
}

if ($cate_mid) {
    $cate_sortcode = $cate_mid;
}

if ($cate_bot) {
    $cate_sortcode = $cate_bot;
}

$search_cnd = "";
if ($fb->form("search_cnd") == "member_name") {
    $search_cnd = "A.member_seqno";
} else if ($fb->form("search_cnd") == "title") {
    $search_cnd = "A.title";
} else if ($fb->form("search_cnd") == "order_num") {
    $search_cnd = "A.order_num";
} else if ($fb->form("search_cnd") == "office_nick") {
    $search_cnd = "A.member_seqno";
}

$param = array();
$param["s_num"] = $s_num;
$param["list_num"] = $list_num;
$param["cate_sortcode"] = $cate_sortcode;
$param["date_cnd"] = $fb->form("date_cnd");
$param["sell_site"] = $fb->form("sell_site");
$param["state"] = $fb->form("state");
$param["search_cnd"] = $search_cnd;
$param["search_txt"] = $fb->form("search_txt");
$param["from"] = $from;
$param["to"] = $to;
$param["dvs"] = "SEQ";

$rs = $dao->selectOrderProcessViewList($conn, $param);

$param["dvs"] = "COUNT";
$count_rs = $dao->selectOrderProcessViewList($conn, $param);
$rsCount = $count_rs->fields["cnt"];

$param["cnt"] = $rsCount;

$list = makeOrderProcessViewListHtml($rs, $param);
$paging = mkDotAjaxPage($rsCount, $page, $scrnum, $list_num, "movePage");

echo $list . "♪" . $paging;
$conn->close();
?>
