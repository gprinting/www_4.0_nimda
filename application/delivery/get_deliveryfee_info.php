<?php
/**
 * Created by PhpStorm.
 * User: Hyeonsik Cho
 * Date: 2017-10-30
 * Time: 오전 10:46
 */

define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/classes/dprinting/PriceCalculator/PriceCalculator.inc");

$fb = new FormBean();
$param = array();
foreach($fb->fb as $key=>$value)
{
    $param[$key] = $value;
}

if($param['dlvr_way'] == "택배") {
    $param['dlvr_way'] = "01";
} else if($param['dlvr_way'] == "직배") {
    $param['dlvr_way'] = "02";
} else if($param['dlvr_way'] == "화물") {
    $param['dlvr_way'] = "03";
} else if($param['dlvr_way'] == "퀵") {
    $param['dlvr_way'] = "04";
} else if($param['dlvr_way'] == "인방") {
    $param['dlvr_way'] = "06";
} else if($param['dlvr_way'] == "필방") {
    $param['dlvr_way'] = "07";
}

$calculator = new DeliveryFeeCalculator();
$delivery_fee_info = $calculator->getDeliveryFeeInfo($param['order_common_seqnos'], $param['dlvr_way'], $param['zipcode']);


echo json_encode($delivery_fee_info);

?>
