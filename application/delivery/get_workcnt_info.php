<?php
/**
 * Created by PhpStorm.
 * User: Hyeonsik Cho
 * Date: 2017-10-27
 * Time: 오전 9:47
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

$json = array();

$json['cnt_all'] = $dao->selectCntAll($conn, $param);
$json['cnt_delay'] = $dao->selectCntDelay($conn, $param);
$json['cnt_produceyesterday'] = $dao->selectCntProduceYesterday($conn, $param);
$json['cnt_producetoday'] = $dao->selectProduceToday($conn, $param);
$json['cnt_willInwarehouse'] = $dao->selectCntWillInwarehouse($conn, $param);
$json['cnt_parcel'] = $dao->selectCntParcel($conn, $param);

echo json_encode($json);

?>