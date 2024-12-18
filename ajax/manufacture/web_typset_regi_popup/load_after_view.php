<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/common_define/common_info.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/manufacture/typset_mng/TypsetListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new TypsetListDAO();

$basic_after_op_seqno = $fb->form("basic_after_op_seqno");

$param = array();
$param["table"] = "basic_after_op";
$param["col"] = "after_name, depth1, depth2, depth3, amt, 
    amt_unit, memo, extnl_brand_seqno";
$param["where"]["basic_after_op_seqno"] = $basic_after_op_seqno;

$rs = $dao->selectData($conn, $param);

$param = array();
$param["table"] = "extnl_brand";
$param["col"] = "extnl_etprs_seqno";
$param["where"]["extnl_brand_seqno"] = $rs->fields["extnl_brand_seqno"];

$extnl_etprs_seqno = $dao->selectData($conn, $param)->fields["extnl_etprs_seqno"];

$param = array();
$param["table"] = "extnl_etprs";
$param["col"] = "manu_name";
$param["where"]["extnl_etprs_seqno"] = $extnl_etprs_seqno;

$manu_name = $dao->selectData($conn, $param)->fields["manu_name"];

echo $rs->fields["after_name"] . "♪" . $rs->fields["depth1"] . "♪" . 
  $rs->fields["depth2"] . "♪" . $rs->fields["depth3"] . "♪" .
  $rs->fields["amt"] . "♪" . $rs->fields["amt_unit"] . "♪" .
  $rs->fields["memo"] . "♪" . $manu_name;
$conn->close();
?>
