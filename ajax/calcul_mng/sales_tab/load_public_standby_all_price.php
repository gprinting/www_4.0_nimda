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
$member_seqno = $fb->form("member_seqno");
$dlvr_way = $fb->form("dlvr_way");
$year = $fb->form("year");
$mon = $fb->form("mon");

if($sell_site == "전체") $sell_site = "";

$param = array();
$param["s_num"] = 0;
$param["list_num"] = 99999;
$param["sell_site"] = $sell_site;
$param["member_seqno"] = $member_seqno;
$param["dlvr_way"] = $dlvr_way;
$param["year"] = $year;
$param["mon"] = $mon;
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
$rs = $dao->selectPublicStandByList($conn, $param);

$all_pay_price = 0;
$all_enuri = 0;
$all_pure_pay_price = 0;

while ($rs && !$rs->EOF) {
    $param["member_seqno"] = $rs->fields["member_seqno"];
    $rs2 = $dao->selectAllPayprice2($conn, $param);
    $all_pay_price += $rs2->fields["pay_price"] + $rs2->fields["card_pay_price"] - $rs2->fields["adjust_sales"];
    $all_price1 = $rs->fields["pay_price"] - $rs->fields["adjust_sales"] - $rs->fields["enuri"];
    if($all_price1 < 0) $all_price1 = 0;
    $all_price += $all_price1;
    $adjust_sales2 += $rs->fields["adjust_sales"];
    
    
    $all_enuri += $rs2->fields["enuri"];
    $rs->moveNext();
}

$all_pure_pay_price = $all_pay_price - $all_enuri;


echo number_format($all_pay_price) . "원♪♭@"  . number_format($all_enuri) . "원♪♭@" . number_format($all_pure_pay_price). "원♪♭@".number_format($all_price). "원";



$conn->close();
?>
