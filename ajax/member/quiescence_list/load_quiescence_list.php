<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/member/member_mng/QuiescenceListDAO.inc");
include_once(INC_PATH . '/com/nexmotion/common/util/nimda/pageLib.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new QuiescenceListDAO();

//한페이지에 출력할 게시물 갯수
$list_num = $fb->form("showPage"); 

//현재 페이지
$page = $fb->form("page");

//검색할 단어
$search_txt = $fb->form("searchTxt");

//정렬
$sorting = $fb->form("sorting");
$sorting_type = $fb->form("sorting_type");

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

$param = array();
$param["s_num"] = $s_num;
$param["list_num"] = $list_num;
$param["sell_site"] = $fb->form("sell_site");
$param["search_txt"] = $search_txt;
$param["search_cnd"] = $fb->form("search_cnd");
$param["from"] = $from;
$param["to"] = $to;
$param["sorting"] = $sorting;
$param["sorting_type"] = $sorting_type;

$rs = $dao->selectQuiescenceMemberInfo($conn, "SEQ", $param);
$list = makeQuiescenceListHtml($conn, $dao, $rs, $param);

$count_rs = $dao->selectQuiescenceMemberInfo($conn, "COUNT", $param);
$rsCount = $count_rs->fields["cnt"];

$paging = mkDotAjaxPage($rsCount, $page, $scrnum, $list_num, "movePage");

$total = "검색결과 ▶ 총 회원 " . $rsCount . "명.";

echo $list . "♪" . $paging . "♪" . $total;
$conn->close();
?>
