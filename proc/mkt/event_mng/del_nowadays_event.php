<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/define/nimda/common_config.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/mkt/mkt_mng/EventMngDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();
$conn->StartTrans();

$fb = new FormBean();
$eventDAO = new EventMngDAO();
$check = 1;

$param = array();
$param["table"] = "nowadays_busy_file";
$param["prk"] = "nowadays_busy_event_seqno";
$param["prkVal"] = $fb->form("nowadays_seqno");
$result = $eventDAO->deleteData($conn, $param);
if (!$result) {

    $check = 2;
}

$param = array();
$param["table"] = "nowadays_busy_event";
$param["prk"] = "nowadays_busy_event_seqno";
$param["prkVal"] = $fb->form("nowadays_seqno");

$result = $eventDAO->deleteData($conn, $param);
if (!$result) {

    $check = 2;
}

echo $check;

$conn->CompleteTrans();
$conn->close();

?>
