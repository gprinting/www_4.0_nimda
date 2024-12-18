<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . '/com/nexmotion/job/nimda/basic_mng/prdt_price_mng/CalculPriceListDAO.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new CalculPriceListDAO();

$dvs = $fb->form("dvs");
$cate_sortcode = $fb->form("cate_sortcode");
$print_name = $fb->form("print_name");

$param = array();
$param["cate_sortcode"] = $cate_sortcode;
$param["name"] = $print_name;

echo $dao->selectPrdtPrintInfoHtml($conn, $dvs, $param);
?>
