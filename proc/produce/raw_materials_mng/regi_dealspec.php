<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/define/nimda/common_config.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/produce/raw_materials_mng/RawMaterialsMngDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();
$conn->StartTrans();

$fb = new FormBean();
$dao = new RawMaterialsMngDAO();
$check = "등록에 성공하였습니다.";

//거래명세서 등록
$param = array();
$param["name"] = $fb->form("pur_prdt");
$param["extnl_etprs_seqno"] = $fb->form("pur_manu");
$param["stan"] = $fb->form("stan");
$param["amt"] = $fb->form("amt");
$param["amt_unit"] = $fb->form("amt_unit");
$param["unitprice"] = $fb->form("unitprice");
$param["memo"] = $fb->form("memo");
$param["price"] = $fb->form("price");
$param["vat_yn"] = $fb->form("vat_yn");

$rs = $dao->insertDealspec($conn, $param);

if (!$rs) {
    $check = "등록에 실패하였습니다.";
}

$conn->CompleteTrans();
$conn->Close();
echo $check;
?>
