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

$public_dvs = $fb->form("public_dvs");

//증빙 유형및 정보 변경
$param = array();
$param["table"] = "public_admin";
$param["col"]["member_name"] = $fb->form("member_name");
$param["col"]["public_dvs"] = $fb->form("public_dvs");
$param["col"]["cashreceipt_num"] = $fb->form("cashreceipt_num");
$param["col"]["public_date"] = $fb->form("public_date");
$param["col"]["supply_price"] = $fb->form("supply_price");
$param["col"]["vat"] = $fb->form("vat");
$param["col"]["public_state"] = "완료";

$param["col"]["supply_corp"] = NULL;
$param["col"]["crn"] = NULL;
$param["col"]["repre_name"] = NULL;
$param["col"]["addr"] = NULL;
$param["col"]["bc"] = NULL;
$param["col"]["tob"] = NULL;
$param["col"]["evid_dvs"] = NULL;

if ($public_dvs == "세금계산서") {

    $param["col"]["supply_corp"] = $fb->form("supply_corp");
    $param["col"]["crn"] = $fb->form("crn");
    $param["col"]["repre_name"] = $fb->form("repre_name");
    $param["col"]["addr"] = $fb->form("addr");
    $param["col"]["bc"] = $fb->form("bc");
    $param["col"]["tob"] = $fb->form("tob");

} else if ($public_dvs == "미발행") {

    $param["col"]["public_state"] = "미발행";

} else if ($public_dvs == "현금영수증") {

    $param["col"]["evid_dvs"] = $fb->form("evid_dvs");
}

$param["prk"] = "public_admin_seqno";
$param["prkVal"] = $fb->form("seqno");

$rs = $dao->updateData($conn, $param);

if (!$rs) {
    $check = 0;
}

$conn->CompleteTrans();
$conn->Close();
echo $check;
?>
