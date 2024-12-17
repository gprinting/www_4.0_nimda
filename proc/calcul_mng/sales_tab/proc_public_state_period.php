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

$now_state = $fb->form("now_state");
$new_state = $fb->form("new_state");
$is_card = "선입금";
if($new_state == "카드건별")
{
    $new_state = "미발행";
    $is_card = "카드";
}


//선택계산서상태변경
$param = array();
$param["new_dvs"] = $fb->form("new_state");
$param["member_seqno"] = $fb->form("member_seqno");
$param["year"] = $fb->form("year");
$param["mon"] = $fb->form("month");
$param["public_dvs"] = $fb->form("public_dvs");
$param["dvs_detail"] = $fb->form("dvs_detail");
$param["pay_way"] = $is_card;

if($now_state == "미발행") {
    $param["public_state"] = "대기";
}

if($now_state == "현금영수증" || $now_state == "미발행" || $now_state == "세금계산서") {
    $rs = $dao->updatePublicStateNew($conn, $param);
}

if($is_card) {

}


if (!$rs) {
    $check = 0;
}

$conn->CompleteTrans();
$conn->Close();
echo $check;
?>
