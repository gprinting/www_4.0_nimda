<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/calcul_mng/tab/SalesTabListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new SalesTabListDAO();
$check = 1;
$conn->StartTrans();

//증빙 유형및 정보 변경
$param = array();
$param["table"] = "public_admin";
$param["col"]["req_year"] = $fb->form("year");
$param["col"]["req_mon"] = $fb->form("mon");
$param["col"]["req_date"] = date("Y-m-d H:i:s",time());
$param["col"]["tab_public"] = "미발행";
$param["col"]["member_seqno"] = $fb->form("regi_member_seqno");
$param["col"]["public_date"] = $fb->form("edit_public_date");
$param["col"]["pay_price"] = $fb->form("edit_pay_price");
$param["col"]["card_price"] = $fb->form("edit_card_price");
$param["col"]["money_price"] = $fb->form("edit_cash_price");
$param["col"]["etc_price"] = $fb->form("edit_etc_price");
$param["col"]["oa"] = $fb->form("edit_oa");
$param["col"]["before_oa"] = $fb->form("edit_before_oa");
$param["col"]["public_dvs"] = $fb->form("edit_public_dvs");
$param["col"]["corp_name"] = $fb->form("edit_corp_name");
$param["col"]["repre_name"] = $fb->form("edit_repre_name");
$param["col"]["crn"] = $fb->form("edit_crn");
$param["col"]["bc"] = $fb->form("edit_bc");
$param["col"]["tob"] = $fb->form("edit_tob");
$param["col"]["addr"] = $fb->form("edit_addr");
$param["col"]["zipcode"] = $fb->form("edit_zipcode");
$param["col"]["unitprice"] = $fb->form("edit_unitprice");
$param["col"]["supply_price"] = $fb->form("edit_supply_price");
$param["col"]["object_price"] = $fb->form("edit_object_price");
$param["col"]["evid_dvs"] = $fb->form("evid_dvs");
$param["col"]["cashreceipt_num"] = $fb->form("cashreceipt_num");
$param["col"]["vat"] = $fb->form("edit_vat");
$param["col"]["public_state"] = "대기";

$rs = $dao->insertData($conn, $param);
if (!$rs) {
    $check = 0;
}

$conn->CompleteTrans();
$conn->Close();
echo $check;
?>
