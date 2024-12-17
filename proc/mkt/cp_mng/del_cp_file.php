<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . '/com/nexmotion/job/nimda/mkt/mkt_mng/CpMngDAO.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();
$conn->StartTrans();

$fb = new FormBean();

$cpDAO = new CpMngDAO();

$param = array();
$param["table"] = "cp_file";
$param["prk"] = "cp_file_seqno";
$param["prkVal"] = $fb->form("file_seqno");

$result = $cpDAO->deleteData($conn, $param);

if ($result) {
    
    echo "1";

} else {

    echo "2";
}

$conn->CompleteTrans();
$conn->close();
?>
