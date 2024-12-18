<?php
/**
 * Created by PhpStorm.
 * User: Hyeonsik Cho
 * Date: 2017-09-28
 * Time: 오후 5:18
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
$json['item'] = "[]";
$decoded = JWT::decode($token, acceptorhelper::$KEY, array('HS256'));
$param = array();

foreach($fb->fb as $key=>$value)
{
    $param[$key] = $value;
}

$param['request_method'] = "get_work_list_all_by_count";
$rs = $dao->selectWorkDetailByCount($conn, $param);

$json['result']['code'] = '0000';
$json['result']['value'] = 'succeeded';
$masks = array();

if(array_key_exists('masks',$param))
    $masks = explode('|',$param['masks']);

while ($rs && !$rs->EOF) {
    $info = array();
    $info['order']['id'] = $rs->fields['order_detail_file_num'];
    $info['order']['name'] = $rs->fields['title'];
    $info['category']['id'] = $rs->fields['cate_sortcode'];
    $info['category']['name'] = $rs->fields['cate_name'];
    $info['customer']['id'] = $rs->fields['member_id'];
    $info['customer']['name'] = $rs->fields['member_name'];
    $info['paper_info'] = $rs->fields['paper_name'] . " " . $rs->fields['color'] . " " . $rs->fields['basisweight'];
    $info['size_name'] = $rs->fields['stan_name'];
    $info['color_info']['name'] = $rs->fields['print_tmpt_name'];
    $tmpt_arr = explode(" / ", $rs->fields['print_tmpt_name']);
    $info['color_info']['front'] = substr($tmpt_arr[0], -1);
    $info['color_info']['back'] = substr($tmpt_arr[1], -1);

    if (in_array("DELIVERY_DETAIL", $masks)) {
        $info['delivery_detail']['recipient']['name'] = $rs->fields['receive_name'];
        $info['delivery_detail']['recipient']['phone'] = $rs->fields['cell_num'];
        $info['delivery_detail']['recipient']['address'] = $rs->fields['addr'] . " " . $rs->fields['addr_detail'];

        $info['delivery_detail']['method']['type'] = $rs->fields['dlvr_way'];
        $info['delivery_detail']['method']['option'] = $rs->fields['dlvr_sum_way'];

        $info['delivery_detail']['courier'] = $rs->fields['invo_cpn'];
        $info['delivery_detail']['dcode'] = $rs->fields['invo_num'];
    }

    $info['quantity'] = $rs->fields['amt'];
    $info['result_file_name'] = str_replace("/home/sitemgr/ndrive/attach","",$rs->fields['file_path']) . $rs->fields['save_file_name'];
    $info['side_count'] = ($rs->fields['side_dvs'] == "단면") ? "1" : "2" ;
    $info['accepted_date'] = $rs->fields['receipt_finish_date'];
    $info['acceptor']['id'] = $rs->fields['empl_id'] == null ?  "" : $rs->fields['empl_id'];
    $info['acceptor']['name'] = $rs->fields['empl_name'] == null ?  "" : $rs->fields['empl_name'];
    $info['status_code'] = $rs->fields['state'];

    $json['item'] = $info;
    $rs->MoveNext();
}

echo json_encode($json);

?>