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

//팝업 파일 지우기
$param = array();
$param["table"] = "popup_admin";
$param["col"]["file_path"] = "";
$param["col"]["save_file_name"] = "";
$param["col"]["origin_file_name"] = "";
$param["prk"] = "popup_admin_seqno";
$param["prkVal"] = $fb->form("popup_seqno");

$result = $bulletinDAO->updateData($conn, $param);

if ($result) {
    
    echo "1";

} else {

    echo "2";
}

$conn->CompleteTrans();
$conn->close();
?>
