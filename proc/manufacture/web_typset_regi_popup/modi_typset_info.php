<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/manufacture/typset_mng/TypsetListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new TypsetListDAO();
$check = 1;

$order_detail_dvs_num = $fb->form("order_detail_dvs_num");
$page_order_detail_brochure_seqno = $fb->form("page_order_detail_brochure_seqno");
$typset_num = $fb->form("typset_num");
$state_arr = $fb->session("state_arr");
$state = $state_arr["조판중"];

if (!$typset_num) {

    $param = array();
    $param["table"] = "brochure_typset";
    $param["col"] = "COUNT(typset_num) AS cnt";
    $param["blike"]["regi_date"] = date("Y-m-d");

    $typset_cnt = $dao->selectData($conn, $param)->fields["cnt"];

    $typset_cnt = $typset_cnt + 1;

    $param = array();
    $param["table"] = "order_detail_brochure";
    $param["col"] = "order_common_seqno, cate_paper_mpcode";
    $param["where"]["order_detail_dvs_num"] = $order_detail_dvs_num;

    $sel_rs = $dao->selectData($conn, $param);
    $order_common_seqno = $sel_rs->fields["order_common_seqno"];
    $cate_paper_mpcode = $sel_rs->fields["cate_paper_mpcode"];

    $param = array();
    $param["table"] = "cate_paper";
    $param["col"] = "name, dvs, color, basisweight";
    $param["where"]["mpcode"] = $cate_paper_mpcode;

    $sel_rs = $dao->selectData($conn, $param);
    $paper_name = $sel_rs->fields["name"];
    $paper_dvs = $sel_rs->fields["dvs"];
    $paper_color = $sel_rs->fields["color"];
    $paper_basisweight = $sel_rs->fields["basisweight"];

    $param = array();
    $param["table"] = "order_common";
    $param["col"] = "oper_sys, cate_sortcode";
    $param["where"]["order_common_seqno"] = $order_common_seqno;

    $sel_rs = $dao->selectData($conn, $param);
    $oper_sys = $sel_rs->fields["oper_sys"];
    $cate_sortcode = $sel_rs->fields["cate_sortcode"];
}

$conn->StartTrans();

$param = array();
$param["table"] = "brochure_typset";
if (!$typset_num) {
    $param["col"]["typset_num"] = "APM" . date("ymd") . str_pad($typset_cnt,"3","0",STR_PAD_LEFT);;
    $param["col"]["paper_name"] = $paper_name;
    $param["col"]["paper_dvs"] = $paper_dvs;
    $param["col"]["paper_color"] = $paper_color;
    $param["col"]["paper_basisweight"] = $paper_basisweight;
    $param["col"]["cate_sortcode"] = $cate_sortcode;
    $param["col"]["state"] = $state;
    $param["col"]["oper_sys"] = $oper_sys;
}

$beforeside_tmpt = $fb->form("beforeside_tmpt");
$beforeside_spc_tmpt = $fb->form("beforeside_spc_tmpt");
$aftside_tmpt = $fb->form("aftside_tmpt");
$aftside_spc_tmpt = $fb->form("aftside_spc_tmpt");
$tot_tmpt = $beforeside_tmpt + $beforeside_spc_tmpt + $aftside_tmpt + $aftside_spc_tmpt;

$param["col"]["regi_date"] = date("Y-m-d H:i:s");
$param["col"]["beforeside_tmpt"] = $beforeside_tmpt;
$param["col"]["beforeside_spc_tmpt"] = $beforeside_spc_tmpt;
$param["col"]["aftside_tmpt"] = $aftside_tmpt;
$param["col"]["aftside_spc_tmpt"] = $aftside_spc_tmpt;
$param["col"]["honggak_yn"] = $fb->form("honggak_yn");
$param["col"]["dlvrboard"] = $fb->form("dlvrboard");
$param["col"]["memo"] = $fb->form("memo");
$param["col"]["affil"] = $fb->form("affil");
$param["col"]["subpaper"] = $fb->form("subpaper");
$param["col"]["wid_size"] = $fb->form("wid_size");
$param["col"]["vert_size"] = $fb->form("vert_size");
$param["col"]["print_amt"] = $fb->form("print_amt");
$param["col"]["print_amt_unit"] = "장";
$param["col"]["prdt_page"] = $fb->form("prdt_page");
$param["col"]["prdt_page_dvs"] = $fb->form("prdt_page_dvs");
$param["col"]["after_list"] = $fb->form("after_list");
$param["col"]["opt_list"] = $fb->form("opt_list");
$param["col"]["specialty_items"] = $fb->form("specialty_items");
$param["col"]["empl_seqno"] = $fb->session("empl_seqno");
$param["col"]["op_typ"] = "자동발주";
$param["col"]["op_typ_detail"] = "자동생성";

if ($typset_num) {

    $param["prk"] = "typset_num";
    $param["prkVal"] = $typset_num;

    $rs = $dao->updateData($conn, $param);

    if (!$rs) {
        $check = 0;
    }

} else {

    $rs = $dao->insertData($conn, $param);

    if (!$rs) {
        $check = 0;
    }

    $brochure_typset_seqno = $conn->insert_ID();

    $param = array();
    $param["table"] = "page_order_detail_brochure";
    $param["col"]["brochure_typset_seqno"] = $brochure_typset_seqno;
    $param["prk"] = "page_order_detail_brochure_seqno";
    $param["prkVal"] = $page_order_detail_brochure_seqno;

    $rs = $dao->updateData($conn, $param);

    if (!$rs) {
        $check = 0;
    }

    $param = array();
    $param["table"] = "brochure_typset";
    $param["col"] = "typset_num";
    $param["where"]["brochure_typset_seqno"] = $brochure_typset_seqno;

    $typset_num = $dao->selectData($conn, $param)->fields["typset_num"];

    $param = array();
    $param["table"] = "output_op";
    $param["col"]["typset_num"] = $typset_num;
    $param["col"]["subpaper"] = $fb->form("subpaper");
    $param["col"]["amt"] = $tot_tmpt;
    $param["col"]["state"] = $state_arr["출력준비"];
    $param["col"]["flattyp_dvs"] = "N";
    $param["col"]["typ"] = "자동발주";
    $param["col"]["typ_detail"] = "자동생성";

    $rs = $dao->insertData($conn, $param);

    if (!$rs) {
        $check = 0;
    }

    $param = array();
    $param["table"] = "print_op";
    $param["col"]["typset_num"] = $typset_num;
    $param["col"]["state"] = $state_arr["인쇄준비"];
    $param["col"]["beforeside_tmpt"] = $beforeside_tmpt;
    $param["col"]["beforeside_spc_tmpt"] = $beforeside_spc_tmpt;
    $param["col"]["aftside_tmpt"] = $aftside_tmpt;
    $param["col"]["aftside_spc_tmpt"] = $aftside_spc_tmpt;
    $param["col"]["subpaper"] = $fb->form("subpaper");
    $param["col"]["tot_tmpt"] = $tot_tmpt;
    $param["col"]["amt"] = $fb->form("print_amt");
    $param["col"]["amt_unit"] = "장";
    $param["col"]["flattyp_dvs"] = "N";
    $param["col"]["typ"] = "자동발주";
    $param["col"]["typ_detail"] = "자동생성";

    $rs = $dao->insertData($conn, $param);

    if (!$rs) {
        $check = 0;
    }

    $param = array();
    $param["table"] = "order_after_history";
    $param["col"] = "after_name, depth1, depth2, depth3";
    $param["where"]["order_detail_dvs_num"] = $order_detail_dvs_num;
    $param["where"]["basic_yn"] = "Y";

    $sel_rs = $dao->selectData($conn, $param);

    while ($sel_rs && !$sel_rs->EOF) {
        $param = array();
        $param["table"] = "basic_after_op";
        $param["col"]["typset_num"] = $typset_num;
        $param["col"]["state"] = $state_arr["조판후공정준비"];
        $param["col"]["flattyp_dvs"] = "N";
        $param["col"]["op_typ"] = "자동발주";
        $param["col"]["op_typ_detail"] = "자동생성";
        $param["col"]["amt"] = $fb->form("print_amt");
        $param["col"]["amt_unit"] = "장";
        $param["col"]["after_name"] = $sel_rs->fields["after_name"];
        $param["col"]["depth1"] = $sel_rs->fields["depth1"];
        $param["col"]["depth2"] = $sel_rs->fields["depth2"];
        $param["col"]["depth3"] = $sel_rs->fields["depth3"];

        $rs = $dao->insertData($conn, $param);

        if (!$rs) {
            $check = 0;
        }
        $sel_rs->moveNext();
    }
}

$param = array();
$param["table"] = "after_op";
$param["col"]["specialty_items"] = $fb->form("specialty_items");
$param["prk"] = "order_detail_dvs_num";
$param["prkVal"] = $order_detail_dvs_num;

$rs = $dao->updateData($conn, $param);

if (!$rs) {
    $check = 0;
}

$param = array();
$param["table"] = "produce_process_flow";
$param["col"]["typset_save_yn"] = "Y";
$param["col"]["output_yn"] = "Y";
$param["col"]["print_yn"] = "Y";
$param["col"]["typset_num"] = $typset_num;

$rs = $dao->insertData($conn, $param);

if (!$rs) {
    $check = 0;
}

$conn->CompleteTrans();
$conn->Close();
echo $check;
?>
