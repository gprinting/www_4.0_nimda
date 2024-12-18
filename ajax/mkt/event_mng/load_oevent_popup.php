<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/mkt/mkt_mng/EventMngDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$eventDAO = new EventMngDAO();

//카테고리 대분류 낱장형 쿼리 실행
$result = $eventDAO->selectFlatCateList($conn);
//카테고리 대분류 콤보박스 셋팅
$arr = [];
$arr["flag"] = "Y";
$arr["def"] = "대분류";
$arr["dvs"] = "cate_name";
$arr["val"] = "sortcode";
$cate_top = makeSelectOptionHtml($result, $arr);

$param = array();
//판매채널 콤보박스 셋팅
$param["sell_site"] = $eventDAO->selectSellSite($conn);
//시간 콤보박스 셋팅
$param["hour_list"] = makeOptionTimeHtml(0,23);
//분 콤보박스 셋팅
$param["min_list"] = makeOptionTimeHtml(0,59);
//대분류 콤보박스 셋팅
$param["cate_top"] = $cate_top;
$param["hide_btn"] = "style=\"display:none\"";

$html = getOeventView($param);

echo $html;

$conn->close();
?>
