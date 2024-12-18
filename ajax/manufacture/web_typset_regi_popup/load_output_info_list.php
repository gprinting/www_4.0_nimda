<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/manufacture/typset_mng/TypsetListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new TypsetListDAO();

//출력 정보리스트
$param = array();
$param["extnl_etprs_seqno"] = $fb->form("extnl_etprs_seqno");
$param["sorting"] = $fb->form("sorting");
$param["sorting_type"] = $fb->form("sorting_type");

$rs = $dao->selectOutputInfoList($conn, $param);
$list = makeOutputInfoListHtml($rs, $param);

echo $list;
$conn->close();
?>
