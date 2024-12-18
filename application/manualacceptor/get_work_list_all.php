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

$decoded = JWT::decode($token, acceptorhelper::$KEY, array('HS256'));
$param = array();

foreach($fb->fb as $key=>$value)
{
    $param[$key] = $value;
}

$param['empl_id'] = $decoded->id;
$param['request_method'] = "get_work_list_all";
$rs = $dao->selectWorkListForAll($conn, $param);

$json['result']['code'] = '0000';
$json['result']['value'] = 'succeeded';

$masks = array();
if(array_key_exists('masks',$param))
    $masks = explode('|',$param['masks']);

$i = 0;
$json['works'] = array();
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

    if(in_array("PRICE", $masks)) {
        $info['price'] = $rs->fields['pay_price'];
    }

    if(in_array("ORDERED_DATE", $masks)) {
        $info['ordered_date'] = $rs->fields['order_regi_date'];
    }

    if(in_array("ACCEPTED_DATE", $masks)) {
        $info['accepted_date'] = $rs->fields['receipt_finish_date'];
    }

    if(in_array("STATUS_CODE", $masks)) {
        $info['status_code'] = $rs->fields['order_state'];
    }

    if(in_array("ACCEPTOR", $masks)) {
        $info['acceptor']['id'] = $rs->fields['receipt_mng'] == null ?  "" : $rs->fields['receipt_mng'];
        $info['acceptor']['name'] = $rs->fields['empl_name'] == null ?  "" : $rs->fields['empl_name'];
    }

    if (in_array("OPTIONS", $masks)) {
        $info['options'] = array();
        $param['order_common_seqno'] = $rs->fields['order_common_seqno'];
        $rs2 = $dao->selectOptions($conn, $param);
        while ($rs2 && !$rs2->EOF) {
            array_push($info['options'],
                $rs2->fields['opt_name'] . "|" .
                $rs2->fields['depth1'] . "|" .
                $rs2->fields['depth2'] . "|" .
                $rs2->fields['depth3'] . "|" .
                $rs2->fields['detail']) ;
            $rs2->MoveNext();
        }
    }

    if(in_array("TARGET_RPATH", $masks)) {
        if($rs->fields['typset_way'] == "CYPRESS") {
            $info['target_rpath'] = "cypress path";
        } else if($rs->fields['typset_way'] == "OUTSOURCE"){
            $info['target_rpath'] = "outsource path";
        } else {
            $info['target_rpath'] = "";
        }
    }

    $update_order['seqno'][$i++] = $rs->fields['order_common_seqno'];
    array_push($json['works'], $info);
    $rs->MoveNext();
}

if($param['assigned'] == "1") {
    $dao->updateManualWorking($conn, $update_order);
}

echo json_encode($json);

?>