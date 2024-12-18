<?php
/**
 * Created by PhpStorm.
 * User: Hyeonsik Cho
 * Date: 2017-08-23
 * Time: 오후 1:56
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

$param = array();
foreach($fb->fb as $key=>$value)
{
    $param[$key] = $value;
}
$rs = $dao->getPlateListForCommercial($conn,$param);

$json = array();
$json['result']['code'] = '0000';
$json['result']['value'] = 'succeeded';

$json['plates'] = array();
while ($rs && !$rs->EOF) {
    $info = array();
    $info['id'] = $rs->fields['typset_num'];
    $info['item_count'] = $rs->fields['cnt'];
    $info['status_code'] = $rs->fields['state'];
    $info['filename'] = $rs->fields['origin_file_name'];
    $info['created_date'] = $rs->fields['regi_date'];

    array_push($json['plates'], $info);
    $rs->MoveNext();
}

echo json_encode($json);

?>


