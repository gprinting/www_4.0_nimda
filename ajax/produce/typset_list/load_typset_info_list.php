<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/produce/typset_mng/TypsetListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new TypsetListDAO();

//조판 정보리스트
$param = array();
$param["cate_sortcode"] = $fb->form("cate_sortcode");
$param["sorting"] = $fb->form("sorting");
$param["sorting_type"] = $fb->form("sorting_type");

$rs = $dao->selectTypsetInfoList($conn, $param);
$list = makeTypsetInfoListHtml($rs, $param);

echo $list;
$conn->close();
?>
