<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/calcul_mng/virt_ba_mng/VirtBaListDAO.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/nimda/pageLib.inc");
$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$virtDAO = new VirtBaListDAO();

$param_update = array();
$param_update["table"] = "member_refund_history";
$param_update["col"]["refund_yn"] = "Y";
$param_update["prk"] = "member_refund_history_seqno";
$param_update["prkVal"] = $fb->form("seq");
$virtDAO->updateData($conn, $param_update);

echo "1";

$conn->close();
?>
