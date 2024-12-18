<?php
/**
 * Created by PhpStorm.
 * User: Hyeonsik Cho
 * Date: 2017-10-10
 * Time: 오전 11:16
 */

define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . '/com/dprinting/DeliveryAppDAO.inc');
include_once(INC_PATH . '/classes/dprinting/PriceCalculator/PriceCalculator.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$dao = new DeliveryAppDAO();
$fb = new FormBean();

$param = array();
$fee_caculator = new DeliveryFeeCalculator();

foreach($fb->fb as $key=>$value)
{
    $param[$key] = $value;
}

$dao->InitBunDlvrInfo($conn, $param);


$sel_param = array();
$sel_param["table"] = "order_dlvr";
$sel_param["col"] = "order_common_seqno, dlvr_way, zipcode, bun_group";
$sel_param["where"]["bun_dlvr_order_num"] = $param['bun_dlvr_order_num'];
$sel_param["where"]["tsrs_dvs"] = '수신';

$sel_rs = $dao->selectData($conn, $sel_param);

$order_common_seqnos = "";
$dlvr_way = "";
$zipcode = "";
$bun_group = "";
while ($sel_rs && !$sel_rs->EOF) {
    $order_common_seqnos .= "|" . $sel_rs->fields['order_common_seqno'];
    $dlvr_way = $sel_rs->fields['dlvr_way'];
    $zipcode = $sel_rs->fields['zipcode'];
    if($bun_group == "")
        $bun_group = $sel_rs->fields['bun_group'];
    $sel_rs->MoveNext();
}

$order_common_seqnos = substr($order_common_seqnos, 1);
$delivery_fee_info = $fee_caculator->getDeliveryFeeInfo($order_common_seqnos, $dlvr_way, $zipcode);

$weight = "";
$dlvr_cost = "";
if($delivery_fee_info['ncBoxCount'] != null) {
    //명함류
    $dlvr_cost = $delivery_fee_info['dlvr_cost_nc'];
    $weight = $delivery_fee_info['weight_namecard'];
    $lump_count = $delivery_fee_info['ncBoxCount'];
} else {
    // 전단류
    $dlvr_cost = $delivery_fee_info['dlvr_cost_bl'];
    $weight = $delivery_fee_info['weight_leaflet'];
    $lump_count = $delivery_fee_info['blBoxCount'];
}

$update_param = array();
$update_param["table"] = "order_dlvr";
$update_param["col"]["dlvr_price"] = $dlvr_cost;
$update_param["col"]["lump_count"] = $lump_count;
$update_param["col"]["bun_group"] = $bun_group;
$update_param["prk"] = "bun_dlvr_order_num";
$update_param["prkVal"] = $param['bun_dlvr_order_num'];

$rs = $dao->updateData($conn, $update_param);

$delivery_fee_info = $fee_caculator->getDeliveryFeeInfo($param['order_common_seqno'], $dlvr_way, $zipcode);

$weight = "";
$dlvr_cost = "";
if($delivery_fee_info['ncBoxCount'] != null) {
    //명함류
    $dlvr_cost = $delivery_fee_info['dlvr_cost_nc'];
    $weight = $delivery_fee_info['weight_namecard'];
    $lump_count = $delivery_fee_info['ncBoxCount'];
} else {
    // 전단류
    $dlvr_cost = $delivery_fee_info['dlvr_cost_bl'];
    $weight = $delivery_fee_info['weight_leaflet'];
    $lump_count = $delivery_fee_info['blBoxCount'];
}

$update_param = array();
$update_param["table"] = "order_dlvr";
$update_param["col"]["dlvr_price"] = $dlvr_cost;
$update_param["col"]["lump_count"] = $lump_count;
$update_param["col"]["bun_group"] = $bun_group;
$update_param["prk"] = "bun_dlvr_order_num";
$update_param["prkVal"] = $param['bun_dlvr_order_num'];

$rs = $dao->updateData($conn, $update_param);

$json['result']['code'] = '0000';
$json['result']['value'] = 'succeeded';

echo json_encode($json);

?>















