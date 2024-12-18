<?php
/**
 * Created by PhpStorm.
 * User: Hyeonsik Cho
 * Date: 2017-09-29
 * Time: 오후 3:49
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

$decoded = JWT::decode($token, acceptorhelper::$KEY, array('HS256'));
$param = array();

foreach($fb->fb as $key=>$value)
{
    $param[$key] = $value;
}

$order_detail_file_nums = explode('|', $param['item_ids']);
$param['status'] = '3120';
foreach($order_detail_file_nums as $order_detail_file_num) {
    $param['order_detail_file_num'] = $order_detail_file_num;
    $dao->updateWorkByCount($conn, $param);
}

$json['result']['code'] = '0000';
$json['result']['value'] = 'succeeded';

echo json_encode($json);

?>