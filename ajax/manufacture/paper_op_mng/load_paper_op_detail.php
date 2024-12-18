<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/manufacture/item_mng/PaperOpMngDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new PaperOpMngDAO();

$param = array();
$param["table"] = "paper_op";
$param["col"] = "paper_op_seqno, name, dvs, color,typset_num,
    basisweight, op_affil, op_size, stor_subpaper,
    stor_size, grain, amt, amt_unit, memo, storplace, extnl_etprs_seqno";
$param["where"]["paper_op_seqno"] = $fb->form("paper_op_seqno");

$rs = $dao->selectData($conn, $param);

$op_size = explode("*", $rs->fields["op_size"]);
$op_wid_size = $op_size[0];
$op_vert_size = $op_size[1];
$stor_size = explode("*", $rs->fields["stor_size"]);
$stor_wid_size = $stor_size[0];
$stor_vert_size = $stor_size[1];

$extnl_etprs_seqno = $dao->selectData($conn, $param)->fields["extnl_etprs_seqno"];

$param = array();
$param["table"] = "extnl_etprs";
$param["col"] = "manu_name";
$param["where"]["extnl_etprs_seqno"] = $extnl_etprs_seqno;

$manu_name = $dao->selectData($conn, $param)->fields["extnl_etprs_name"];

echo $rs->fields["name"] . "♪" . 
$rs->fields["dvs"] . "♪" .
$rs->fields["color"] . "♪" .
$rs->fields["basisweight"] . "♪" .
$rs->fields["basisweight_unit"] . "♪" .
$manu_name . "♪" .
$rs->fields["op_affil"] . "♪" .
$op_wid_size . "♪" .
$op_vert_size . "♪" .
$rs->fields["stor_place"] . "♪" .
$rs->fields["stor_subpaper"] . "♪" .
$stor_wid_size . "♪" .
$stor_vert_size . "♪" .
$rs->fields["grain"] . "♪" .
$rs->fields["amt"] . "♪" .
$rs->fields["amt_unit"] . "♪" .
$rs->fields["memo"] . "♪" .
$rs->fields["extnl_etprs_seqno"] . "♪" .
$rs->fields["paper_op_seqno"]. "♪" .
    $rs->fields["typset_num"];

$conn->close();
?>
