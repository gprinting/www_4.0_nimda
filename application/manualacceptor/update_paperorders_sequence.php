<?php
/**
 * Created by PhpStorm.
 * User: Hyeonsik Cho
 * Date: 2017-09-11
 * Time: 오후 7:21
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

$token = $fb->form("token");
$json = array();
$decoded = "";
try {
    $decoded = JWT::decode($token, acceptorhelper::$KEY, array('HS256'));
} catch(\Firebase\JWT\ExpiredException $e) {
    $json['result'] = 'fail';
    echo json_encode($json);
}

$param = array();

foreach($fb->fb as $key=>$value)
{
    $param[$key] = $value;
}

if($dao->updatePaperOrdersSequence($conn, $param)) {
    $json['result']['code'] = '0000';
    $json['result']['value'] = 'success';
} else {
    $json['result']['code'] = '0001';
    $json['result']['value'] = 'fail';
}

echo json_encode($json);
?>