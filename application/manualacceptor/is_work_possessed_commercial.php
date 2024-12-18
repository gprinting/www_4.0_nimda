<?php
/**
 * Created by PhpStorm.
 * User: Hyeonsik Cho
 * Date: 2017-08-23
 * Time: 오전 11:53
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
$json['result']['code'] = '0000';
$json['result']['value'] = 'succeeded';

$param = array();
$param['order_num'] = $fb->form("order_id");
$decoded = JWT::decode($token, acceptorhelper::$KEY, array('HS256'));

$rs = $dao->isWorkPossessedCommercial($conn, $param);
while ($rs && !$rs->EOF) {
    $json['work']['acceptor']['id'] = $rs->fields['empl_id'];
    $json['work']['acceptor']['name'] = $rs->fields['name'];
    $json['work']['status_code'] = $rs->fields['order_state'];
    $rs->MoveNext();
}

echo json_encode($json);

?>