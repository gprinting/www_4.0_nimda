<?php
/**
 * Created by PhpStorm.
 * User: Hyeonsik Cho
 * Date: 2017-08-22
 * Time: 오후 8:03
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

$param['empl_id'] = $decoded->id;
$param['request_method'] = "get_work_list_manual_commercial";
$rs = $dao->selectWorkListForManualCommercial($conn, $param);

$json['result']['code'] = '0000';
$json['result']['value'] = 'succeeded';

$i = 0;
$json['works'] = array();
$update_order = array();

while ($rs && !$rs->EOF) {
    $info = array();
    $info['order']['id'] = $rs->fields['order_num'];
    $update_order[$i]['order_num'] = $rs->fields['order_num'];
    $info['order']['name'] = $rs->fields['title'];
    $info['category']['id'] = $rs->fields['cate_sortcode'];
    $info['category']['name'] = $rs->fields['cate_name'];
    $info['customer']['id'] = $rs->fields['member_id'];
    $info['customer']['name'] = $rs->fields['member_name'];
    $info['side_count'] = ($rs->fields['side_dvs'] == "단면") ? "1" : "2" ;
    $info['tied_order_ids'] = array();
    if($rs->fields['onefile_etprs_yn'] == "O") {
        $one_file_param['order_num'] = $rs->fields['order_num'];
        $info['tied_order_ids'] = $dao->selectOneFileOrderNum($conn, $one_file_param);
    }
    $info['file_info']['source'] = $rs->fields['file_upload_dvs'] == "Y" ? "0" : "1";
    $info['file_info']['name'] = str_replace("/home/sitemgr/front/attach","",$rs->fields['file_path']) . $rs->fields['save_file_name'];
    $info['file_info']['dp_image'] = $rs->fields['owncompany_img_num'];
    $info['item_count'] = $rs->fields['count'];
    $info['status_code'] = $rs->fields['order_state'];
    $info['ordered_date'] = $rs->fields['order_regi_date'];
    $info['price'] = $rs->fields['pay_price'];
    $update_order['seqno'][$i++] = $rs->fields['order_common_seqno'];

    $info['options'] = array();
    $param['order_common_seqno'] = $rs->fields['order_common_seqno'];
    $rs1 = $dao->selectOptions($conn, $param);
    while ($rs1 && !$rs1->EOF) {
        array_push($info['options'],
            $rs1->fields['opt_name'] . "|" .
            $rs1->fields['depth1'] . "|" .
            $rs1->fields['depth2'] . "|" .
            $rs1->fields['depth3'] . "|" .
            $rs1->fields['detail']) ;
        $rs1->MoveNext();
    }

    array_push($json['works'], $info);

    $rs->MoveNext();
}

$update_order['empl_id'] = $param['empl_id'];

$dao->updateManualWorking($conn, $update_order);

echo json_encode($json);

?>