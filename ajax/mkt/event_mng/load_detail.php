<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/common_lib/CommonUtil.inc");
include_once(INC_PATH . '/com/nexmotion/job/nimda/basic_mng/prdt_price_mng/PrdtPriceListDAO.inc');
include_once(INC_PATH . '/com/nexmotion/doc/nimda/basic_mng/prdt_price_mng/PrdtPriceListPrintCond.inc');
include_once(INC_PATH . '/com/nexmotion/html/nimda/mkt/mkt_mng/EventMngHTML.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new PrdtPriceListDAO();
$util = new CommonUtil();

$cate_sortcode = $fb->form("cate_sortcode");

$param = array();
$param["cate_sortcode"] = $cate_sortcode;

$ret  = "{";
$ret .= " \"paper\" : \"%s\",";
$ret .= " \"size\"  : \"%s\",";
$ret .= " \"print\" : \"%s\"";
$ret .= "}";

$paper = $dao->selectCatePaperHtml($conn, "NAME", $param);
$paper = $util->convJsonStr($paper);

$size = $dao->selectCateSizeHtml($conn, $param);
$size = $util->convJsonStr($size);

$print = null;
$print = makePrintTmptHtmlSheet($conn, $dao, $param);

$print = $util->convJsonStr($print);

echo sprintf($ret, $paper, $size, $print);

$conn->Close();
?>