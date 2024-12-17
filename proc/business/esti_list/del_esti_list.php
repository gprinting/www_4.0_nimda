<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/business/esti_mng/EstiListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new EstiListDAO();
$check = 1;

$conn->StartTrans();
$seqno = explode(',', $fb->form("seqno"));

//견적파일 삭제
$param = array();
$param["table"] = "esti_file";
$param["prk"] = "esti_seqno";
$param["prkVal"] = $seqno;

$rs = $dao->deleteMultiData($conn, $param);

if (!$rs) {
    $check = 0;
}

$param = array();
$param["table"] = "admin_esti_file";
$param["prk"] = "esti_seqno";
$param["prkVal"] = $seqno;

$rs = $dao->deleteMultiData($conn, $param);

if (!$rs) {
    $check = 0;
}

//견적 삭제
$param = array();
$param["esti_seqno"] = $seqno;

$rs = $dao->deleteEstiList($conn, $param);

if (!$rs) {
    $check = 0;
}

$conn->CompleteTrans();
$conn->Close();
echo $check;
?>
