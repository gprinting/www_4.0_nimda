<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/basic_mng/cate_mng/BasicProBusMngDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new BasicProBusMngDAO();

$el = $fb->form("el");
$seqno = $fb->form("seqno");

$param = array();
$param["table"] = "opt";
$param["col"] = "name, 
    depth1, depth2, depth3";
$param["where"]["opt_seqno"] = $seqno;

$rs = $dao->selectData($conn, $param);

$name = $rs->fields["name"];
$depth1 = $rs->fields["depth1"];
$depth2 = $rs->fields["depth2"];
$depth3 = $rs->fields["depth3"];

echo $name
. "♪" . $depth1
. "♪" . $depth2
. "♪" . $depth3 ;
$conn->Close();
?>
