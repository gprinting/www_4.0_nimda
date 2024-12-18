<?php
/**
 * Created by PhpStorm.
 * User: Hyeonsik Cho
 * Date: 2017-12-05
 * Time: 오후 4:12
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

$decoded = JWT::decode($token, labelhelper::$KEY, array('HS256'));

$json = array();
$param = array();
foreach($fb->fb as $key=>$value)
{
    $param[$key] = $value;
}

$dao->update($conn, $param);

?>