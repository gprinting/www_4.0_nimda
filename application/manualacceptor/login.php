<?php
/**
 * Created by PhpStorm.
 * User: HyeonsikCho
 * Date: 2017-05-23
 * Time: 오후 6:09
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

$json = array();

if(array_key_exists('token',$param)) {
    $empl_id = $dao->checkIDAndToken($conn, $param);
} else {
    $empl_id = $dao->checkIDAndPassword($conn, $param);
}

if($empl_id !== false) {
    $token['id'] = $empl_id;

    $token['iat'] = time();
    $token['exp'] = time() + acceptorhelper::SESSION_VALID_TIME();

    $json['result']['code'] = '0000';
    $json['result']['value'] = 'succeeded';
    $json['token'] = $jwt->encode($token, acceptorhelper::$KEY);

    $param['empl_id'] = $empl_id;
    $param['token'] = $json['token'];
    $param['kind'] = 'login';
    $dao->insertLoginRecord($conn, $param);
} else {
    $json['result']['code'] = '0003';
    $json['result']['value'] = 'incorrect login info';
}

echo json_encode($json);