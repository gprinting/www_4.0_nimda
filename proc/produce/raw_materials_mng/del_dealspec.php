<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/produce/raw_materials_mng/RawMaterialsMngDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();
$conn->StartTrans();

$fb = new FormBean();
$dao = new RawMaterialsMngDAO();
$check = "삭제되었습니다.";

//거래명세서 삭제
$param = array();
$param["dealspec_seqno"] = $fb->form("dealspec_seqno");

$rs = $dao->deleteDealspec($conn, $param);

if (!$rs) {
    $check = "삭제에실패하였습니다.";
}

$conn->CompleteTrans();
$conn->Close();
echo $check;
?>
