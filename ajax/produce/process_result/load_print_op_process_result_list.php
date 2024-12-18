<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . '/com/nexmotion/job/nimda/produce/produce_result/ProcessResultDAO.inc');
include_once(INC_PATH . '/com/nexmotion/common/util/nimda/pageLib.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new ProcessResultDAO();

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
    $from = $from_date . " " . $from_time;
}

if ($to_date) {
    $to_time = " " . $fb->form("time_to") + 1;
    $to =  $to_date . " " . $to_time;
}

$state = "";
if ($fb->form("state") == "대기") {
    $state = "= 710";
} else if ($fb->form("state") == "중") {
    $state = "= 720";
} else if ($fb->form("state") == "완료") {
    $state = "= 810";
}

$param = array();
$param["s_num"] = $s_num;
$param["list_num"] = $list_num;
$param["search_txt"] = $fb->form("search_txt");
$param["search_cnd"] = $fb->form("search_cnd");
$param["extnl_etprs_seqno"] = $fb->form("extnl_etprs_seqno");
$param["state"] = $state;
$param["from"] = $from;
$param["to"] = $to;
$param["dvs"] = "SEQ";

$rs = $dao->selectPrintOpProcessResultList($conn, $param);

$param["dvs"] = "COUNT";
$count_rs = $dao->selectPrintOpProcessResultList($conn, $param);
$rsCount = $count_rs->fields["cnt"];

$param["cnt"] = $rsCount;

$list = makePrintOpProcessResultListHtml($rs, $param);
$paging = mkDotAjaxPage($rsCount, $page, $scrnum, $list_num, "movePage", 'print');

echo $list . "♪" . $paging;
$conn->close();
?>
