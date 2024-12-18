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
$year = $fb->form("year");
$mon = $fb->form("mon");
$public_state = $fb->form("public_state");

$param = array();
$param["s_num"] = $s_num;
$param["list_num"] = $list_num;
$param["sell_site"] = $sell_site;
$param["member_dvs"] = $member_dvs;
$param["year"] = $year;
$param["mon"] = $mon;
$param["dvs"] = "SEQ";

if ($fb->form("member_name")) {
    $param["member_name"] = $fb->form("member_name");
}

$rs = $dao->selectPublicUnissuedList($conn, $param);
$list = makePublicUnissuedListHtml($conn, $dao, $rs, $param);

$param["dvs"] = "COUNT";
$count_rs = $dao->selectPublicUnissuedList($conn, $param);
$rsCount = $count_rs->fields["cnt"];

$paging = mkDotAjaxPage($rsCount, $page, $scrnum, $list_num, "moveUnissuedPage");
$total_rs = $dao->selectUnissuedSumPrice($conn, $param);

$member_cnt = "0";
$sales_total = "0";
$unissued_total = "0";

//업체수가 있을때
if ($total_rs->fields["member_cnt"]) {

    $member_cnt = number_format($total_rs->fields["member_cnt"]);

}

//매출합계가 있을때
if ($total_rs->fields["sales_total"]) {
    
    $sales_total = $total_rs->fields["sales_total"];

}

//미발행합계가 있을때
if ($total_rs->fields["unissued_total"]) {
    
    $unissued_total = $total_rs->fields["unissued_total"];

}

echo $list . "♪♭@"  . $paging . "♪♭@" . $member_cnt . "건♪♭@" . 
     number_format($sales_total) . "원♪♭@" . number_format($unissued_total) . "원";
$conn->close();
?>
