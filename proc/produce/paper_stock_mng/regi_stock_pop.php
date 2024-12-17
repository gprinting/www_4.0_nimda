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
$check = "등록에 성공하였습니다.";

//종이재고조정 등록
$param = array();
$param["manu"] = $fb->form("manu");
$param["paper_name"] = $fb->form("paperName");
$param["paper_dvs"] = $fb->form("paperDvs");
$param["paper_color"] = $fb->form("paperColor");
$param["paper_basisweight"] = $fb->form("paperBasisweight");
$param["adjustFlag"] = $fb->form("adjustFlag");
$param["amt"] = $fb->form("amt");
$param["adjust_reason"] = $fb->form("adjustReason");
$param["admin"] = $fb->session("name");

$rs = $dao->insertPaperStock($conn, $param);

if (!$rs) {
    $check = "등록에 실패하였습니다.";
}

$conn->CompleteTrans();
$conn->Close();
echo $check;
?>
