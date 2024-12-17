<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/common_lib/CommonUtil.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/manufacture/print_mng/PrintListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new PrintListDAO();
$util = new CommonUtil();

$check = 1;
$print_op_seqno = $fb->form("seqno");
$state = $util->status2statusCode("인쇄중");

//인쇄 발주서 검색
$param = array();
$param["table"] = "print_op";
$param["col"] = "typset_num, flattyp_dvs 
,aftside_tmpt ,aftside_spc_tmpt ,amt ,amt_unit";
$param["where"]["print_op_seqno"] = $print_op_seqno;

$sel_rs = $dao->selectData($conn, $param);

$flattyp_dvs = $sel_rs->fields["flattyp_dvs"];
$typset_num = $sel_rs->fields["typset_num"];
$aftside_tmpt = $sel_rs->fields["aftside_tmpt"];
$aftside_spc_tmpt = $sel_rs->fields["aftside_spc_tmpt"];
$amt = $sel_rs->fields["amt"];
$amt_unit = $sel_rs->fields["amt_unit"];

$conn->StartTrans();

$param = array();
$param["flattyp_dvs"] = $sel_rs->fields["flattyp_dvs"];
$param["typset_num"] = $sel_rs->fields["typset_num"];
$param["state"] = $state;

$check = $util->changeOrderState($conn, $dao, $param);

//인쇄지시서 상태 변경
$param = array();
$param["table"] = "print_op";
$param["col"]["affil"] = $fb->form("affil");
$param["col"]["size"] = $fb->form("size");
$param["col"]["extnl_brand_seqno"] = $fb->form("extnl_brand_seqno");
$param["col"]["state"] = $state;
$param["prk"] = "print_op_seqno";
$param["prkVal"] = $print_op_seqno;

$rs = $dao->updateData($conn, $param);

if (!$rs) {
    $check = 0;
}

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

//기존 작업일지 유효여부 수정
$param = array();
$param["table"] = "print_work_report";
$param["col"]["work_end_hour"] = date("Y-m-d H:i:s");
$param["col"]["valid_yn"] = "N";
$param["prk"] = "print_op_seqno";
$param["prkVal"] = $print_op_seqno;

$rs = $dao->updateWorkReport($conn, $param);

if (!$rs) {
    $check = 0;
}

//인쇄 작업일지 추가
$param = array();
$param["table"] = "print_work_report";
$param["col"]["worker_memo"] = $fb->form("worker_memo");
$param["col"]["work_start_hour"] = date("Y-m-d H:i:s");
$param["col"]["perform_date"] = date("Y-m-d H:i:s");
$param["col"]["adjust_price"] = $fb->form("adjust_price");
$param["col"]["work_price"] = $fb->form("work_price");
$param["col"]["ink_C"] = $fb->form("ink_C");
$param["col"]["ink_M"] = $fb->form("ink_M");
$param["col"]["ink_Y"] = $fb->form("ink_Y");
$param["col"]["ink_K"] = $fb->form("ink_K");
$param["col"]["subpaper"] = $fb->form("subpaper");
$param["col"]["expec_perform_mark"] = $expec_perform_mark;
$param["col"]["expec_perform_paper"] = $expec_perform_paper;
$param["col"]["expec_perform_bucket"] = $expec_perform_bucket;
$param["col"]["extnl_etprs_seqno"] = $fb->form("extnl_etprs_seqno");
$param["col"]["worker"] = $fb->session("name");
$param["col"]["valid_yn"] = "Y";
$param["col"]["state"] = $state;
$param["col"]["print_op_seqno"] = $print_op_seqno;

$rs = $dao->insertData($conn, $param);

if (!$rs) {
    $check = 0;
}

$conn->CompleteTrans();
$conn->Close();
echo $check;
?>
