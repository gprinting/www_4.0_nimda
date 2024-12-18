<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . '/com/nexmotion/job/nimda/common/NimdaCommonDAO.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$commonDAO = new NimdaCommonDAO();

$cate_sortcode = $fb->form("cate_sortcode");

//$conn->debug = 1;

echo $commonDAO->selectCateList($conn, $cate_sortcode);
$conn->Close();
?>
