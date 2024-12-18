<?php
/**
 * Created by PhpStorm.
 * User: Hyeonsik Cho
 * Date: 2017-07-10
 * Time: 오전 10:47
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
$param['request_method'] = "finish_qc_work";
$decoded = JWT::decode($param['token'], acceptorhelper::$KEY, array('HS256'));

$item_ids = explode("|", $param['item_ids']);

if($param['result'] == "1") {
    // QC - 성공처리
    $param['status'] = '1360';
} else if($param['result'] == "0") {
    // QC - 실패처리
    $param['status'] = '1320';
    $param['receipt_dvs'] = 'Manual';
}

foreach($item_ids as $item_id) {
    $param['order_detail_file_num'] = $item_id;
    $dao->updateWorkByCount($conn, $param);
}

$json['result']['code'] = '0000';
$json['result']['value'] = 'succeeded';

echo json_encode($json);
?>