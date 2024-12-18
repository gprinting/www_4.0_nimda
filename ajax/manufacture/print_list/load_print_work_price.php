<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/manufacture/print_mng/PrintListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new PrintListDAO();

$param = array();
$param["table"] = "print";
$param["col"] = "basic_price";
$param["where"]["extnl_brand_seqno"] = $fb->form("extnl_brand_seqno");
$param["where"]["search_check"] = $fb->form("print_name") . "|" . $fb->form("size");
$param["where"]["crtr_unit"] = $fb->form("amt_unit");

$rs = $dao->selectData($conn, $param);

$basic_price = $rs->fields["basic_price"];
$price = number_format(intVal($basic_price) * intVal($fb->form("amt")));

echo $price;
$conn->close();
?>
