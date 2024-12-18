<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/produce/typset_mng/ProcessMngDAO.inc");
include_once(INC_PATH . '/com/nexmotion/common/util/nimda/pageLib.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new ProcessMngDAO();

$param = array();
$param['product_sort'] = $fb->form("product_sort"); //현재 출고실에서는 상품을 전단류, 명함류로 분류해 배송함
$param['after_yn'] = $fb->form("after_yn");
$param['keyword'] = $fb->form("keyword");
$param['theday_yn'] = $fb->form("theday_yn");
$param['showPage'] = $fb->form("showPage");
$param['page'] = $fb->form("page");
$param['after_yn'] = $fb->form("after_yn");
$param["dlvr_way"] = $fb->form("dlvr_way");
$param["dvs"] = "SEQ";

//현재 페이지
$page = $fb->form("page");
$list_num = $fb->form("showPage");

//리스트 보여주는 갯수 설정
if (!$fb->form("showPage")) {
    $list_num = 30;
}

// 페이지가 없으면 1 페이지
if (!$page) {
    $page = 1;
}

$s_num = $list_num * ($page-1);

$scrnum = 5;

$param["s_num"] = $s_num;
$param["list_num"] = $list_num;

$from_date = $fb->form("date_from");
$to_date = $fb->form("date_to");

if ($from_date) {
    $from_time = $fb->form("time_from");
    $from = $from_date . " " . $from_time;
}

if ($to_date) {
    $to_time = " " . $fb->form("time_to") + 1;
    $to =  $to_date . " " . $to_time;
}

$param["from"] = $from;
$param["to"] = $to;
$param["search_cnd"] = $fb->form("search_cnd");
$rs = $dao->selectWaitOutList($conn, $param);
$rs->fields['order_regi_date'] = substr($rs->fields['order_regi_date'],0,10);
$rs->fields['receipt_regi_date'] = substr($rs->fields['receipt_regi_date'],0,10);

$param["dvs"] = "COUNT";
$count_rs = $dao->selectWaitOutList($conn, $param);
$rsCount = $count_rs->fields["cnt"];

$param["cnt"] = $rsCount;

$list = makeWaitOutListHtml($rs, $param);
$paging = mkDotAjaxPage($rsCount, $page, $scrnum, $list_num, "movePage");
echo $list . "♪" . $paging;
$conn->close();

?>
