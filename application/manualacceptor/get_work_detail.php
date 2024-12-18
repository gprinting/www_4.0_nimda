<?php
/**
 * Created by PhpStorm.
 * User: Hyeonsik Cho
 * Date: 2017-05-31
 * Time: 오전 9:42
 */

define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/common_define/common_config.inc");
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

$param = array();
foreach($fb->fb as $key=>$value)
{
    $param[$key] = $value;
}

$decoded = JWT::decode($param['token'], acceptorhelper::$KEY, array('HS256'));
$param['request_method'] = "get_work_detail";
$param['order_num'] = $param['order_id'];
$rs = $dao->selectWorkDetail($conn, $param);
$json['result']['code'] = '0000';
$json['result']['value'] = 'succeeded';

$json['work'] = "";
$masks = array();

if(array_key_exists('masks',$param))
    $masks = explode('|',$param['masks']);

while ($rs && !$rs->EOF) {
    $info = array();
    $info['order']['id'] = $rs->fields['order_num'];
    $info['order']['name'] = $rs->fields['title'];
    $info['category']['id'] = $rs->fields['cate_sortcode'];
    $info['category']['name'] = $rs->fields['cate_name'];

    if($rs->fields['page_amt'] > 2) {
        $info['booklet_info']['page_count'] = $rs->fields['page_amt'];
        $info['booklet_info']['cover'] = "구현예정";
        $info['booklet_info']['page'] = "구현예정";
    }

    if (in_array("CUSTOMER", $masks)) {
        $info['customer']['id'] = $rs->fields['member_id'];
        $info['customer']['name'] = $rs->fields['member_name'];
    }

    if (in_array("ITEM_COUNT", $masks)) {
        $info['item_count'] = $rs->fields['count'];
    }

    if (in_array("ORDERED_DATE", $masks)) {
        $info['ordered_date'] = $rs->fields['order_regi_date'];
    }

    if (in_array("IMPOSITION_TYPE", $masks)) {
        $info['imposition_type'] = $rs->fields['typset_way'];
    }

    if (in_array("ACCEPTED_DATE", $masks)) {
        $info['accepted_date'] = $rs->fields['receipt_finish_date'];
    }

    if (in_array("PAPER_INFO", $masks)) {
        $info['paper_info'] = $rs->fields['name'] . " " . $rs->fields['color'] . " " . $rs->fields['basisweight'];
    }

    if (in_array("SIZE_INFO", $masks)) {
        //$info['paper_info'] = $rs->fields['name'] . " " . $rs->fields['color'] . " " . $rs->fields['basisweight'];
        $info['size_info']['name'] = $rs->fields['stan_name'];
        $info['size_info']['fit'] = ($rs->fields['stan_name'] == "비규격") ? "0" : "1";
        $info['size_info']['bleed']['width'] = $rs->fields['work_size_wid'];
        $info['size_info']['bleed']['height'] = $rs->fields['work_size_vert'];
        $info['size_info']['trim']['width'] = $rs->fields['cut_size_wid'];
        $info['size_info']['trim']['height'] = $rs->fields['cut_size_vert'];
    }

    /*
    if (in_array("BLEED_SIZE", $masks)) {
        $info['bleed_size']['width'] = $rs->fields['work_size_wid'];
        $info['bleed_size']['height'] = $rs->fields['work_size_vert'];
    }

    if (in_array("TRIM_SIZE", $masks)) {
        $info['trim_size']['width'] = $rs->fields['cut_size_wid'];
        $info['trim_size']['height'] = $rs->fields['cut_size_vert'];
    }

    if (in_array("SIZE_FIT", $masks)) {
        $info['size_fit'] = ($rs->fields['stan_name'] == "비규격") ? "0" : "1";
    }
    */
    if (in_array("SIDE_COUNT", $masks)) {
        $info['side_count'] = ($rs->fields['side_dvs'] == "단면") ? "1" : "2";
    }

    if (in_array("COLOR_INFO", $masks)){
        $info['color_info']['name'] = $rs->fields['print_tmpt_name'];
        $tmpt_arr = explode(" / ", $rs->fields['print_tmpt_name']);
        if(strpos($rs->fields['order_num'],'MT') !== false ) {
            $info['color_info']['front'] = "1";
            $info['color_info']['back'] = "0";
        } else {
            $info['color_info']['front'] = substr($tmpt_arr[0], -1);
            $info['color_info']['back'] = substr($tmpt_arr[1], -1);
        }
    }

    if (in_array("QUANTITY", $masks)){
        $info['quantity'] = (int)$rs->fields['amt'];

        if($rs->fields['amt_unit_dvs'] == "R") {
            $info['quantity'] .= "R";
        }

        if($rs->fields['amt_unit_dvs'] == "권") {
            $info['quantity'] .= "V";
        }

        if($rs->fields['amt_unit_dvs'] == "부") {
            $info['quantity'] .= "C";
        }
    }

    if(in_array("DELIVERY_INFO", $masks)) {
        $info['delivery_info']['type'] = $rs->fields['dlvr_way'];
        $info['delivery_info']['option'] = $rs->fields['dlvr_sum_way'];
    }

    if (in_array("CUSTOMER_MEMO", $masks)) {
        $info['customer_memo'] = $rs->fields['cust_memo'];
    }

    if (in_array("ACCEPTOR_MEMO", $masks)) {
        $info['acceptor_memo'] = $rs->fields['receipt_memo'];
    }

    if (in_array("FILE_INFO", $masks)) {
        $info['file_info']['source'] = $rs->fields['file_upload_dvs'] == "Y" ? "0" : "1";
        $filename = str_replace("sitemgr/attach","sitemgr/front/attach",$rs->fields['file_path']) . $rs->fields['save_file_name'];
        $info['file_info']['name'] = explode('attach', $filename)[1];
        $info['file_info']['dp_image'] = $rs->fields['owncompany_img_num'];
    }

    if (in_array("ACCEPTOR_MEMO", $masks)) {
        $info['acceptor_memo'] = $rs->fields['receipt_memo'];
    }

    if (in_array("AUTO_RESULT", $masks)) {
        $info['auto_result'] = array();
    }

    if (in_array("RESULT_FOLDER", $masks)) {
        $result_path = SITE_DEFAULT_ORDER_DETAIL_COUNT_FILE . "/"
            . date("Y/m/d", mktime(0,0,0,date("m")  , date("d"), date("Y")));

        $info['result_folder'] = explode('attach',$result_path)[1];
    }

    if (in_array("RESULT_FILES", $masks)) {
        $result_path = SITE_DEFAULT_ORDER_DETAIL_COUNT_FILE . "/"
            . date("Y/m/d", mktime(0,0,0,date("m")  , date("d"), date("Y")));

        $result_folder = explode('attach',$result_path)[1];

        // 구현예정
        $info['result_files'] = array();
        $detail_files_rs = $dao->selectOrderDetailFileNums($conn, $param);
        while ($detail_files_rs && !$detail_files_rs->EOF) {
            if($detail_files_rs->fields['save_file_name'] == null) {
                array_push($info['result_files'], "");
            } else {
                array_push($info['result_files'], str_replace("/home/sitemgr/ndrive/attach","",$detail_files_rs->fields['file_path']) . "/" . $detail_files_rs->fields['save_file_name']);
            }
            $detail_files_rs->MoveNext();
        }
    }

    if (in_array("PREVIEW_FOLDER", $masks)) {
        $preview_path = SITE_DEFAULT_ORDER_DETAIL_COUNT_PREVIEW_FILE . "/"
            . date("Y/m/d", mktime(0,0,0,date("m")  , date("d"), date("Y")));

        $info['preview_folder'] = explode('attach',$preview_path)[1];
    }

    if (in_array("FINISHES", $masks)) {
        $info['finishes'] = array();
        $param['order_common_seqno'] = $rs->fields['order_common_seqno'];
        $rs1 = $dao->selectAfterProcesses($conn, $param);
        $after = array();
        while ($rs1 && !$rs1->EOF) {
            array_push($info['finishes'],
                $rs1->fields['after_name'] . "|" .
                $rs1->fields['depth1'] . "|" .
                $rs1->fields['depth2'] . "|" .
                $rs1->fields['depth3'] . "|" .
                $rs1->fields['detail']) ;
            $rs1->MoveNext();
        }
        if(count($after) > 0)
            array_push($info['finishes'], $after);
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

    if (in_array("ARCHIVES_FOLDER", $masks)) {
        // 구현예정
        $info['archives_folder'] = "not implemented";
    }

    if (in_array("PLATE_INFO", $masks)) {
        $info['plate_info'] = $dao->selectTypsetNumFromOrderNum($conn, $param);
    }

    if (in_array("ACCEPTOR", $masks)) {
        $empl['id'] = $rs->fields['receipt_mng'];
        $empl['name'] = $rs->fields['empl_name'];
        $info['acceptor'] = $empl;
    }

    if (in_array("STATUS_CODE", $masks)) {
        $info['status_code'] = $rs->fields['order_state'];
    }

    $json['work'] = $info;

    $result_path = SITE_DEFAULT_ORDER_DETAIL_COUNT_FILE . "/"
        . date("Y/m/d", mktime(0,0,0,date("m")  , date("d"), date("Y")));

    $rs->MoveNext();
}

echo json_encode($json);

?>



