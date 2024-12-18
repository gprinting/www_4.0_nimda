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
$param["table"] = "prdt_output_info";
$param["col"] = "mpcode";
$param["where"]["output_name"] = $fb->form("output_name");
$param["where"]["output_board_dvs"] = $fb->form("output_board");
$param["where"]["size"] = $fb->form("output_size");

$rs = $dao->selectData($conn, $param);

$mpcode = $rs->fields["mpcode"];

$param = array();
$param["table"] = "prdt_stan_price";
$param["col"] = "sell_price";
$param["where"]["prdt_output_info_mpcode"] = $mpcode;

$rs = $dao->selectData($conn, $param);

echo $rs->fields["sell_price"];
$conn->Close();
?>
