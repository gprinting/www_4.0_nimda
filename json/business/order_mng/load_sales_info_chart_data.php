<?
/*
 * Copyright (c) 2017 Nexmotion, Inc.
 * All rights reserved.
 *
 * 매출거래현황정보 차트용 json data 생성
 * 데이터 기준은 시작 연월, 작년동월, -1월, -2월, -3월
 *
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2017/05/10 엄준현 생성
 *=============================================================================
 */
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/business/order_mng/OrderMngDAO.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/nimda/ErpCommonUtil.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new OrderMngDAO();
$util = new ErpCommonUtil();

$fb = $fb->getForm();

$member_seqno = $fb["seqno"];
$from_arr = explode('-', $fb["from"]);

$date_arr = $util->getDateRangeArr($from_arr[0], $from_arr[1]);
$date_arr_count = count($date_arr);

$dvs_arr= array();
$dvs_arr[] = "sum_oa";
$dvs_arr[] = "sum_net";
$dvs_arr[] = "sum_sale";
$dvs_arr[] = "sum_depo";
$dvs_arr[] = "year_sum_net";
$dvs_arr_count = count($dvs_arr);

$outer_form = "\"%s\" : %s";

$data_form  = '{';
$data_form .=  "\"categories\" : [%s]";
$data_form .= ",\"data\"       : [%s]";
$data_form .= '}';

$param = array();
$param["member_seqno"] = $member_seqno;

$result_arr = array();
for ($i = 0; $i < $date_arr_count; $i++) {
    $param["from"] = $date_arr[$i]["from"];
    $param["to"]   = $date_arr[$i]["to"];

    for ($j = 0; $j < $dvs_arr_count; $j++) {
        $dvs = $dvs_arr[$j];

        $rs = $dao->selectDaySalesStatsEach($conn, $param, $dvs);

        $result_arr[$dvs][substr($date_arr[$i]["from"], 0, -3)] = $rs;
    }
}
unset($rs);

$json = '';
foreach($result_arr as $dvs => $data_arr) {
    $cat_str = '';
    $data_str = '';
    foreach($data_arr as $cat => $data) {
        $cat_str .= '"' . $cat . '",';
        $data_str .= intval($data) . ',';
    }

    $cat_str = substr($cat_str, 0, -1);
    $data_str = substr($data_str, 0, -1);
    $data_json = sprintf($data_form, $cat_str, $data_str);

    $json .= sprintf($outer_form, $dvs, $data_json);
    $json .= ',';
}

$json = '{' . substr($json, 0, -1) . '}';

echo $json;

$conn->Close();
exit;
?>
