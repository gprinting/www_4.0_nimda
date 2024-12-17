<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/common_lib/CommonUtil.inc");
include_once(INC_PATH . '/com/nexmotion/job/nimda/basic_mng/prdt_price_mng/PrdtPriceListDAO.inc');
include_once(INC_PATH . '/com/nexmotion/doc/nimda/basic_mng/prdt_price_mng/PrdtPriceListPrintCond.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new PrdtPriceListDAO();
$util = new CommonUtil();

$cate_sortcode = $fb->form("cate_sortcode");

$param = array();
$param["cate_sortcode"] = $cate_sortcode;

$ret  = "{";
$ret .= " \"paper\" : \"%s\"";
$ret .= "}";

$paper = $dao->selectCatePaperHtml($conn, "NAME", $param);
$paper = $util->convJsonStr($paper);

echo sprintf($ret, $paper);
$conn->Close();
?>
