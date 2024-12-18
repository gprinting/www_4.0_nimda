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
$param['dlvr_dvs'] = $fb->form("dlvr_dvs"); //현재 출고실에서는 상품을 전단류, 명함류로 분류해 배송함
$param['after_yn'] = $fb->form("after_yn");
$param['member_name'] = $fb->form("member_name");
$param['after_yn'] = $fb->form("after_yn");
$param['theday_yn'] = $fb->form("theday_yn");
$param["dlvr_way"] = $fb->form("dlvr_way");
$param["dlvr_way_detail"] = $fb->form("dlvr_way_detail");
$param["dvs"] = "SEQ";

//현재 페이지
$page = $fb->form("page");
$list_num = $fb->form("showPage");

//리스트 보여주는 갯수 설정
if (!$fb->form("showPage")) {
    $list_num = 30;
}


$from_date = $fb->form("date_from");
$to_date = $fb->form("date_to");

$param["from"] = $from_date;
$param["to"] = $to_date;
$param["member_name"] = $fb->form("member_name");
$param["title"] = $fb->form("title");
$param["dlvr_way"] = "01";
$param["state"] = $fb->form("state");

$rs = $dao->selectDeliveryList($conn, $param);



$list = makeDeliveryListHtml($conn, $dao, $rs, $param);
//$paging = mkDotAjaxPage($rsCount, $page, $scrnum, $list_num, "movePage");

echo $list . "♪";
$conn->close();

?>
