<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/manufacture/typset_mng/TypsetListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new TypsetListDAO();

$typset_num = $fb->form("typset_num");

$param = array();
$param["table"] = "basic_after_op";
$param["col"] = "basic_after_op_seqno, after_name,
    depth1, depth2, depth3, amt, amt_unit, memo, 
    extnl_brand_seqno";
$param["where"]["typset_num"] = $typset_num;

$rs = $dao->selectData($conn, $param);
$list = makeAfterOpListHtml($conn, $dao, $rs);

echo $list;
$conn->close();
?>
