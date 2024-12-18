<?php
/**
 * Created by PhpStorm.
 * User: Hyeonsik Cho
 * Date: 2017-07-10
 * Time: 오후 3:47
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
$decoded = JWT::decode($token, acceptorhelper::$KEY, array('HS256'));
$param['qc_check_pc'] = $decoded->id;

foreach($fb->fb as $key=>$value)
{
    $param[$key] = $value;
}

$rs = $dao->selectWorkListForQC($conn, $param);
$json['items'] = array();
$update_order = array();

$param['status'] = $update_param['status'] = '1350';

if($param['filters'] == 'ready') {
    $update_param['qc_check_pc'] = $param['qc_check_pc'];
}

$qc_waiting = 0;
while ($rs && !$rs->EOF) {
    $info = array();
    $info['order']['id'] = $rs->fields['order_num'];
    $update_param['order_detail_file_num'] = $rs->fields['order_detail_file_num'];
    $update_param['order_num'] = $rs->fields['order_num'];
    $update_param['order_common_seqno'] = $rs->fields['order_common_seqno'];
    $info['order']['name'] = $rs->fields['title'];
    $info['category']['id'] = $rs->fields['cate_sortcode'];
    $info['category']['name'] = $rs->fields['cate_name'];
    $info['customer']['id'] = $rs->fields['member_id'];
    $info['customer']['name'] = $rs->fields['member_name'];
    $info['side_count'] = ($rs->fields['side_dvs'] == "단면") ? "1" : "2" ;
    $info['item_count'] = $rs->fields['count'];
    $info['item_index'] = $rs->fields['seq'];
    $info['ordered_date'] = $rs->fields['order_regi_date'];
    $filename = $rs->fields['file_path'] . $rs->fields['save_file_name'];
    $info['file_name'] = explode('attach', $filename)[1];
    $update_order['seqno'][$i++] = $rs->fields['order_common_seqno'];

    $info['options'] = array();
    $param['order_common_seqno'] = $rs->fields['order_common_seqno'];
    $rs1 = $dao->selectOptions($conn, $update_param);
    while ($rs1 && !$rs1->EOF) {
        array_push($info['options'],
            $rs1->fields['opt_name'] . "|" .
            $rs1->fields['depth1'] . "|" .
            $rs1->fields['depth2'] . "|" .
            $rs1->fields['depth3'] . "|" .
            $rs1->fields['detail']);
        $rs1->MoveNext();
    }

    array_push($json['items'], $info);

    // 상태 업데이트
    $param['order_num'] = $rs->fields['order_num'];
    //$dao->updateWork($conn, $param);
    $param['request_method'] = "get_work_list_qc";


    $dao->updateWorkByCount($conn, $update_param);

    $rs->MoveNext();
}



echo json_encode($json);

?>