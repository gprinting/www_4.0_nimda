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

$sell_site = $fb->form("sell_site");
$member_dvs = $fb->form("member_dvs");
$year = $fb->form("year");
$mon = $fb->form("mon");
$public_dvs = $fb->form("public_dvs");

$param = array();
$param["s_num"] = $s_num;
$param["list_num"] = $list_num;
$param["sell_site"] = $sell_site;
$param["member_dvs"] = $member_dvs;
$param["year"] = $year;
$param["mon"] = $mon;
$param["public_dvs"] = $public_dvs;
$param["dvs"] = "SEQ";

if ($fb->form("member_name")) {
    $param["member_name"] = $fb->form("member_name");
}

$rs = $dao->selectPublicStandByList(($conn, $param);
$list = makePublicStandByListHtml($conn, $dao, $rs, $param);

$param["dvs"] = "COUNT";
$count_rs = $dao->selectSalesTabList($conn, $param);
$rsCount = $count_rs->fields["cnt"];

$paging = mkDotAjaxPage($rsCount, $page, $scrnum, $list_num, "movePage");
$total_rs = $dao->selectTabSumPrice($conn, $param);

$member_cnt = "0건";
$sales_total = "0원";
$card_total = "0원";

//업체수가 있을때
if ($total_rs->fields["member_cnt"]) {

    $member_cnt = number_format($result->fields["member_cnt"]) . "건";

}

//매출합계가 있을때
if ($total_rs->fields["sales_total"]) {
    
    $sales_total = number_format($total_rs->fields["sales_total"]) . "원";

}

//카드합계가 있을때
if ($total_rs->fields["card_total"]) {
    
    $card_total = number_format($total_rs->fields["card_total"]) . "원";

}

echo $list . "♪♭@"  . $paging . "♪♭@" . $member_cnt . "♪♭@" . 
     $sales_total . "♪♭@" . $card_total;
$conn->close();
?>
