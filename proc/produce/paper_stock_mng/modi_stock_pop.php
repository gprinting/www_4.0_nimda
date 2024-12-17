<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/define/nimda/common_config.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . '/com/nexmotion/job/nimda/produce/paper_stock_mng/PaperStockMngDAO.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();
$conn->StartTrans();

$fb = new FormBean();
$dao = new PaperStockMngDAO();
$check = "수정에 성공하였습니다.";

//종이재고조정 등록
$param = array();
$param["seq"] = $fb->form("seq");
$param["amt"] = $fb->form("amt");
$param["adjust_reason"] = $fb->form("adjustReason");
$param["admin"] = $fb->session("name");

$rs = $dao->updatePaperStock($conn, $param);

if (!$rs) {
    $check = "수정에 실패하였습니다.";
}

$conn->CompleteTrans();
$conn->Close();
echo $check;
?>
