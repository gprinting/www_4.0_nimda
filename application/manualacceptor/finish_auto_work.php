<?php
/**
 * Created by PhpStorm.
 * User: Hyeonsik Cho
 * Date: 2017-06-01
 * Time: 오전 10:53
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
$param['request_method'] = "finish_auto_work";
$json = array();
$decoded = JWT::decode($param['token'], acceptorhelper::$KEY, array('HS256'));

$param['order_num'] = $param['order_id'];

if($param['result'] == "1") {
    // 오토접수 성공
    $param['status'] = '1335';
    $dao->updateWork($conn, $param);
} else if($param['result'] == "0") {
    // 오토접수 실패
    $param['status'] = '1320';
    $param['receipt_dvs'] = 'Manual';
    $dao->updateWork($conn, $param);
}

$json['result']['code'] = '0000';
$json['result']['value'] = 'succeeded';

echo json_encode($json);

?>