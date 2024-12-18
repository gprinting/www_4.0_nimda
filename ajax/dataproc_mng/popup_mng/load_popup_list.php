<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/dataproc_mng/bulletin_mng/BulletinMngDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$bulletinDAO = new BulletinMngDAO();

//팝업 리스트
$result = $bulletinDAO->selectPopupList($conn, $param);
$popup_list = makePopupList($result);

echo $popup_list;

$conn->close();
?>
