<?php
/**
 * Created by PhpStorm.
 * User: Hyeonsik Cho
 * Date: 2017-08-23
 * Time: 오후 2:28
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
$decoded = JWT::decode($token, acceptorhelper::$KEY, array('HS256'));

$param = array();
$param['typset_num'] = $fb->form("plate_id");
$rs = $dao->getPlateListForCommercial($conn,$param);

$json = array();
$json['result']['code'] = '0000';
$json['result']['value'] = 'succeeded';

while ($rs && !$rs->EOF) {
    $info = array();
    $info['id'] = $rs->fields['typset_num'];
    $info['item_count'] = $rs->fields['cnt'];
    $info['status_code'] = $rs->fields['state'];
    $info['filename'] = $rs->fields['origin_file_name'];
    $info['created_date'] = $rs->fields['regi_date'];
    $info['plate_type'] = $rs->fields['dlvrboard'];
    $info['plate_size'] = $rs->fields['size'];
    $info['plate_paper'] = $rs->fields['paper_name'] . " " . $rs->fields['paper_color'] . " " . $rs->fields['paper_basisweight'];

    if($rs->fields['aftside_tmpt'] > 0) {
        $plate_color = "양면";
    } else {
        $plate_color = "단면";
    }
    $plate_color .= ($rs->fields['beforeside_tmpt'] + $rs->fields['aftside_tmpt']) . "도";
    $info['print_count'] = $rs->fields['print_amt'] . $rs->fields['amt_unit'];
    $info['output_office'] = "자사출력실";
    $info['print_house'] = $rs->fields['print_etprs'];
    $info['plate_color'] = $plate_color;
    $info['plate_memo'] = $rs->fields['memo'];
    $info['imposer']['id'] = $rs->fields['empl_id'];
    $info['imposer']['name'] = $rs->fields['name'];
    $info['plate_folder_path'] = $rs->fields['save_path'];

    $param['sheet_typset_seqno'] = $rs->fields['sheet_typset_seqno'];
    $rs1 = $dao->selectItemsInPlate($conn, $param);

    $items = array();
    while ($rs1 && !$rs1->EOF) {
        $item = array();
        $item['order']['id'] = $rs1->fields['order_num'];
        $item['order']['name'] = $rs1->fields['title'];
        $item['category']['id'] = $rs1->fields['cate_sortcode'];
        $item['category']['name'] = $rs1->fields['cate_name'];
        $item['customer']['id'] = $rs1->fields['member_id'];
        $item['customer']['name'] = $rs1->fields['member_name'];
        $item['size_name'] = $rs1->fields['stan_name'];
        $item['acceptor_memo'] = $rs1->fields['cust_memo'];
        $item['side_count'] = ($rs1->fields['side_dvs'] == "단면") ? "1" : "2" ;

        $info['color_info']['name'] = $rs->fields['print_tmpt_name'];
        $tmpt_arr = explode(" / ", $rs->fields['print_tmpt_name']);
        $info['color_info']['front'] = substr($tmpt_arr[0], -1);
        $info['color_info']['back'] = substr($tmpt_arr[1], -1);

        $item['quantity'] = $rs1->fields['amt'];
        $item['paper_info'] = $rs1->fields['name'] . " " . $rs1->fields['color'] . " " . $rs1->fields['basisweight'];

        array_push($items, $item);

        $rs1->MoveNext();
    }
    $info['items'] = $items;
    $json['plate'] = $info;
    $rs->MoveNext();
}

echo json_encode($json);

?>

