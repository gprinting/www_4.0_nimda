<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . '/com/nexmotion/job/nimda/common/NimdaCommonDAO.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$commonDAO = new NimdaCommonDAO();

//인쇄 카테고리 중분류
$param = array();
$param["table"] = "cate";
$param["col"] = "cate_name, sortcode";
$param["where"]["cate_level"] = "2";

$print_cate_mid_rs =  $commonDAO->selectData($conn, $param);
$print_cate_mid_html = makeOptionHtml($print_cate_mid_rs, "sortcode", "cate_name", "중분류(전체)", "Y");

echo $print_cate_mid_html;

$conn->Close();
?>
