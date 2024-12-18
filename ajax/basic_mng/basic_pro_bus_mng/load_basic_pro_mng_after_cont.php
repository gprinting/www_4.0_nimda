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
$param["typset_format_seqno"] = $seqno;
$rs = $dao->selectRegiAfterList($conn, $param);

if ($rs->EOF == 1) {
    $process_yn = "N";
} else {
    $process_yn = "Y";
}

echo makeRegiAfterListHtml($rs) . "♪" . $process_yn;
$conn->Close();
?>
