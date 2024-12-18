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
$param["table"] = "prdt_after";
$param["col"] = "prdt_after_seqno";
$param["where"]["after_name"] = $fb->form("after_name");

if ($fb->form("after_depth1")) {
    $param["where"]["depth1"] = $fb->form("after_depth1");
}

if ($fb->form("after_depth2")) {
    $param["where"]["depth2"] = $fb->form("after_depth2");
}

if ($fb->form("after_depth3")) {
    $param["where"]["depth3"] = $fb->form("after_depth3");
}

$param["where"]["crtr_unit"] = $fb->form("after_unit");

$rs = $dao->selectData($conn, $param);

$prdt_after_seqno = $rs->fields["prdt_after_seqno"];

$param = array();
$param["table"] = "cate_after";
$param["col"] = "mpcode";
$param["where"]["prdt_after_seqno"] = $prdt_after_seqno;

$rs = $dao->selectData($conn, $param);

$mpcode = $rs->fields["mpcode"];

$param = array();
$param["table"] = "cate_after_price";
$param["col"] = "sell_price";
$param["where"]["cate_after_mpcode"] = $mpcode;

$rs = $dao->selectData($conn, $param);

echo $rs->fields["sell_price"];
$conn->Close();
?>
