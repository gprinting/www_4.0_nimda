<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/manufacture/typset_mng/TypsetFormatDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new TypsetFormatDAO();

$el = $fb->form("el");
$seqno = $fb->form("seqno");

$param = array();
$param["table"] = "print";
$param["col"] = "name, extnl_brand_seqno, 
    wid_size, vert_size";
$param["where"]["print_seqno"] = $seqno;

$rs = $dao->selectData($conn, $param);

$name = $rs->fields["name"];
$wid_size = $rs->fields["wid_size"];
$vert_size = $rs->fields["vert_size"];

$param = array();
$param["table"] = "extnl_brand";
$param["col"] = "extnl_etprs_seqno";
$param["where"]["extnl_brand_seqno"] = $rs->fields["extnl_brand_seqno"];

$rs = $dao->selectData($conn, $param);

$param = array();
$param["table"] = "extnl_etprs";
$param["col"] = "manu_name";
$param["where"]["extnl_etprs_seqno"] = $rs->fields["extnl_etprs_seqno"];

$rs = $dao->selectData($conn, $param);

$manu = $rs->fields["manu_name"];

echo $name . "♪" . $manu
. "♪" . $wid_size
. "♪" . $vert_size ;
$conn->Close();
?>
