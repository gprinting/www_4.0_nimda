<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/manufacture/typset_mng/TypsetListDAO.inc");
include_once(INC_PATH . '/com/nexmotion/common/util/nimda/pageLib.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new TypsetListDAO();

$param = array();
$param["typset_num"] = $fb->form("typset_num");

$rs = $dao->selectTypsetPaperOpMngList($conn, $param);
$list = makeTypsetPaperOpMngListHtml($rs, $param);

if ($fb->form("typset_num")) {
    echo $list;
} else {
    echo "";
}
$conn->close();
?>
