<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/Template.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/business/order_mng/OrderHandRegiDAO.inc");
include_once(INC_PATH . '/com/nexmotion/common/util/nimda/pageLib.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new OrderHandRegiDAO();
$commonDAO = $dao;

//한페이지에 출력할 게시물 갯수
$list_num = $fb->form("listSize"); 

//리스트 보여주는 갯수 설정
if (!$fb->form("listSize")) {
    $list_num = 30;
}

//현재 페이지
$page = $fb->form("page");

// 페이지가 없으면 1 페이지
if (!$fb->form("page")) {
    $page = 1; 
}

//블록 갯수
$scrnum = 5; 
$s_num = $list_num * ($page-1);

$param = array();
$param["s_num"] = $s_num;
$param["list_num"] = $list_num;
$param["sell_site"] = $fb->form("sell_site");
$param["member_seqno"] = $fb->form("search_txt");
$param["dvs"] = "";

$rs = $dao->selectMemberListCond($conn, $param);

$param["dvs"] = "COUNT";
$count_rs = $dao->selectMemberListCond($conn, $param);
$rsCount = $count_rs->fields["cnt"];

$param["cnt"] = $rsCount;

$list = makeMemberListHtml($rs, $param);
$paging = mkDotAjaxPage($rsCount, $page, $scrnum, $list_num, "movePage");

echo $list . "♪" . $paging;
$conn->Close();
?>
