<?php
/**
 * Created by PhpStorm.
 * User: Hyeonsik Cho
 * Date: 2017-08-23
 * Time: 오전 11:15
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

$rs = $dao->selectCommercialPlateWatingList($conn, $param);

$json['items'] = array();

while ($rs && !$rs->EOF) {
    $info = array();
    $info['order']['id'] = $rs->fields['order_detail_file_num'];
    $info['order']['name'] = $rs->fields['title'];
    $info['category']['id'] = $rs->fields['sortcode'];
    $info['category']['name'] = $rs->fields['cate_name'];
    $info['customer']['id'] = $rs->fields['member_id'];
    $info['customer']['name'] = $rs->fields['member_name'];
    $info['size_name'] = $rs->fields['stan_name'];
    $info['side_count'] = ($rs->fields['side_dvs'] == "단면") ? "1" : "2" ;

    $info['color_info']['name'] = $rs->fields['print_tmpt_name'];
    $tmpt_arr = explode(" / ", $rs->fields['print_tmpt_name']);
    $info['color_info']['front'] = substr($tmpt_arr[0], -1);
    $info['color_info']['back'] = substr($tmpt_arr[1], -1);

    $info['quantity'] = $rs->fields['amt'];
    $info['acceptor_memo'] = $rs->fields['produce_memo'];
    if($rs->fields['amt_unit_dvs'] == "R") {
        $info['quantity'] .= "R";
    }
    if($rs->fields['amt_unit_dvs'] == "권") {
        $info['quantity'] .= "V";
    }
    $info['paper_info'] = $rs->fields['name'] . " " . $rs->fields['color'] . " " . $rs->fields['basisweight'];
    $info['ordered_date'] = $rs->fields['order_regi_date'];
    $info['accepted_date'] = $rs->fields['receipt_finish_date'];
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
            $rs1->fields['detail']);
        $rs1->MoveNext();
    }

    array_push($json['items'], $info);
    $rs->MoveNext();
}

echo json_encode($json);

?>




