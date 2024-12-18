<?php
/**
 * Created by PhpStorm.
 * User: Hyeonsik Cho
 * Date: 2017-06-01
 * Time: 오전 9:41
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

$decoded = JWT::decode($param['token'], acceptorhelper::$KEY, array('HS256'));

$result_path_create =  $_SERVER["SiteHome"] .
    SITE_NET_DRIVE .
    SITE_DEFAULT_ORDER_DETAIL_COUNT_FILE .
    "/" .
    $util->getYmdDirPath();

if(!is_dir($result_path_create))
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

if(!is_dir($preview_path_create) )
{
    $old = umask(0);
    mkdir($preview_path_create, 0777, true);
    umask($old);
}

// 현재 오토 설정이 on으로 되어있는지 확인
$auto_set_rs = $dao->selectAutoSet($conn, $param);

if($auto_set_rs->fields['exec_yn'] == 'Y') {
    $rs = $dao->selectAworkForAuto($conn, $param);
}

$json['result']['code'] = '0000';
$json['result']['value'] = 'succeeded';

$json['work'] = "{}";

while ($rs && !$rs->EOF) {
    $info = array();
    $info['order']['id'] = $rs->fields['order_num'];
    $info['order']['name'] = $rs->fields['title'];
    $info['category']['id'] = $rs->fields['cate_sortcode'];
    $info['category']['name'] = $rs->fields['cate_name'];
    $info['item_count'] = $rs->fields['count'];
    $info['bleed_size']['width'] = $rs->fields['work_size_wid'];
    $info['bleed_size']['height'] = $rs->fields['work_size_vert'];
    $info['trim_size']['width'] = $rs->fields['cut_size_wid'];
    $info['trim_size']['height'] = $rs->fields['cut_size_vert'];
    $info['size_fit'] = ($rs->fields['stan_name'] == "비규격") ? "0" : "1";
    $info['side_count'] = ($rs->fields['side_dvs'] == "단면") ? "1" : "2";
    $info['color_count'] = $rs->fields['tot_tmpt'];
    $info['options'] = array();//"not implemented";
    $info['marker'] = "not implemented";
    $filename = $rs->fields['file_path'] . $rs->fields['save_file_name'];
    $info['file_name'] = explode('attach', $filename)[1];
    $info['ordered_date'] = $rs->fields['order_regi_date'];

    $info['setting']['last_updated']  = date("Y.m.d H:i:s");
    $param = array();
    $param['order_num'] = $info['order']['id'];
    $param['status'] = "1330";

    $param['request_method'] = "request_auto_work";
    $dao->updateWork($conn, $param);

    //array_push($json['work'], $info);
    $json['work'] = $info;
    $rs->MoveNext();
}

echo json_encode($json);