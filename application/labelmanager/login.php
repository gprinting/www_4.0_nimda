<?php
/**
 * Created by PhpStorm.
 * User: Hyeonsik Cho
 * Date: 2017-12-04
 * Time: 오후 6:42
 */

define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . '/com/dprinting/LabelManagerDAO.inc');
include_once($_SERVER["DOCUMENT_ROOT"] . "/application/libraries/JWT.php");
include_once($_SERVER["DOCUMENT_ROOT"] . "/application/helpers/labelhelper.php");
use \Firebase\JWT\JWT;

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$jwt = new JWT();
$dao = new LabelManagerDAO();
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
    $token['exp'] = time() + labelhelper::SESSION_VALID_TIME();

    $json['result']['code'] = '0000';
    $json['result']['value'] = 'succeeded';
    $json['token'] = $jwt->encode($token, labelhelper::$KEY);

    $param['empl_id'] = $empl_id;
    $param['token'] = $json['token'];
    $param['kind'] = 'login';
    $param['access_ip'] = '';
    $param['oper_sys'] = '';
    $dao->insertLoginRecord($conn, $param);
} else {
    $json['result']['code'] = '0003';
    $json['result']['value'] = 'incorrect login info';
}

echo json_encode($json);