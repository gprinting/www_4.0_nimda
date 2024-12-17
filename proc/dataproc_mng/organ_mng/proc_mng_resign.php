<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/dataproc_mng/organ_mng/OrganMngDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();
$conn->StartTrans();

$fb = new FormBean();
$organDAO = new OrganMngDAO();
$check = 1;

$empl_seqno = $fb->form("mng_seq");

//퇴사처리
$param = array();
$param["table"] = "empl";
$param["col"]["resign_yn"] = "Y";
$param["prk"] = "empl_seqno";
$param["prkVal"] = $empl_seqno;

$result = $organDAO->updateData($conn, $param);
if (!$result) $check = 0;

$param = array();
$param["table"] = "auth_admin_page";
$param["col"]["auth_yn"] = "N";
$param["prk"] = "empl_seqno";
$param["prkVal"] = $empl_seqno;

$result = $organDAO->updateData($conn, $param);
if (!$result) $check = 0;

echo $check;

$conn->CompleteTrans();
$conn->close();
?>
