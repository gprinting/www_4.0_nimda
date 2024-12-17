<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/produce/paper_materials_mng/PaperMaterialsMngDAO.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/nimda/pageLib.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new PaperMaterialsMngDAO();

$conn->StartTrans();

//취소할 발주id
$paper_op_seqno = $fb->form("paper_op_seqno");

$param = array();
$param["paper_op_seqno"] = $fb->form("paper_op_seqno");

if( $dao->updatePaperMaterialsMngCancel($conn, $param) ) {
    echo "취소되었습니다.";
}else{
    echo "취소실패했습니다.";
}

$conn->CompleteTrans();
$conn->close();
?>
