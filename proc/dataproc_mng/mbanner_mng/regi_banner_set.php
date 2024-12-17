<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/dataproc_mng/bulletin_mng/BulletinMngDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new BulletinMngDAO();
$check = 1;

$seqno = $fb->form("seqno");

$param = array();
$param["table"] = "main_banner_set";
$param["col"]["mb_count"] = $fb->form("mb_count");
$param["col"]["slide_timer"] = $fb->form("slide_timer");

$conn->StartTrans();

if ($seqno) {
    $param["prk"] = "main_banner_set_seqno";
    $param["prkVal"] = $seqno;

    $rs = $dao->updateData($conn, $param);
} else {
    $rs = $dao->insertData($conn, $param);
    $seqno = $conn->insert_ID();
}

if (!$rs) {
    $check = 0;
}

$param = array();
$param["seq"] = $fb->form("mb_count");

$rs = $dao->updateMbannerUseY($conn, $param);

if (!$rs) {
    $check = 0;
}

$rs = $dao->updateMbannerUseN($conn, $param);

if (!$rs) {
    $check = 0;
}

echo $check . "♪" . $seqno;
$conn->CompleteTrans();
$conn->close();
?>
