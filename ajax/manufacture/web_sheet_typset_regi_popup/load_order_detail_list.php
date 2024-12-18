<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/manufacture/typset_mng/TypsetListDAO.inc");
include_once(INC_PATH . '/com/nexmotion/common/util/nimda/pageLib.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new TypsetListDAO();

$typset_num = $fb->form("typset_num");
$list_num = $fb->form("showPage");
$page = $fb->form("page");
$scrnum = 5;

//리스트 보여주는 갯수 설정
if (!$fb->form("showPage")) {
    $list_num = 5;
}

// 페이지가 없으면 1 페이지
if (!$page) {
    $page = 1;
}

$s_num = $list_num * ($page-1);

$param = array();
$param["table"] = "sheet_typset";
$param["col"] = "sheet_typset_seqno";
$param["where"]["typset_num"] = $typset_num;

$sheet_typset_seqno = $dao->selectData($conn, $param)->fields["sheet_typset_seqno"];

$param = array();
$param["s_num"] = $s_num;
$param["list_num"] = $list_num;
$param["sheet_typset_seqno"] = $sheet_typset_seqno;
$param["dvs"] = "SEQ";

$rs = $dao->selectSheetTypsetOrderList($conn, $param);

$param["dvs"] = "COUNT";
$count_rs = $dao->selectSheetTypsetOrderList($conn, $param);
$rsCount = $count_rs->fields["cnt"];
$param["cnt"] = $rsCount;

$list = makeSheetTypsetOrderListHtml($rs, $param);
$paging = mkDotAjaxPage($rsCount, $page, $scrnum, $list_num, "movePage");

echo $list . "♪" . $paging;
$conn->close();
?>
