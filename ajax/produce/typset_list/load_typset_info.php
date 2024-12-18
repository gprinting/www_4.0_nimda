<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/produce/typset_mng/TypsetListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new TypsetListDAO();

$seqno = $fb->form("seqno");

$param = array();
$param["typset_format_seqno"] = $seqno;

$rs = $dao->selectTypsetInfoApply($conn, $param);

//echo $typset_info_list . "♪" .
echo $rs->fields["name"] . "♪" . 
$rs->fields["affil"] . "♪" .
$rs->fields["subpaper"] . "♪" .
$rs->fields["wid_size"] . "♪" .
$rs->fields["vert_size"] . "♪" .
$rs->fields["honggak_yn"] . "♪" .
$rs->fields["purp"] . "♪" .
$rs->fields["dlvrboard"];

$conn->close();
?>
