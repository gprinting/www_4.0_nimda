<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/define/nimda/common_config.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/business/oto_inq_mng/OtoInqMngDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new OtoInqMngDAO();
$check = 1;

$conn->StartTrans();
$seqno = $fb->form("seqno");

$param = array();
$param["table"] = "oto_inq_reply";
$param["col"]["is_deleted"] = "Y";
$param["prk"] = "oto_inq_seqno";
$param["prkVal"] = $seqno;
$rs = $dao->updateData($conn,$param);


$param = array();
$param["table"] = "oto_inq";
$param["col"]["answ_yn"] = "N";
$param["prk"] = "oto_inq_seqno";
$param["prkVal"] = $seqno;
$rs = $dao->updateData($conn,$param);

if (!$rs) {
    $check = 0;
}

$conn->CompleteTrans();
$conn->Close();
echo $check;
?>
