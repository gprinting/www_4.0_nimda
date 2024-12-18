<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/calcul_mng/tab/SalesTabListDAO.inc");
include_once(INC_PATH . '/com/nexmotion/common/util/nimda/pageLib.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new SalesTabListDAO();

//한페이지에 출력할 게시물 갯수
$list_num = $fb->form("listSize"); 

//현재 페이지
$page = $fb->form("page");

//리스트 보여주는 갯수 설정
if (!$fb->form("listSize")) {
    $list_num = 30;
}

//블록 갯수
$scrnum = 5; 

// 페이지가 없으면 1 페이지
if (!$page) {
    $page = 1; 
}

$s_num = $list_num * ($page-1);

$sell_site = $fb->form("sell_site");
$member_dvs = $fb->form("member_dvs");
$dlvr_way = $fb->form("dlvr_way");
$year = $fb->form("year");
$mon = $fb->form("mon");
$public_dvs = $fb->form("public_dvs");
$member_seqno = $fb->form("member_seqno");

$param = array();
$param["s_num"] = $s_num;
$param["list_num"] = $list_num;
$param["sell_site"] = $sell_site;
$param["member_dvs"] = $member_dvs;
$param["dlvr_way"] = $dlvr_way;
$param["year"] = $year;
$param["mon"] = $mon;
$param["member_seqno"] = $member_seqno;
$param["tab_public"] = $tab_public;
$param["dvs"] = "SEQ";

if ($fb->form("search")) {
    $param["search"] = $fb->form("search");
    $param["search_dvs"] = $fb->form("search_dvs");
}

//마지막날짜구하기
$day = 1;
while(checkdate($mon, $day, $year)) {
    $day++;
}

$param["day"] = $day-1;
$rs = $dao->selectPublicCompleteList($conn, $param);
$list = makePublicCompleteListHtml($conn, $dao, $rs, $param);

$param["dvs"] = "COUNT";
$count_rs = $dao->selectPublicCompleteList($conn, $param);
$rsCount = $count_rs->fields["cnt"];

$param["dvs"] = "SUM";
$paging = mkDotAjaxPage($rsCount, $page, $scrnum, $list_num, "moveCompletePage");
$total_rs = $dao->selectPublicCompleteList($conn, $param);

$param["dvs"] = "SUM2";
$total_rs2 = $dao->selectPublicCompleteList($conn, $param);

$member_cnt = "0";
$pay_total = "0";
$card_total = "0";
$pay_total2 = "0";

//업체수가 있을때
if ($count_rs->fields["cnt"]) {
    $member_cnt = number_format($count_rs->fields["cnt"]);
}

//매출합계가 있을때
if ($total_rs->fields["pay_price"]) {
    $pay_total = $total_rs->fields["pay_price"];
}

if ($total_rs2->fields["pay_price"]) {
    $pay_total2 = $total_rs->fields["pay_price"] - $total_rs->fields["enuri"] - $total_rs->fields["adjust_sales"];
}
//조정합계가 있을때
if ($total_rs->fields["adjust_price"]) {
    $adjust_price = $total_rs->fields["adjust_price"];
}

$total = $pay_total - $adjust_price;

echo $list . "♪♭@"  . $paging . "♪♭@" . $member_cnt . "♪♭@" . 
     number_format($pay_total) . "♪♭@" .
    number_format($adjust_price) . "♪♭@" .
    number_format($total) . "♪♭@" .
    number_format($pay_total2);

$conn->close();
?>
