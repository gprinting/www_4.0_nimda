<?php
/**
 * Created by PhpStorm.
 * User: Hyeonsik Cho
 * Date: 2017-05-31
 * Time: 오후 1:50
 */

define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/common_define/common_config.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . '/com/dprinting/ManualAcceptorDAO.inc');
include_once($_SERVER["INC"] . "/common_lib/CommonUtil.inc");
include_once($_SERVER["DOCUMENT_ROOT"] . "/application/libraries/JWT.php");
include_once($_SERVER["DOCUMENT_ROOT"] . "/application/helpers/acceptorhelper.php");
use \Firebase\JWT\JWT;

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();
$util = new CommonUtil();

$jwt = new JWT();
$dao = new ManualAcceptorDAO();
$fb = new FormBean();

$param = array();
foreach($fb->fb as $key=>$value)
{
    $param[$key] = $value;
}

$json = array();
$decoded = JWT::decode($param['token'], acceptorhelper::$KEY, array('HS256'));
$param['empl_id'] = $decoded->id;
$param['order_num'] = $param['order_id'];
$event = array();
if(array_key_exists('event',$param))
    $event = explode('|',$param['event']);

if (in_array("accept_initiate", $event) && strlen($param['order_id']) != 21) {
    $result_path_create =  $_SERVER["SiteHome"] .
        SITE_NET_DRIVE .
        SITE_DEFAULT_ORDER_DETAIL_COUNT_FILE .
        "/" .
        $util->getYmdDirPath();

    if( !is_dir($result_path_create) )
    {
        $old = umask(0);
        mkdir($result_path_create, 0777, true);
        umask($old);
    }

    $preview_path_create =  $_SERVER["SiteHome"] .
        SITE_NET_DRIVE .
        SITE_DEFAULT_ORDER_DETAIL_COUNT_PREVIEW_FILE .
        "/" .
        $util->getYmdDirPath();

    if(!is_dir($preview_path_create))
    {
        $old = umask(0);
        mkdir($preview_path_create, 0777, true);
        umask($old);
    }

    $result_path = SITE_DEFAULT_ORDER_DETAIL_COUNT_FILE . "/"
        . date("Y/m/d", mktime(0,0,0,date("m")  , date("d"), date("Y")));

    $path_result = $dao->getPrintfilepath($conn, $param['order_num']);

    $update_param = array();
    $update_param['file_path'] = $result_path;
    $update_param['print_file_path'] = $path_result['print_file_path'];
    $update_param['order_detail_dvs_num'] = $path_result['order_detail_dvs_num'];
    $cnt = $path_result['count'];
    for($i = 1; $i <= $cnt ; $i++) {
        $order_detail_file_num = $update_param['order_detail_dvs_num'] . str_pad($i, 2, "0", STR_PAD_LEFT);
        $update_param['save_file_name'] = $order_detail_file_num . ".pdf";
        $update_param['print_file_name'] = $order_detail_file_num . ".pdf";
        $update_param['order_detail_file_num'] = $order_detail_file_num;
        $dao->updateOrderDetailCountFileInfo($conn,$update_param);
    }

    $param['status'] = "1330";
}

if (in_array("accept_pause", $event)) {
    $param['status'] = "1370";
}

if (in_array("accept_resume", $event)) {
    $result_path_create =  $_SERVER["SiteHome"] .
        SITE_NET_DRIVE .
        SITE_DEFAULT_ORDER_DETAIL_COUNT_FILE .
        "/" .
        $util->getYmdDirPath();

    if( !is_dir($result_path_create) )
    {
        $old = umask(0);
        mkdir($result_path_create, 0777, true);
        umask($old);
    }

    $preview_path_create =  $_SERVER["SiteHome"] .
        SITE_NET_DRIVE .
        SITE_DEFAULT_ORDER_DETAIL_COUNT_PREVIEW_FILE .
        "/" .
        $util->getYmdDirPath();

    if( !is_dir($preview_path_create) )
    {
        $old = umask(0);
        mkdir($preview_path_create, 0777, true);
        umask($old);
    }

    $param['status'] = "1330";
}

if (in_array("accept_complete", $event)) {
    $param['status'] = "1360";
    $param['status'] = "2120"; // 임시코드

    $files = explode('|', $param['result_files']);
    foreach($files as $file) {
        // /home/sitemgr/ndrive/attach/order_detail_count_file/2017/10/20/SGPT171016NC004560101.pdf
        $path = $_SERVER["SiteHome"] .
            SITE_NET_DRIVE . "/attach" . $file;

        $arr = explode('/', $path);

        $update_param = array();
        $update_param['save_file_name'] = $update_param['print_file_name'] = array_pop($arr);
        $update_param['file_path'] = $update_param['print_file_path'] =  implode('/', $arr);
        $update_param['order_detail_file_num'] = str_replace(".pdf","",$update_param['save_file_name']);

        $dao->updateOrderDetailCountFileInfo($conn,$update_param);
    }
}

if (in_array("order_cancel", $event)) {
    $param['status'] = "1180";
}

$json['result']['code'] = '0000';
$json['result']['value'] = 'succeeded';

$origin_status = $param['status'];

if(strlen($param['order_id']) == 21) {
    $param['order_num'] = substr($param['order_id'], 1, strlen($param['order_id']) - 5);
}

if($param['status'] == null || strlen($param['order_id']) == 21) {
    $param['status'] = $dao->selectOrderCommonState($conn, $param['order_num']);
}

$order_id = $dao->updateWork($conn, $param);

if(strlen($param['order_id']) == 21) {
    $param['status'] = $origin_status;
    $param['order_num'] = $param['order_id'];
    $dao->updateWorkByCount($conn, $param);
}

$json['work']['order_id'] = $order_id;
$json['work']['status_code'] = $param['status'];

echo json_encode($json);


?>