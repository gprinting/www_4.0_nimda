<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/calcul_mng/cashbook/CashbookRegiDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();

$cashbookDAO = new CashbookRegiDAO();
$conn->StartTrans();

//금전출납부 삭제
$param = array();
$param["table"] = "cashbook";
$param["prk"] = "cashbook_seqno";
$param["prkVal"] = $fb->form("cashbook_seqno"); 
$result = $cashbookDAO->deleteData($conn, $param);

echo $result;

$conn->CompleteTrans();
$conn->close();
?>
