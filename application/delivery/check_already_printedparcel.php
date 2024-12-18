<?php
/**
 * Created by PhpStorm.
 * User: Hyeonsik Cho
 * Date: 2017-09-18
 * Time: 오후 5:35
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
$json['result'] = $dao->SelectAlreadyPrintedParcels($conn, $param);

echo json_encode($json);

?>