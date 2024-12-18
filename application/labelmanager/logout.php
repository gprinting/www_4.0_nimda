<?php
/**
 * Created by PhpStorm.
 * User: Hyeonsik Cho
 * Date: 2017-12-04
 * Time: 오후 6:56
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


$token = $fb->form("token");
$json = array();
$decoded = "";

$decoded = JWT::decode($token, labelhelper::$KEY, array('HS256'));

$param = array();
$param['empl_id'] = $decoded->id;
$param['kind'] = "logout";
$param['token'] = $token;
$param['access_ip'] = $fb->form("access_ip");;
$param['oper_sys'] = $fb->form("oper_sys");;


$dao->insertLoginRecord($conn, $param);

$json['result']['code'] = '0000';
$json['result']['value'] = 'succeeded';

echo json_encode($json);
?>