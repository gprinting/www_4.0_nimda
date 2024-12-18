<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/produce/typset_mng/TypsetListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new TypsetListDAO();

//옵션 발주 리스트
$param = array();
$param["table"] = "opt";
$param["col"] = "opt_seqno, name ,depth1 ,depth2 , depth3 ,amt ,crtr_unit";

$rs = $dao->selectData($conn, $param);
$list = makeOptInfoListHtml($rs, $param);

echo $list;
$conn->close();
?>
