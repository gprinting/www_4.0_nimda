<?php
/**
 * Created by PhpStorm.
 * User: Hyeonsik Cho
 * Date: 2017-09-14
 * Time: 오전 10:44
 * detail : 묶음요청
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

$bun_dlvr_order_nums = explode('|',$param['bun_dlvr_order_nums']);
$bun_groups = explode('|',$param['bun_groups']);
$time_stamp = strval(ceil(microtime(true) * 1000.0)) . rand(0, 9);
$new_bun_group = $bun_groups[0];

$i = 0;
foreach($bun_dlvr_order_nums as $bun_dlvr_order_num) {
    $param = array();
    $param["new_bun_dlvr_order_num"] = $time_stamp;
    $param["bun_group"] = $bun_groups[$i];
    $param["new_bun_group"] = $new_bun_group;
    $param["bun_dlvr_order_num"] = $bun_dlvr_order_num;

    $rs = $dao->UpdateBunDlvrInfo($conn, $param);

    $i++;
}

$param = array();
$param["table"] = "order_dlvr";
$param["col"] = "order_common_seqno, dlvr_way, zipcode, bun_group";
$param["where"]["bun_dlvr_order_num"] = $time_stamp;
$param["where"]["tsrs_dvs"] = '수신';

$sel_rs = $dao->selectData($conn, $param);

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

$param = array();
$param["table"] = "order_dlvr";
$param["col"]["dlvr_price"] = $dlvr_cost;
$param["col"]["lump_count"] = $lump_count;
$param["col"]["bun_group"] = $bun_group;
$param["prk"] = "bun_dlvr_order_num";
$param["prkVal"] = $time_stamp;

$rs = $dao->updateData($conn, $param);

// 송장재출력을 위한 송장이력 초기화를 해줘야함

$json['result']['code'] = '0000';
$json['result']['value'] = 'succeeded';

echo json_encode($json);
?>