<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . '/com/nexmotion/job/nimda/basic_mng/prdt_price_mng/OptPriceListDAO.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new OptPriceListDAO();

$cate_sortcode = $fb->form("cate_sortcode");
$depth    = $fb->form("depth");
$opt_name = $fb->form("opt_name");
$dep1_val = $fb->form("dep1_val");
$dep2_val = $fb->form("dep2_val");

$param = array();
$param["cate_sortcode"] = $cate_sortcode;
$param["opt_name"] = $opt_name;
$param["depth1"]   = $dep1_val;
$param["depth2"]   = $dep2_val;

$dvs = "OPT_NAME";
if ($depth === '0') {
    $dvs = "DEPTH1";
} else if ($depth === '1') {
    $dvs = "DEPTH2";
}

echo $dao->selectCateOptHtml($conn, $dvs, $param);
?>
