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
$state = $fb->form("state");

if($sell_site == "전체") $sell_site = "";

$param = array();
$param["s_num"] = $s_num;
$param["list_num"] = 200; 
$param["sell_site"] = $sell_site;
$param["member_dvs"] = $member_dvs;
$param["member_seqno"] = $member_seqno;
$param["dlvr_way"] = $dlvr_way;
$param["year"] = $year;
$param["mon"] = $mon;
$param["state"] = $state;
$param["dvs"] = "SEQ";

if ($fb->form("corp_name")) {
    $param["corp_name"] = $fb->form("corp_name");
}

$rs = $dao->selectCashreceiptList($conn, $param);
//$rs2 = $dao->selectCashreceiptList2($conn, $param);
$list = makeCashreceiptListHtml($conn, $dao, $rs, $param);

$param["dvs"] = "COUNT";
$count_rs = $dao->selectCashreceiptList($conn, $param);
$rsCount = $count_rs->fields["cnt"];

$param["dvs"] = "SUM";
$paging = mkDotAjaxPage($rsCount, $page, $scrnum, $list_num, "moveCashreceiptPage");
$total_rs = $dao->selectCashreceiptList($conn, $param);

$member_cnt = "0";
$pay_total = "0";
$card_total = "0";

//업체수가 있을때
if ($total_rs->fields["cnt"]) {

    $member_cnt = number_format($total_rs->fields["cnt"]);

}

//매출합계가 있을때
if ($total_rs->fields["pay_price"]) {

    $pay_total = $total_rs->fields["pay_price"];

}


//카드합계가 있을때
if ($total_rs->fields["card_pay_price"]) {

    $card_total = $total_rs->fields["card_pay_price"];

}

echo $list . "♪♭@"  . $paging . "♪♭@" . $rsCount . "♪♭@" .
     number_format($pay_total). "♪♭@" .
     number_format($card_total);
$conn->close();
?>
