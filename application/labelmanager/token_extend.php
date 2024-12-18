<?php
/**
 * Created by PhpStorm.
 * User: Hyeonsik Cho
 * Date: 2017-12-04
 * Time: 오후 7:00
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

$receive_token = $fb->form("token");
$param = array();

$decoded = JWT::decode($receive_token, labelhelper::$KEY, array('HS256'));

$json['result']['code'] = '0000';
$json['result']['value'] = 'succeeded';

$token['id'] = $decoded->id;
$token['iat'] = time();
$token['exp'] = time() + labelhelper::SESSION_VALID_TIME();


$json['token'] = $jwt->encode($token, labelhelper::$KEY);

echo json_encode($json);

?>

