<?php
/**
 * Created by PhpStorm.
 * User: Hyeonsik Cho
 * Date: 2017-10-13
 * Time: 오후 2:28
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

if($param['SelectedDeliveryWay'] == "택배") {
    $param['SelectedDeliveryWay'] = "01";
} else if($param['SelectedDeliveryWay'] == "직배") {
    $param['SelectedDeliveryWay'] = "02";
} else if($param['SelectedDeliveryWay'] == "화물") {
    $param['SelectedDeliveryWay'] = "03";
} else if($param['SelectedDeliveryWay'] == "퀵") {
    $param['SelectedDeliveryWay'] = "04";
} else if($param['SelectedDeliveryWay'] == "인방") {
    $param['SelectedDeliveryWay'] = "06";
} else if($param['SelectedDeliveryWay'] == "필방") {
    $param['SelectedDeliveryWay'] = "07";
}

$json = $dao->UpdateDlvrInfo($conn, $param);


?>

