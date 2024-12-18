<?php
/**
 * Created by PhpStorm.
 * User: Hyeonsik Cho
 * Date: 2017-09-04
 * Time: 오후 6:13
 */

define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . '/com/dprinting/DeliveryAppDAO.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$dao = new DeliveryAppDAO();
$fb = new FormBean();

$param = array();

foreach($fb->fb as $key=>$value)
{
    $param[$key] = $value;
}

$param['enddate'] .= " 23:59:59";

$deliveryway_depth = explode("/", $param['deliveryway']);

if($deliveryway_depth[0] == "택배") {
    $param['deliveryway_depth1'] = "01";
    $param['deliveryway_depth2'] = $deliveryway_depth[1];
} else if($deliveryway_depth[0] == "직배") {
    $param['deliveryway_depth1'] = "02";
    $param['deliveryway_depth2'] = $deliveryway_depth[1];
} else if($deliveryway_depth[0] == "퀵") {
    $param['deliveryway_depth1'] = "03";
} else if($deliveryway_depth[0] == "화물") {
    $param['deliveryway_depth1'] = "04";
} else if($deliveryway_depth[0] == "필방") {
    $param['deliveryway_depth1'] = "05";
} else if($deliveryway_depth[0] == "인방") {
    $param['deliveryway_depth1'] = "06";
} else {
    $param['deliveryway_depth1'] = "";
}


if($param['isincludedafterprocess'] == '전체') {
    $param['isincludedafterprocess'] = "";
} else if($param['isincludedafterprocess'] == '있음') {
    $param['isincludedafterprocess'] = "Y";
} else if($param['isincludedafterprocess'] == '없음') {
    $param['isincludedafterprocess'] = "N";
}

if($param['Istodaydelivery'] == '전체') {
    $param['Istodaydelivery'] = "";
} else if($param['Istodaydelivery'] == '당일판') {
    $param['Istodaydelivery'] = "Y";
} else if($param['Istodaydelivery'] == '당일판아님') {
    $param['Istodaydelivery'] = "N";
}

$json = $dao->selectDeliveryList($conn, $param);
//$json['cnt_all'] = $dao->selectCntAll($conn, $param);
echo json_encode($json);

?>