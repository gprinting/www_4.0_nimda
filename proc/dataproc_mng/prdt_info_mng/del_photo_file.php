<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/dataproc_mng/set/PrdtInfoMngDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();
$conn->StartTrans();

$fb = new FormBean();

$prdtDAO = new PrdtInfoMngDAO();

//카테고리 사진 파일 삭제
$param = array();
$param["table"] = "cate_photo";
$param["prk"] = "cate_photo_seqno";
$param["prkVal"] = $fb->form("photo_seqno");

$result = $prdtDAO->deleteData($conn, $param);

if ($result) {
    echo "1";
} else {
    echo "2";
}

$conn->CompleteTrans();
$conn->close();
?>
