<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/basic_mng/pur_etprs_mng/PurEtprsListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$purDAO = new PurEtprsListDAO();

$conn->StartTrans();

$check = 1;
$brand_seqno = $fb->form("brand_seqno");

// 생산품목 삭제

// 브랜드 삭제
$param = array();
$param["table"] = "extnl_brand";
$param["prk"] = "extnl_brand_seqno";
$param["prkVal"] = $brand_seqno;
$result = $purDAO->deleteData($conn, $param);

if (!$result) {
    $check = 0;
}

echo $check;
$conn->CompleteTrans();
$conn->close();
?>
