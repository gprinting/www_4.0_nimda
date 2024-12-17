<?
define("INC_PATH", $_SERVER["INC"]);
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once(INC_PATH . "/com/nexmotion/job/front/common/FrontCommonDAO.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . '/com/nexmotion/common/util/nimda/pageLib.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$dao = new FrontCommonDAO();
$fb = new FormBean();


//한페이지에 출력할 게시물 갯수
$list_num = $fb->form("showPage2"); 

// $conn->debug = 1;

//현재 페이지
$page = $fb->form("page2");

//리스트 보여주는 갯수 설정
if (!$fb->form("showPage2")) {
    $list_num = 30;
}

//블록 갯수
$scrnum = 5; 

// 페이지가 없으면 1 페이지
if (!$page) {
    $page = 1; 
}

$s_num = $list_num * ($page-1);
// 세션 불러온다.


$param = array();
$param["s_num"] = $s_num;
$param["list_num"] = $list_num;
$param["search_dvs"] = $fb->form("search_dvs");
$param["keyword"] = $fb->form("keyword");
$param["version"] = $fb->form("version");
$param["date_from"] = $fb->form("date_from");
$param["date_to"] = $fb->form("date_to");
$param["member_seqno"] = $fb->form("member_seqno");

$rs = $dao->selectPointUseList($conn, "SEQ", $param);
$count_rs = $dao->selectPointUseList($conn, "COUNT", $param);


$rsCount = $count_rs->fields["cnt"];


$list = makeUsePoint($conn, $dao, $rs, $param);
$rs2 = $dao->selectPointUseList2($conn, "SEQ", $param);
$points = usePointCheck($conn, $dao, $rs2, $param);
$paging = mkDotAjaxPage($rsCount, $page, $scrnum, $list_num, "movePage2");


echo $list . "♪" . $paging . "♪" .$points ; 

$conn->close();
?>
