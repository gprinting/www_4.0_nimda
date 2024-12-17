<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/business/oto_inq_mng/OtoInqMngDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new OtoInqMngDAO();
$check = 1;

$conn->StartTrans();

$param = array();
$param["table"] = "oto_inq_reply_file";
$param["prk"] = "oto_inq_reply_file_seqno";
$param["prkVal"] = $fb->form("seqno");

$rs = $dao->deleteData($conn, $param);

if (!$rs) {
    $check = 0;
}

$conn->CompleteTrans();
$conn->Close();
echo $check;
?>
