<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/calcul_mng/tab/PurTabListDAO.inc");
include_once(INC_PATH . '/com/nexmotion/common/util/nimda/pageLib.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();
$fb = new FormBean();
$dao = new PurTabListDAO();

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

$start_year = $fb->form("start_year");
$start_mon = $fb->form("start_mon");
$end_year = $fb->form("end_year");
$end_mon = $fb->form("end_mon");


$param = array();
$param["s_num"] = $s_num;
$param["list_num"] = $list_num;
$param["sell_site"] = $sell_site;
$param["start_year"] = $start_year;
$param["start_mon"] = $start_mon;
$param["end_year"] = $end_year;
$param["end_mon"] = $end_mon;
$param["dvs"] = "SEQ";

if ($fb->form("search")) {
    $param["pur_cpn"] = $fb->form("search");
}

$rs = $dao->selectPurTabList($conn, $param);
$list = makePurTabListHtml($conn, $dao, $rs, $param);

$param["dvs"] = "COUNT";
$count_rs = $dao->selectPurTabList($conn, $param);
$rsCount = $count_rs->fields["cnt"];

$paging = mkDotAjaxPage($rsCount, $page, $scrnum, $list_num, "movePage");
$total_rs = $dao->selectPurSumPrice($conn, $param);

$etprs_cnt = "0";
$pur_total = "0";
$vat_total = "0";
$supply_total = "0";
//업체수가 있을때
if ($total_rs->fields["etprs_cnt"]) {

    $etprs_cnt = number_format($total_rs->fields["etprs_cnt"]);

}

//매입합계가 있을때
if ($total_rs->fields["pur_total"]) {
    
    $pur_total = $total_rs->fields["pur_total"];

}

//매입합계가 있을때
if ($total_rs->fields["supply_price"]) {

    $supply_total = $total_rs->fields["supply_price"];

}

//매입합계가 있을때
if ($total_rs->fields["vat"]) {

    $vat_total = $total_rs->fields["vat"];

}

echo $list . "♪♭@"  . $paging . "♪♭@" . $etprs_cnt . "건♪♭@" . 
     number_format($pur_total) . "원♪♭@" .
    number_format($supply_total) . "원♪♭@" .
    number_format($vat_total) . "원♪♭@";
$conn->Close();
?>
