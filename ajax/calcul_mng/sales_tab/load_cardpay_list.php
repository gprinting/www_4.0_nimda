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
$member_seqno = $fb->form("member_seqno");

if($sell_site == "전체") $sell_site = "";

$param = array();
$param["s_num"] = $s_num;
$param["list_num"] = $list_num;
$param["sell_site"] = $sell_site;
$param["member_dvs"] = $member_dvs;
$param["dlvr_way"] = $dlvr_way;
$param["year"] = $year;
$param["mon"] = $mon;
$param["member_seqno"] = $member_seqno;
$param["dvs"] = "SEQ";

if ($fb->form("corp_name")) {
    $param["corp_name"] = $fb->form("corp_name");
}

//마지막날짜구하기
$day = 1;
while(checkdate($mon, $day, $year)) {
    $day++;
}

$param["day"] = $day-1;
$rs = $dao->selectCardpayList($conn, $param);
$list = makeCardpayListHtml($conn, $dao, $rs, $param);

$param["dvs"] = "COUNT";
$count_rs = $dao->selectCardpayList($conn, $param);
$rsCount = $count_rs->fields["cnt"];

$paging = mkDotAjaxPage($rsCount, $page, $scrnum, $list_num, "moveCardpayPage");

$param["dvs"] = "SUM";
$param["kind"] = "card";
$total_rs = $dao->selectCardpayList($conn, $param);


$param["dvs"] = "SEQ";
$param["kind"] = null;
$param["s_num"] = 0;
$param["list_num"] = 999999;
$rs = $dao->selectCardpayList($conn, $param);

$object_price = 0;
$all_pay_price = 0;
$enuri = 0;
$pure_pay_price = 0;
$param["kind"] = "card or cash";
while ($rs && !$rs->EOF) {
    $object_price += $rs->fields["card_depo_price"] + $rs->fields["card_pay_price"];
    $param["member_seqno"] = $rs->fields["member_seqno"];
    $rs2 = $dao->selectAllPayprice2($conn, $param);
    $all_pay_price += $rs2->fields["pay_price"] + $rs2->fields["card_pay_price"] - $rs2->fields["adjust_sales"];
    $enuri += $rs2->fields["enuri"];
    $rs->moveNext();
}

$pure_pay_price = $all_pay_price - $enuri;

//$total_rs2 = $dao->selectAllPayprice2($conn, $param);


$member_cnt = "0";

//업체수가 있을때
if ($count_rs->fields["cnt"]) {
    $member_cnt = number_format($count_rs->fields["cnt"]);
}
echo $list . "♪♭@"  . $paging . "♪♭@" . $member_cnt . "♪♭@" .
    number_format($all_pay_price) . "♪♭@" .
    number_format($enuri) . "♪♭@" .
    number_format($pure_pay_price)  . "♪♭@" .
    number_format($object_price);



$conn->close();
?>
