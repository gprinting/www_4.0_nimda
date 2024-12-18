<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/common/BasicMngCommonDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new BasicMngCommonDAO();
$commonDAO = $dao;

$param = array();
$param["table"] = "prdt_print_info";
$param["col"] = "mpcode";
$param["where"]["print_name"] = $fb->form("print_name");
$param["where"]["purp_dvs"] = $fb->form("print_purp");
$param["where"]["cate_sortcode"] = $fb->form("cate_sortcode");
$param["where"]["crtr_unit"] = $fb->form("print_unit");

$rs = $dao->selectData($conn, $param);

$mpcode = $rs->fields["mpcode"];

$param = array();
$param["table"] = "prdt_print_price";
$param["col"] = "sell_price";
$param["where"]["prdt_print_info_mpcode"] = $mpcode;

$rs = $dao->selectData($conn, $param);

echo $rs->fields["sell_price"];
$conn->Close();
?>
