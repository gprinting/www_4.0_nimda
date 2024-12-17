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
$print_op_seqno = $fb->form("seqno");
$state = $util->status2statusCode("조판후공정대기");

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
$param["table"] = "basic_after_op";
$param["col"]["state"] = $state;
$param["prk"] = "typset_num";
$param["prkVal"] = $sel_rs->fields["typset_num"];

$rs = $dao->updateData($conn, $param);

if (!$rs) {
    $check = 0;
}

$param = array();
$param["flattyp_dvs"] = $sel_rs->fields["flattyp_dvs"];
$param["typset_num"] = $sel_rs->fields["typset_num"];
$param["state"] = $state;

$check = $util->changeOrderState($conn, $dao, $param);

//인쇄지시서 상태 변경
$param = array();
$param["table"] = "print_op";
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

$param = array();
$param["table"] = "print_work_report";
$param["col"] = "worker_memo ,work_start_hour, worker
, perform_date, ink_C, ink_M, ink_Y, ink_K, subpaper
, adjust_price, work_price, extnl_etprs_seqno";
$param["where"]["print_op_seqno"] = $print_op_seqno;
$param["where"]["valid_yn"] = "Y";

$rs = $dao->selectData($conn, $param);

$worker_memo = $rs->fields["worker_memo"];
$work_start_hour = $rs->fields["work_start_hour"];
$perform_date = $rs->fields["perform_date"];
$worker = $rs->fields["worker"];
$subpaper = $rs->fields["subpaper"];
$ink_C = $rs->fields["ink_C"];
$ink_M = $rs->fields["ink_M"];
$ink_Y = $rs->fields["ink_Y"];
$ink_K = $rs->fields["ink_K"];
$adjust_price = $rs->fields["adjust_price"];
$work_price = $rs->fields["work_price"];
$extnl_etprs_seqno = $rs->fields["extnl_etprs_seqno"];

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

$conn->CompleteTrans();
$conn->Close();
echo $check;
?>
