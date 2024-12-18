<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/manufacture/storage_mng/StorageMngDAO.inc");
include_once(INC_PATH . "/com/nexmotion/html/nimda/manufacture/ManufactureMngHTML.inc");
include_once(INC_PATH . '/com/nexmotion/common/util/nimda/pageLib.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new StorageMngDAO();

$param = array();
$param['dlvr_dvs'] = $fb->form("category");
$param['after_yn'] = $fb->form("after_yn");
$param['member_name'] = $fb->form("member_name");
$param['title'] = $fb->form("title");
$param["dlvr_way"] = $fb->form("dlvr_way");
$param["theday_yn"] = $fb->form("theday_yn");
$param["dlvr_way_detail"] = $fb->form("dlvr_way_detail");
$param["state"] = $fb->form("state");
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
$param["from"] = $from_date;
$param["to"] = $to_date;

$rs = $dao->selectReleaseList($conn, $param);


$rs->fields['order_regi_date'] = substr($rs->fields['order_regi_date'],0,10);
$rs->fields['receipt_regi_date'] = substr($rs->fields['receipt_regi_date'],0,10);

//$param["dvs"] = "COUNT";
//$count_rs = $dao->selectReleaseList($conn, $param);
//$rsCount = $count_rs->fields["cnt"];

//$param["cnt"] = $rsCount;

$list = makeStorageListHtml($rs, $param, $dao, $conn);
$paging = mkDotAjaxPage($rsCount, $page, $scrnum, $list_num, "movePage");

echo $list . "♪" . $paging;
$conn->close();

?>
