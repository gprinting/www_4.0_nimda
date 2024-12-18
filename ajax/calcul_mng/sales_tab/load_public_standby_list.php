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
$param["s_num"] = $s_num;
$param["list_num"] = $list_num;
$param["sell_site"] = $sell_site;
$param["member_seqno"] = $member_seqno;
$param["dlvr_way"] = $dlvr_way;
$param["year"] = $year;
$param["mon"] = $mon;
$param["dvs"] = "SEQ";


$param2 = array();
$param2["s_num"] = 0;
$param2["list_num"] = 99999;
$param2["sell_site"] = $sell_site;
$param2["member_seqno"] = $member_seqno;
$param2["dlvr_way"] = $dlvr_way;
$param2["year"] = $year;
$param2["mon"] = $mon;
$param2["dvs"] = "SEQ";



if ($fb->form("corp_name")) {
    $param["corp_name"] = $fb->form("corp_name");
}

//마지막날짜구하기
$day = 1;
while(checkdate($mon, $day, $year)) {
    $day++;
}

$param["day"] = $day-1;
$rsa = $dao->selectPublicStandByList($conn, $param2);
//$test = $dao->testQueryString($conn,$param2);

while ($rsa && !$rsa->EOF) {
    $all_price1 = $rsa->fields["pay_price"] - $rsa->fields["adjust_sales"] - $rsa->fields["enuri"];
    // 20231129 세금계산서 수정 발급을 위한 체크 
  //  $param3["member_seqno"] = $rsa->fields["member_seqno"];
  //  $param3["year"] = $year;
  //  $param3["mon"] = $mon;
    
    if($rsa->fields['change_price'] != 0 ) {
        $all_price1 = $rsa->fields['change_price'];
    }   


    if($all_price1 < 0) $all_price1 = 0;   
    $all_price += $all_price1;
    $rsa->moveNext();
}

$param["day"] = $day-1;
$rs = $dao->selectPublicStandByList($conn, $param);

$list = makePublicStandByListHtml($conn, $dao, $rs, $param);
$param["dvs"] = "COUNT";
$count_rs = $dao->selectPublicStandByList($conn, $param);
$rsCount = $count_rs->RecordCount();

$paging = mkDotAjaxPage($rsCount, $page, $scrnum, $list_num, "moveStandbyPage");

$param["dvs"] = "SUM";
$total_rs = $dao->selectPublicStandByList($conn, $param);
$total_rs2 = $dao->selectAllPayprice2($conn, $param);
$member_cnt = "0";
$pay_total = "0";
$card_total = "0";


//업체수가 있을때
if ($count_rs->fields["cnt"]) {
    $member_cnt = number_format($count_rs->fields["cnt"]);
}

if ($total_rs->fields["pay_price"]) {
    $pay_price1 = $total_rs->fields["pay_price"];
    $adjust_sales1 = $total_rs->fields["adjust_sales"];
    $enuri = $total_rs->fields["enuri"];
}
$object_price = $pay_price1 - $adjust_sales1- $enuri;

//매출합계가 있을때
if ($total_rs->fields["pay_price"]) {
    $pay_total = $total_rs2->fields["pay_price"] + $total_rs2->fields["card_pay_price"] + $total_rs2->fields["adjust_price"];
}

//조정합계가 있을때
if ($total_rs->fields["adjust_sales"]) {
    $adjust_sales = $total_rs->fields["adjust_sales"];
}

$total = $pay_total - $adjust_sales;

/////
//조정합계가 있을때
if ($total_rs->fields["pay_price"]) {
    $pay_total2 = $total_rs2->fields["pay_price"];
}

//조정합계가 있을때
if ($total_rs->fields["adjust_price"]) {
    $adjust_price2 = $total_rs2->fields["adjust_price"];
}

$total2 = $pay_total2 - $adjust_price2;

echo $list . "♪♭@"  . $paging . "♪♭@" . $rsCount . "♪♭@" .
     number_format($pay_total) . "♪♭@" . 
     number_format($enuri) . "♪♭@" .
    number_format($total)  . "♪♭@" .
    //number_format($object_price) . "♪♭@".
    number_format($all_price) . "♪♭@" .
    number_format($test);


$conn->close();
?>
