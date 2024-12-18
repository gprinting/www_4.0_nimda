<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/produce/typset_mng/TypsetListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new TypsetListDAO();

$param = array();
$param["table"] = "brochure_typset";
$param["col"] = "typset_num";
$param["where"]["brochure_typset_seqno"] = $fb->form("seqno");

$rs = $dao->selectData($conn, $param);

$typset_num = $rs->fields["typset_num"];

//종이 발주 리스트
$param = array();
$param["typset_num"] = $typset_num;
$rs = $dao->selectPaperDirectionsList($conn, $param);
$list = makePaperOpListHtml($rs, $param);

echo $list;
$conn->close();
?>
