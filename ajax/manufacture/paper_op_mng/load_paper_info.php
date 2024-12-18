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
$param["prdt_paper_seqno"] = $fb->form("seqno");

$rs = $dao->selectPaperInfoApply($conn, $param);

echo $rs->fields["name"] . "♪" . 
$rs->fields["dvs"] . "♪" .
$rs->fields["color"] . "♪" .
$rs->fields["basisweight"] . "♪" .
$rs->fields["basisweight_unit"] . "♪" .
$rs->fields["manu_name"] . "♪" .
$rs->fields["affil"] . "♪" .
explode("*", $rs->fields["size"])[0] . "♪" .
    explode("*", $rs->fields["size"])[1] . "♪" .
$rs->fields["extnl_brand_seqno"] . "♪" .
$rs->fields["paper_seqno"];

$conn->close();
?>
