<?php
/**
 * Created by PhpStorm.
 * User: Hyeonsik Cho
 * Date: 2017-07-10
 * Time: 오후 1:26
 */

define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . '/com/dprinting/ManualAcceptorDAO.inc');
include_once($_SERVER["DOCUMENT_ROOT"] . "/application/libraries/JWT.php");
include_once($_SERVER["DOCUMENT_ROOT"] . "/application/helpers/acceptorhelper.php");
use \Firebase\JWT\JWT;

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$jwt = new JWT();
$dao = new ManualAcceptorDAO();
$fb = new FormBean();

$param = array();
foreach($fb->fb as $key=>$value)
{
    $param[$key] = $value;
}
$param['request_method'] = "request_auto_statistics";
if(in_array("order_date_range",$param)) {
    $date = explode('_', $param['order_date_range']);
    $param['from_date'] = $date[0];
    $param['to_date'] = $date[1];
} else {
    $param['from_date'] = date("Y-m-d");
    $param['to_date'] = date("Y-m-d");
}

$decoded = JWT::decode($param['token'], acceptorhelper::$KEY, array('HS256'));

$json['result']['code'] = '0000';
$json['result']['value'] = 'succeeded';
$json['work_count'] = $dao->selectAutoStatisticsForToday($conn, $param);

echo json_encode($json);

?>