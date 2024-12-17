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

//카테고리 배너 파일 삭제
$param = array();
$param["table"] = "cate_banner";
$param["col"]["file_path"] = "";
$param["col"]["save_file_name"] = "";
$param["col"]["origin_file_name"] = "";
$param["prk"] = "cate_banner_seqno";
$param["prkVal"] = $fb->form("banner_seqno");

$result = $prdtDAO->updateData($conn, $param);

if ($result) {
    echo "1";
} else {
    echo "2";
}

$conn->CompleteTrans();
$conn->close();
?>
