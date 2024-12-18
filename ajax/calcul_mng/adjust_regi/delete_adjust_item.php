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
$param["table"] = "adjust";
$param["prk"] = "adjust_seqno";
$param["prkVal"] = $fb->form("adjust_seqno");
$result = $cashbookDAO->deleteData($conn, $param);

$param = array();
$param["table"] = "member_pay_history";
$param["col"]["adjust_price"] = 0;

$param["prk"] = "adjust_seqno";
$param["prkVal"] = $fb->form("adjust_seqno");
$result = $cashbookDAO->deleteData($conn, $param);


echo $result;

$conn->CompleteTrans();
$conn->close();
?>