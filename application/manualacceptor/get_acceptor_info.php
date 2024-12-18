<?php
/**
 * Created by PhpStorm.
 * User: Hyeonsik Cho
 * Date: 2017-05-26
 * Time: 오후 6:42
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

$decoded = JWT::decode($token, acceptorhelper::$KEY, array('HS256'));

$param = array();
$param['empl_id'] = $decoded->id;
$param['acceptors'] = $fb->form("acceptors");
$param['status_range'] = $fb->form("status_range");
$param['date_range'] = $fb->form("date_range");
$param['count'] = $fb->form("count");
$param['masks'] = $fb->form("masks");
$param['assigned'] = $fb->form("assigned");

$rs = $dao->selectAcceptorInfo($conn, $param);
$json['result']['code'] = '0000';
$json['result']['value'] = 'succeeded';
$param['request_method'] = "get_acceptor_info";
while ($rs && !$rs->EOF) {
    $json['info']['id'] = $rs->fields['empl_id'];
    $json['info']['name'] = $rs->fields['name'];
    $json['info']['exten_num'] = $rs->fields['exten_num'];
    $json['info']['department_code'] = $rs->fields['high_depar_code'];

    $rs->MoveNext();
}

echo json_encode($json);

?>
