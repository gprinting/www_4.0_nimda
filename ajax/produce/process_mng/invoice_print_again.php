<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/classes/cjparcel/CJparcel.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");

$fb = new FormBean();
$param = array();

$param["order_detail_num"] = explode('|', $fb->form("order_num"));

$cParcel = new CJparcel();
$isSuccess = $cParcel->printAgain($fb->form("order_num"));

if($isSuccess == true) { // 성공
    echo 1;
} else {
    echo 0;
}

?>