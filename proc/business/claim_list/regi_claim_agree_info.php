<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/business/claim_mng/ClaimListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new ClaimListDAO();
$check = 1;

$conn->StartTrans();
$order_claim_seqno = $fb->form("seqno");

$claim_status = $fb->form("claim_status");
$agree_yn = "";
if ($claim_status == "합의") {
    $agree_yn = "Y";
} else if ($claim_status == "처리중") {
    $agree_yn = "N";
}

$param = array();
$param["table"] = "order_claim"; 
$param["col"]["accident_cause"]         = $fb->form("accident_cause");
$param["col"]["accident_type"]          = $fb->form("accident_type");
$param["col"]["occur_price"]            = str_replace(",", "", $fb->form("occur_price"));
$param["col"]["outsource_burden_price"] = str_replace(",", "", $fb->form("outsource_burden_price"));
$param["col"]["cust_burden_price"]      = str_replace(",", "", $fb->form("cust_burden_price"));
$param["col"]["agree_yn"]               = $agree_yn;
$param["col"]["state"]                  = $claim_status;
$param["col"]["deal_date"]              = $fb->form("deal_date");
$param["col"]["resp_dvs"]               = $fb->form("resp_dvs");
$param["prk"]    = "order_claim_seqno";
$param["prkVal"] = $order_claim_seqno;

$rs = $dao->updateData($conn, $param);

if (!$rs) {
    $check = 0;
}

//정산관리 개발 후 추가 개발 필요

$conn->CompleteTrans();
$conn->Close();
echo $check;
?>
