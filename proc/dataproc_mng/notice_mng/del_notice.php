<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/dataproc_mng/bulletin_mng/BulletinMngDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();
$conn->StartTrans();

$fb = new FormBean();
$bulletinDAO = new BulletinMngDAO();
$check = 1;

//공지사항 파일 삭제
$param = array();
$param["table"] = "notice_file";
$param["prk"] = "notice_seqno";
$param["prkVal"] = $fb->form("notice_seq");

$result = $bulletinDAO->deleteData($conn, $param);
if (!$result) $check = 0;

//공지사항 삭제
$param = array();
$param["table"] = "notice";
$param["prk"] = "notice_seqno";
$param["prkVal"] = $fb->form("notice_seq");

$result = $bulletinDAO->deleteData($conn, $param);
if (!$result) $check = 0;

echo $check;

$conn->CompleteTrans();
$conn->close();
?>
