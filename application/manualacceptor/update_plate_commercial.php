<?php
/**
 * Created by PhpStorm.
 * User: Hyeonsik Cho
 * Date: 2017-08-23
 * Time: 오후 7:41
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

$json = array();
$json['result']['code'] = '0000';
$json['result']['value'] = 'succeeded';

$rs = $dao->selectTypsetPath($conn, $fb->form("plate_id"));
if($fb->form("event") == "complete_plate") {
    $param = array();
    $param['table'] = 'sheet_typset_file';
    $param['col']['file_path'] = "/home/sitemgr/ndrive/typeset/typset_file/" . $rs->fields['save_path'];
    $param['col']['save_file_name'] = $fb->form("plate_id") . ".pdf";
    $param['col']['origin_file_name'] = $fb->form("plate_id") . ".pdf";
    $param['col']['sheet_typset_seqno'] = $rs->fields["sheet_typset_seqno"];

    $dao->insertData($conn, $param);
    $json['file_path_to_upload'] = "/typeset/typset_file/" . $rs->fields['save_path'];

    // 판 2120 -> 2220
    $param = array();
    $param['table'] = 'sheet_typset';
    $param['col']['state'] = '2220';
    $param["prk"] = "typset_num";
    $param["prkVal"] = $fb->form('plate_id');

    $dao->updateData($conn, $param);

    // 조판완료 낱건 2130 -> 2220
    $count_file_rs = $dao->selectOrderDetailCountFileNums($conn, $rs->fields["sheet_typset_seqno"]);
    $update_param = array();
    $update_param['status'] = '2220';
    while ($count_file_rs && !$count_file_rs->EOF) {
        $update_param['order_detail_file_num'] = $count_file_rs->fields['order_detail_file_num'];
        $dao->updateWorkByCount($conn, $update_param);
        $count_file_rs->MoveNext();
    }
} else if($fb->form("event") == "cancel_plate") {

    $param = array();
    $param['plate_id'] = $fb->form("plate_id");
    $rs = $dao->selectOrderDetailFileNumFromTypsetNum($conn, $param);

    $param['status'] = "2120";
    while($rs && !$rs->EOF) {
        $param['order_detail_file_num'] = $rs->fields['order_detail_file_num'];
        $dao->updateWorkByCount($conn, $param);
        $rs->MoveNext();
    }

    $cancelled_items = explode('|',$fb->form("canceled_item_ids"));

    //판깨기
    //1. sheet_typset의 state -> 1180
    //2. amt_order_detail_sheet 테이블의 sheet_typset_seqno -> null, state -> 2120
    $dao->cancelPlate($conn, $param);



    //3. order_detail_count_file 테이블의 state -> 2120



} else if($fb->form("event") == "pause_plate") {

} else if($fb->form("event") == "start_imposition") {
    // 판 2120 -> 2130
    $param = array();
    $param['table'] = 'sheet_typset';
    $param['col']['state'] = '2130';
    $param["prk"] = "typset_num";
    $param["prkVal"] = $fb->form('plate_id');

    $dao->updateData($conn, $param);

    // 조판완료 낱건 2130 -> 2130
    $count_file_rs = $dao->selectOrderDetailCountFileNums($conn, $rs->fields["sheet_typset_seqno"]);
    $update_param = array();
    $update_param['status'] = '2130';
    while ($count_file_rs && !$count_file_rs->EOF) {
        $update_param['order_detail_file_num'] = $count_file_rs->fields['order_detail_file_num'];
        $dao->updateWorkByCount($conn, $update_param);
        $count_file_rs->MoveNext();
    }

} else if($fb->form("event") == "stop_imposition") {
    // 판 2120 -> 2220
    $param = array();
    $param['table'] = 'sheet_typset';
    $param['col']['state'] = '2120';
    $param['col']['empl_seqno'] = NULL;
    $param["prk"] = "typset_num";
    $param["prkVal"] = $fb->form('plate_id');

    $dao->updateData($conn, $param);

    // 조판완료 낱건 2130 -> 2220
    $count_file_rs = $dao->selectOrderDetailCountFileNums($conn, $rs->fields["sheet_typset_seqno"]);
    $update_param = array();
    $update_param['status'] = '2120';
    while ($count_file_rs && !$count_file_rs->EOF) {
        $update_param['order_detail_file_num'] = $count_file_rs->fields['order_detail_file_num'];
        $dao->updateWorkByCount($conn, $update_param);
        $count_file_rs->MoveNext();
    }
}

echo json_encode($json);