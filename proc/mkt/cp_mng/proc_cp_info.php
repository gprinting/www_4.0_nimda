<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/define/nimda/common_config.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . '/com/nexmotion/job/nimda/mkt/mkt_mng/CpMngDAO.inc');
include_once(INC_PATH . "/com/nexmotion/job/nimda/file/FileAttachDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();
$conn->StartTrans();

$fb = new FormBean();
$cpDAO = new CpMngDAO();
$fileDAO = new FileAttachDAO();

$check = 1;

$param = array();
$param["table"] = "coupon";
$param["col"]["name"] = $fb->form("pop_cp_name");
$param["col"]["cpn_admin_seqno"] = 1;
$param["col"]["coupon_kind"] = $fb->form("coupon_kind");

if($fb->form("coupon_kind") == "input_coupon") {
    $param["col"]["coupon_number"] = $fb->form("coupon_number_1") . "-" .$fb->form("coupon_number_2") . "-" .$fb->form("coupon_number_3");
    $param["col"]["is_released"] = "Y";
}


$discount_rate = array();
$discount_rate["sale_dvs"] = $fb->form("sale_dvs");
//할인 구분 요율 일때
if ($fb->form("sale_dvs") == "%") {
    $discount_rate["per_val"] = $fb->form("per_val");
    $discount_rate["max_discount_price"] = $fb->form("max_discount_price");
} else {
    $discount_rate["won_val"] = $fb->form("won_val");
    $discount_rate["min_order_price"] = $fb->form("min_order_price");
}

$param["col"]["discount_rate"] = json_encode($discount_rate);

//발행일
$param["col"]["release_date"] = $fb->form("release_date");
$param["col"]["expired_date"] = $fb->form("expired_date");

//카테고리 중분류
$cateArr = array();
$cateArr = $fb->form('cate_sortcode');
$param["col"]["categories"] = json_encode($cateArr);
$cp_seqno = $fb->form("coupon_seqno");

//쿠폰 수정
if (empty($cp_seqno) === false) {

    $param["prk"] = "coupon_seqno";
    $param["prkVal"] = $cp_seqno;

    $result = $cpDAO->updateData($conn, $param);
} else {
    //쿠폰 추가
    $result = $cpDAO->insertData($conn, $param);
    $cp_seqno = $conn->insert_ID();
}

if ($check == 1) {
    echo "1";
} else {
    echo "2";
}
$conn->CompleteTrans();
$conn->close();

?>

