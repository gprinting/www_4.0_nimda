<?php
/**
 * Created by PhpStorm.
 * User: Hyeonsik Cho
 * Date: 2017-09-13
 * Time: 오전 10:34
 */

define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . '/com/dprinting/ManualAcceptorDAO.inc');
include_once(INC_PATH . "/define/nimda/cypress_init.inc");
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
$param['table'] = 'paper_op';
$param['col']['typset_num'] = "";
$param['col']['op_size'] = $fb->form("paper_size_1");
$param['col']['stor_size'] = $fb->form("paper_size_2");
$param['col']['grain'] = $fb->form("paper_grain");
$param['col']['op_date'] = date('Y-m-d H:i:s');
$param['col']['amt'] = $fb->form("quantity");
$param['col']['amt_unit'] = $fb->form("장");
$param['col']['memo'] = $fb->form("memo");
$param['col']['warehouser'] = $fb->form("print_house");
$param['col']['extnl_etprs_seqno'] = $fb->form("paper_mill");
$paper_info = explode(" ", $fb->form("paper_info"));
$param['col']['name'] = $paper_info[0];
$param['col']['color'] = $paper_info[1];
$param['col']['basisweight'] = $paper_info[2];

if($dao->insertData($conn, $param)) {
    $json['result']['code'] = '0000';
    $json['result']['value'] = 'succeeded';
    $rs = $dao->selectLastInsertedPaperOrder($conn);
    $json['sorder']['order_id'] = $rs->fields['paper_op_seqno'];
} else {
    $json['result']['code'] = '0001';
    $json['result']['value'] = 'fail';
}


echo json_encode($json);

?>