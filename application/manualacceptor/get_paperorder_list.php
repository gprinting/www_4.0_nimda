<?php
/**
 * Created by PhpStorm.
 * User: Hyeonsik Cho
 * Date: 2017-09-04
 * Time: 오후 2:19
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

$rs = $dao->selectPaperorderList($conn, $param);

$json['result']['code'] = '0000';
$json['result']['value'] = 'succeeded';

$json['sorders'] = array();
while ($rs && !$rs->EOF) {
    $info = array();
    $info['order_id'] = $rs->fields['paper_op_seqno'];
    $info['paper_mill'] = $rs->fields['extnl_etprs_seqno'];
    $info['paper_info'] = $rs->fields['name'] . " " . $rs->fields['color'] . " " . $rs->fields['basisweight'];
    $info['paper_size_1'] = $rs->fields['op_size'];
    $info['paper_size_2'] = $rs->fields['stor_size'];
    $info['paper_grain'] = $rs->fields['grain'];
    $info['quantity'] = $rs->fields['amt'];
    $info['print_house'] = $rs->fields['warehouser'];
    $info['memo'] = $rs->fields['memo'];
    $info['sequence'] = $rs->fields['op_degree'];
    array_push($json['sorders'], $info);
    $rs->MoveNext();
}

echo json_encode($json);

?>