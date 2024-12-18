<?php
/**
 * Created by PhpStorm.
 * User: Hyeonsik Cho
 * Date: 2017-05-29
 * Time: 오전 9:31
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

$param['empl_seq'] = $decoded->id;

$rs = $dao->selectWorkList($conn, $param);

$json['result']['code'] = '0000';
$json['result']['value'] = 'success';

$masks = array();
if(array_key_exists('masks',$param))
    $masks = explode('|',$param['masks']);

$i = 0;
$json['info'] = array();
while ($rs && !$rs->EOF) {
    $info = array();
    $info['order']['id'] = $rs->fields['order_num'];
    $info['order']['name'] = $rs->fields['title'];
    $info['category']['id'] = $rs->fields['cate_sortcode'];
    $info['category']['name'] = $rs->fields['order_detail'];

    if(in_array("CUSTOMER", $masks)) {
        $info['customer']['id'] = $rs->fields['member_id'];
        $info['customer']['name'] = $rs->fields['member_name'];
    }

    if(in_array("ITEM_COUNT", $masks)) {
        $info['item_count'] = $rs->fields['count'];
    }

    if(in_array("ORDER_DATE", $masks)) {
        $info['order_date'] = $rs->fields['order_regi_date'];
    }

    if(in_array("ACCEPTOR", $masks)) {
        $info['acceptor'] = $rs->fields['receipt_mng'];
    }

    array_push($json['info'], $info);

    $rs->MoveNext();
}

echo json_encode($json);

?>