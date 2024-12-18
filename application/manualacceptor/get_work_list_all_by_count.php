<?php
/**
 * Created by PhpStorm.
 * User: Hyeonsik Cho
 * Date: 2017-09-28
 * Time: 오후 4:24
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

$param['request_method'] = "get_work_list_all_by_count";

$rs = $dao->selectWorkListForAllByCount($conn, $param);

$json['result']['code'] = '0000';
$json['result']['value'] = 'succeeded';

$i = 0;
$json['items'] = array();
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
    if($info['size_name'] == "비규격")
        $info['size_name'] .= "(" . $rs->fields['cut_size_wid'] . "*" . $rs->fields['cut_size_vert'] . ")";
    $info['color_info']['name'] = $rs->fields['print_tmpt_name'];
    $tmpt_arr = explode(" / ", $rs->fields['print_tmpt_name']);
    $info['color_info']['front'] = substr($tmpt_arr[0], -1);
    $info['color_info']['back'] = substr($tmpt_arr[1], -1);
    $info['quantity'] = $rs->fields['amt'];
    $info['result_file_name'] = str_replace("/home/sitemgr/ndrive/attach","",$rs->fields['file_path']) . "/" . $rs->fields['save_file_name'];
    $info['side_count'] = ($rs->fields['side_dvs'] == "단면") ? "1" : "2";
    $info['accepted_date'] = $rs->fields['receipt_finish_date'];
    $info['acceptor']['id'] = $rs->fields['empl_id'] == null ?  "" : $rs->fields['empl_id'];
    $info['acceptor']['name'] = $rs->fields['empl_name'] == null ?  "" : $rs->fields['empl_name'];
    $info['status_code'] = $rs->fields['state'];

    array_push($json['items'], $info);
    $rs->MoveNext();
}

echo json_encode($json);

?>