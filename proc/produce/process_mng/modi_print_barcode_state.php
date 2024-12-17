<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/common_lib/CommonUtil.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/produce/typset_mng/ProcessMngDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new ProcessMngDAO();
$util = new CommonUtil();

$check = 1;
$typset_num = $fb->form("typset_num");

//인쇄 발주서 검색
$param = array();
$param["table"] = "print_op";
$param["col"] = "flattyp_dvs, print_op_seqno, state
,aftside_tmpt ,aftside_spc_tmpt, beforeside_tmpt ,beforeside_spc_tmpt ,amt ,amt_unit, extnl_brand_seqno";
$param["where"]["typset_num"] = $typset_num;

$sel_rs = $dao->selectData($conn, $param);

$print_op_seqno = $sel_rs->fields["print_op_seqno"];
$flattyp_dvs = $sel_rs->fields["flattyp_dvs"];
$beforeside_tmpt = $sel_rs->fields["beforeside_tmpt"];
$beforeside_spc_tmpt = $sel_rs->fields["beforeside_spc_tmpt"];
$aftside_tmpt = $sel_rs->fields["aftside_tmpt"];
$aftside_spc_tmpt = $sel_rs->fields["aftside_spc_tmpt"];
$amt = $sel_rs->fields["amt"];
$amt_unit = $sel_rs->fields["amt_unit"];
$tot_tmpt = $beforeside_tmpt + $beforeside_spc_tmpt + $aftside_tmpt + $aftside_spc_tmpt;

$param = array();
$param["table"] = "extnl_brand";
$param["col"] = "extnl_etprs_seqno";
$param["where"]["extnl_brand_seqno"] = $sel_rs->fields["extnl_brand_seqno"];

$extnl_etprs_seqno = $dao->selectData($conn, $param)->fields["extnl_etprs_seqno"];

$param = array();
$param["table"] = "print";
$param["col"] = "basic_price";
$param["where"]["extnl_brand_seqno"] = $fb->form("extnl_brand_seqno");
$param["where"]["search_check"] = $fb->form("print_name") . "|" . $fb->form("size");
$param["where"]["crtr_unit"] = $fb->form("amt_unit");

$rs = $dao->selectData($conn, $param);

$basic_price = $rs->fields["basic_price"];
$price = number_format(intVal($basic_price) * intVal($fb->form("amt")));

$expec_perform_mark = "2";

if (($aftside_tmpt == 0 || 
     $aftside_tmpt == NULL || 
     $aftside_tmpt == "") && 
    ($aftside_spc_tmpt == 0 || 
     $aftside_spc_tmpt == NULL || 
     $aftside_spc_tmpt == "")) {

    $expec_perform_mark = "1";
}

$expec_perform_paper = $amt;
if ($amt_unit == "장") {

    $expec_perform_paper = intVal($amt) / 500;
}
$subpaper = str_replace("절", "", $fb->form("subpaper"));
$expec_perform_bucket = $subpaper * 500 * $expec_perform_mark * $expec_perform_paper;

$conn->StartTrans();

if ($sel_rs->fields["state"] == $util->status2statusCode("인쇄대기")) {
    $state = $util->status2statusCode("인쇄중");

    //인쇄 작업일지 추가
    $param = array();
    $param["table"] = "print_work_report";
    $param["col"]["worker_memo"] = "바코드 처리";
    $param["col"]["work_start_hour"] = date("Y-m-d H:i:s");
    $param["col"]["perform_date"] = date("Y-m-d H:i:s");
    $param["col"]["work_price"] = $price;
    $param["col"]["adjust_price"] = 0;
    $param["col"]["ink_C"] = $tot_tmpt;
    $param["col"]["ink_M"] = $tot_tmpt;
    $param["col"]["ink_Y"] = $tot_tmpt;
    $param["col"]["ink_K"] = $tot_tmpt;
    $param["col"]["subpaper"] = $subpaper;
    $param["col"]["expec_perform_mark"] = $expec_perform_mark;
    $param["col"]["expec_perform_paper"] = $expec_perform_paper;
    $param["col"]["expec_perform_bucket"] = $expec_perform_bucket;
    $param["col"]["extnl_etprs_seqno"] = $extnl_etprs_seqno;
    $param["col"]["worker"] = $fb->session("name");
    $param["col"]["valid_yn"] = "Y";
    $param["col"]["state"] = $state;
    $param["col"]["print_op_seqno"] = $print_op_seqno;

    $rs = $dao->insertData($conn, $param);

    if (!$rs) {
        $check = 0;
    }

} else if ($sel_rs->fields["state"] == $util->status2statusCode("인쇄중")) {
    $state = $util->status2statusCode("조판후공정대기");

    $param = array();
    $param["table"] = "basic_after_op";
    $param["col"]["state"] = $state;
    $param["prk"] = "typset_num";
    $param["prkVal"] = $typset_num;

    $rs = $dao->updateData($conn, $param);

    if (!$rs) {
        $check = 0;
    }

    //기존 작업일지 유효여부 수정
    $param = array();
    $param["table"] = "print_work_report";
    $param["col"]["work_end_hour"] = date("Y-m-d H:i:s");
    $param["col"]["state"] = $state;
    $param["prk"] = "print_op_seqno";
    $param["prkVal"] = $print_op_seqno;

    $rs = $dao->updateWorkReport($conn, $param);

    if (!$rs) {
        $check = 0;
    }
} else {
    echo "2";
    exit;
}

//변경된 상태값 적용
$param = array();
$param["table"] = "print_op";
$param["col"]["state"] = $state;
$param["prk"] = "print_op_seqno";
$param["prkVal"] = $print_op_seqno;

$rs = $dao->updateData($conn, $param);

if (!$rs) {
    $check = 0;
}

$param = array();
$param["flattyp_dvs"] = $sel_rs->fields["flattyp_dvs"];
$param["typset_num"] = $typset_num;
$param["state"] = $state;

$check = $util->changeOrderState($conn, $dao, $param);

$conn->CompleteTrans();
$conn->Close();
echo $check;
?>
