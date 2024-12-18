<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/manufacture/item_mng/PaperStockMngDAO.inc");
include_once(INC_PATH . '/com/nexmotion/common/util/nimda/pageLib.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new PaperStockMngDAO();

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

$date = $fb->form("date");

$from = "";
$to = "";
if ($date) {
    $from = $date . " " . "00:00:00";
    $to =  $date . " " . "23:59:59";
}

$param = array();
$param["table"] = "extnl_etprs";
$param["col"] = "manu_name";
$param["where"]["extnl_etprs_seqno"] = $fb->form("manu");

$manu = $dao->selectData($conn, $param)->fields["manu_name"];

$param = array();
$param["s_num"] = $s_num;
$param["list_num"] = $list_num;
$param["from"] = $from;
$param["to"] = $to;
$param["dvs"] = "SEQ";
$param["manu"] = $manu;
$param["paper_name"] = $fb->form("name");
$param["paper_dvs"] = $fb->form("dvs");
$param["paper_color"] = $fb->form("color");
$param["paper_basisweight"] = $fb->form("basisweight");

$rs = $dao->selectPaperStockMngList($conn, $param);

$param["dvs"] = "COUNT";
$count_rs = $dao->selectPaperStockMngList($conn, $param);
$rsCount = $count_rs->fields["cnt"];

$param["cnt"] = $rsCount;

$list = makePaperStockMngListHtml($rs, $param);
$paging = mkDotAjaxPage($rsCount, $page, $scrnum, $list_num, "movePage");

echo $list . "♪" . $paging;
$conn->close();
?>
