<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/produce/typset_mng/TypsetStandbyListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new TypsetStandbyListDAO();

$param = array();
$param["dvs"] = "SEQ";

$seqno = $fb->form("seqno");
if ($seqno == null || $seqno == "") {
    $seqno = 0;
}
$param["amt_order_detail_sheet_seqno"] = $seqno;
//$param["order_detail_count_file_seqno"] = $seqno;

$rs = $dao->selectFlattYTypsetStandbyList($conn, $param);
$list = makeTmpTypsetStandbyListHtml($rs, $param);

echo $list;
$conn->close();
?>
