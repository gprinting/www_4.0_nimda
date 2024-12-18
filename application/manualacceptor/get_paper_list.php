<?php
/**
 * Created by PhpStorm.
 * User: Hyeonsik Cho
 * Date: 2017-08-23
 * Time: 오후 6:10
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
$decoded = JWT::decode($token, acceptorhelper::$KEY, array('HS256'));

$rs = $dao->selectPaperList($conn);

$json = array();
$json['result']['code'] = '0000';
$json['result']['value'] = 'succeeded';

$json['papers'] = array();

while($rs && !$rs->EOF) {
    array_push($json['papers'], $rs->fields['paper']);
    $rs->MoveNext();
}

echo json_encode($json);

?>