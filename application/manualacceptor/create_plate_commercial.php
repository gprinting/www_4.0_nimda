<?php
/**
 * Created by PhpStorm.
 * User: Hyeonsik Cho
 * Date: 2017-08-23
 * Time: 오후 3:34
 */

define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . '/com/dprinting/ManualAcceptorDAO.inc');
include_once(INC_PATH . "/define/nimda/cypress_init.inc");
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
$decoded = "";

$decoded = JWT::decode($token, acceptorhelper::$KEY, array('HS256'));
// ($rs->fields['side_dvs'] == "단면") ? "1" : "2" ;

$param = array();
$param["table"] = "sheet_typset";
$param['col']['dlvrboard'] = $fb->form("type");
$param['col']['paper_name'] = $fb->form("paper_name");
$param['col']['paper_color'] = $fb->form("paper_color");
$param['col']['size'] = $fb->form("plate_size");
$param['col']['beforeside_tmpt'] = $fb->form("front_side_color_count");
$param['col']['aftside_tmpt'] = $fb->form("back_side_color_count");
$param['col']['honggak_yn'] = ($fb->form("honggak") == "1") ? "Y" : "N";
$param['col']['print_amt'] = $fb->form("quantity");
$param['col']['print_etprs'] = $fb->form("print_house");
$param['col']['memo'] = $fb->form("memo");
if($fb->form("print_method") == "0") {
    if($fb->form("back_side_color_count") > 0) {
        $tmpt = "양면";
    } else {
        $tmpt = "단면";
    }
} else if($fb->form("print_method") == "1") {
    $tmpt = "돈땡";
} else if($fb->form("print_method") == "2") {
    $tmpt = "구와이돈땡";
} else if($fb->form("print_method") == "3") {
    $tmpt = "홍각";
}
$tmpt .= ($fb->form("front_side_color_count") + $fb->form("back_side_color_count")) . "도";
$param['col']['print_title'] = $fb->form("plate_size") . "_" . $tmpt . "_" . $fb->form("print_house");
$typset_num = $dao->makeNewTypsetNum($conn, $param);
$param['col']['typset_num'] = $typset_num;
$param['col']['state'] = "2120";
$param['col']['after_list'] = "";
$param['col']['opt_list'] = "";
$param['col']['beforeside_spc_tmpt'] = "0";
$param['col']['aftside_spc_tmpt'] = "0";
$param['col']['print_amt_unit'] = "장";
$param['col']['prdt_page'] = "2";
$param['col']['prdt_page_dvs'] = "낱장";
$param['col']['op_typ'] = "수동발주";
$param['col']['op_typ_detail'] = "자동생성";
$param['col']['empl_seqno'] = $dao->getEmplSeqno($conn, $decoded->id);
$param['col']['paper_dvs'] = "-";
$param['col']['save_path'] = date('Y') . "/" . date('m') . "/" . date('d') . "/" . substr($typset_num, 0, strlen($typset_num) - 3) . "/" . $typset_num;
$save_path = $param['col']['save_path'];
$param['col']['specialty_items'] = "";
$param['col']['typset_way'] = "COMMERCIAL";

$rs = $dao->insertData($conn, $param);

if($rs) {
    $json['result']['code'] = '0000';
    $json['result']['value'] = 'succeeded';
    $json['plate']['id'] = $param['col']['typset_num'];

    // 경로생성
    $printfile_path = _WEB_FILE_ENZINE_PRINTFILE . "/" . $save_path;
    if(!is_dir($printfile_path)) {
        $old = umask(0);
        mkdir($printfile_path, 0777, true);
        umask($old);
    }

    //TEST
    $template_name = "수정-2005봉투-대봉투1소봉2개_횡결_레자크.cdr";
    $template_source = _WEB_FILE_ENZINE_TEMPLATE . "/"  . $template_name;
    $template_dest = $printfile_path . "/" . $template_name;
    copy($template_source, $template_dest);

    $param = array();
    $param['order_detail_file_nums'] = explode("|",$fb->form("item_ids"));
    $param['typset_num'] = $typset_num;

    $dao->updateTypsetCompleteByCount($conn, $param);

    $param['status'] = '2120';
    foreach($param['order_detail_file_nums'] as $order_detail_file_num) {
        $param['order_detail_file_num'] = $order_detail_file_num;

        $select_param = array();
        $select_param['table'] = 'order_detail_count_file';
        $select_param['col'] = 'save_file_name, file_path ';
        $select_param["where"]["order_detail_file_num"] = $order_detail_file_num;

        $select_result = $dao->selectData($conn, $select_param);

        $source = $select_result->fields["file_path"] . "/" . $select_result->fields["save_file_name"];
        $dest = $printfile_path . "/" . $select_result->fields["save_file_name"];
        copy($source, $dest);

        $dao->updateWorkByCount($conn, $param);
    }

    //$json['plate']['save_path'] = $printfile_path;

    //$paper_info = explode(' ',$fb->form("paper_name"));
    // 생성된 판에 대한 종이발주
    $param = array();
    $param['table'] = 'paper_op';
    $param['col']['typset_num'] = $typset_num;
    $param['col']['op_size'] = $fb->form("plate_size");
    $param['col']['stor_size'] = $fb->form("plate_size");
    $param['col']['grain'] = $fb->form("paper_grain");
    if($fb->form("paper_order") == "1") {
        $param['col']['op_date'] = date('Y-m-d H:i:s');
    }
    $param['col']['amt'] = $fb->form("quantity");
    $param['col']['amt_unit'] = $fb->form("장");
    $param['col']['memo'] = $fb->form("memo");
    $param['col']['warehouser'] = $fb->form("print_house");
    //$etprs_rs = $dao->getPaperEtprs($conn,$fb->form("paper_mill"));
    $param['col']['extnl_etprs_seqno'] = $fb->form("paper_mill");
    $paper_info = explode(" ", $fb->form("paper_name"));
    $param['col']['name'] = $paper_info[0];
    $param['col']['color'] = $paper_info[1];
    $param['col']['basisweight'] = $paper_info[2];

    $dao->insertData($conn, $param);
}

echo json_encode($json);

?>