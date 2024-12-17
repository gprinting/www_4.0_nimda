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
//공지사항 파일 삭제
$param = array();
$param["table"] = "notice_file";
$param["prk"] = "notice_file_seqno";
$param["prkVal"] = $fb->form("file_seq");

$result = $bulletinDAO->deleteData($conn, $param);

if ($result) {

    echo "1";

} else {

    echo "2";
}
$conn->CompleteTrans();
$conn->close();
?>
