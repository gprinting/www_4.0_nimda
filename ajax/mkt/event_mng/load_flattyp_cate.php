<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/mkt/mkt_mng/EventMngDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$eventDAO = new EventMngDAO();

//카테고리 대분류 쿼리 실행
$result = $eventDAO->selectFlatCateList($conn, $param);
//카테고리 대분류 콤보박스 셋팅
$arr = [];
$arr["flag"] = "Y";
$arr["def"] = "대분류";
$arr["dvs"] = "cate_name";
$arr["val"] = "sortcode";
$html = makeSelectOptionHtml($result, $arr);

echo $html;

$conn->close();
?>
