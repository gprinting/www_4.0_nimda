<?php
/**
 * Created by PhpStorm.
 * User: Hyeonsik Cho
 * Date: 2017-12-05
 * Time: 오전 10:15
 */

define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . '/com/dprinting/LabelManagerDAO.inc');
include_once($_SERVER["DOCUMENT_ROOT"] . "/application/libraries/JWT.php");
include_once($_SERVER["DOCUMENT_ROOT"] . "/application/helpers/labelhelper.php");
use \Firebase\JWT\JWT;

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$jwt = new JWT();
$dao = new LabelManagerDAO();
$fb = new FormBean();


$token = $fb->form("token");
$json = array();

$decoded = JWT::decode($token, labelhelper::$KEY, array('HS256'));

$rs = $dao->selectAWork($conn);

if($rs->fields['order_detail_file_num'] != null) {
    $json["code"] = "0000";
    $json["value"] = "succeeded";
} else {
    $json["code"] = "0001";
    $json["value"] = "no result";
}
while($rs && !$rs->EOF) {
    $work = array();
    $work['accept']['id'] = $rs->fields['order_detail_file_num'];
    $work['accept']['abbr_id'] = $rs->fields['barcode_num'];
    $work['accept']['name'] = $rs->fields['title'];
    $work['manager']['id'] = $rs->fields['empl_id'] == null ? "" : $rs->fields['empl_id'];
    $work['manager']['name'] = $rs->fields['empl_id'] == null ? "" : $rs->fields['empl_id'];
    $work['plate']['id'] = $rs->fields['typset_num'] == null ? "" : $rs->fields['typset_num'];
    $work['plate']['name'] = $rs->fields['print_title'] == null ? "" : $rs->fields['print_title'];
    $work['label']['id'] = labelhelper::GET_LABELID_BY_SIZE($rs->fields['cut_size_wid'], $rs->fields['cut_size_vert']);
    $work['label']['name'] = "";
    $work['category']['id'] = $rs->fields['sortcode'];
    $work['category']['name'] = $rs->fields['cate_name'];
    $work['channel']['id'] = "GP";
    $work['channel']['name'] = "굿프린팅";
    $work['order_detail'] = $rs->fields['order_detail'];
    $work['finish_info'] = $dao->selectAPInfo($conn, $rs->fields['order_common_seqno']);
    $work['address'] = $rs->fields['addr'] . " " . $rs->fields['addr_detail'];
    $work['quantity'] = $rs->fields['amt'] . $rs->fields['amt_unit_dvs'];
    $work['item_count'] = $rs->fields['count'] . "건(" . $rs->fields['expec_weight'] . "kg)";
    $work['size'] = $rs->fields['cut_size_wid'] . "X" . $rs->fields['cut_size_vert'];
    $work['memo'] = $rs->fields['cust_memo'];
    $work['delivery_info']['method'] = "직7";
    $work['delivery_info']['fill_color'] = "blue";
    $work['result_file_path'] = $rs->fields['preview_file_path'] . "/" . $rs->fields['preview_file_name'];
    $work['preview_file_path'] = "";
    $json['work'] = $work;
    $rs->MoveNext();
}

echo json_encode($json);

?>










