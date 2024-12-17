<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/produce/typset_mng/TypsetListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new TypsetListDAO();
$check = 1;
$state_arr = $fb->session("state_arr");

$typset_num = $fb->form("typset_num");

$conn->StartTrans();

//출력 작업 일지 삭제
$param = array();
$param["table"] = "output_op";
$param["col"] = "output_op_seqno";
$param["where"]["typset_num"] = $typset_num;

$sel_rs = $dao->selectData($conn,$param);

while ($sel_rs && !$sel_rs->EOF) {

    $param = array();
    $param["table"] = "output_work_report";
    $param["prk"] = "output_op_seqno";
    $param["prkVal"] = $sel_rs->fields["output_op_seqno"];

    $rs = $dao->deleteData($conn, $param);

    if (!$rs) {
        $check = 0;
    }

    $sel_rs->moveNext();
}

//출력발주 삭제
$param = array();
$param["table"] = "output_op";
$param["prk"] = "typset_num";
$param["prkVal"] = $typset_num;

$rs = $dao->deleteData($conn, $param);

if (!$rs) {
    $check = 0;
}

//인쇄 작업 일지 삭제
$param = array();
$param["table"] = "print_op";
$param["col"] = "print_op_seqno";
$param["where"]["typset_num"] = $typset_num;

$sel_rs = $dao->selectData($conn,$param);

while ($sel_rs && !$sel_rs->EOF) {

    $param = array();
    $param["table"] = "print_work_report";
    $param["prk"] = "print_op_seqno";
    $param["prkVal"] = $sel_rs->fields["print_op_seqno"];

    $rs = $dao->deleteData($conn, $param);

    if (!$rs) {
        $check = 0;
    }

    $sel_rs->moveNext();
}

//인쇄발주 삭제
$param = array();
$param["table"] = "print_op";
$param["prk"] = "typset_num";
$param["prkVal"] = $typset_num;

$rs = $dao->deleteData($conn, $param);

if (!$rs) {
    $check = 0;
}

//종이발주 삭제
$param = array();
$param["table"] = "paper_op";
$param["col"]["state"] = $state_arr["종이발주취소"];
$param["col"]["typset_num"] = NULL;
$param["prk"] = "typset_num";
$param["prkVal"] = $typset_num;

$rs = $dao->updateData($conn, $param);

if (!$rs) {
    $check = 0;
}

$param = array();
$param["table"] = "sheet_typset";
$param["col"] = "sheet_typset_seqno";
$param["where"]["typset_num"] = $typset_num;

$sel_rs = $dao->selectData($conn, $param);

$sheet_typset_seqno = $sel_rs->fields["sheet_typset_seqno"];
$state = $state_arr["조판대기"];

$param = array();
$param["table"] = "amt_order_detail_sheet";
$param["col"] = "order_detail_count_file_seqno";
$param["where"]["sheet_typset_seqno"] = $sheet_typset_seqno;

$sheet_rs = $dao->selectData($conn, $param);

while ($sheet_rs && !$sheet_rs->EOF) {
    $order_detail_count_file_seqno = $sheet_rs->fields["order_detail_count_file_seqno"];

    //order_detail_count_file 의 state 변경
    $param = array();
    $param["table"] = "order_detail_count_file";
    $param["col"]["state"] = $state;
    $param["prk"] = "order_detail_count_file_seqno";
    $param["prkVal"] = $order_detail_count_file_seqno;

    $count_update_rs = $dao->updateData($conn, $param);
    if (!$count_update_rs) {
        $check = 0;
    }

    //order_detail_seqno 값 구하기(무조건 값이 1개임).
    $param = array();
    $param["table"] = "order_detail_count_file";
    $param["col"] = "order_detail_seqno";
    $param["where"]["order_detail_count_file_seqno"] = $order_detail_count_file_seqno;
    $detail_rs = $dao->selectData($conn, $param);

    $order_detail_seqno = $detail_rs->fields["order_detail_seqno"];

    //order_detail 의 state 변경
    $param = array();
    $param["table"] = "order_detail";
    $param["col"]["state"] = $state;
    $param["prk"] = "order_detail_seqno";
    $param["prkVal"] = $order_detail_seqno;

    $detail_update_rs = $dao->updateData($conn, $param);
    if (!$detail_update_rs) {
        $check = 0;
    }

    //order_common_seqno 값 구하기(무조건 값이 1개임).
    //after_op 경우를 위해 order_detail_dvs_num 값 구함.
    $param = array();
    $param["table"] = "order_detail";
    $param["col"] = "order_common_seqno, order_detail_dvs_num";
    $param["where"]["order_detail_seqno"] = $order_detail_seqno;
    $common_rs = $dao->selectData($conn, $param);

    while ($common_rs && !$common_rs->EOF) {
        $order_detail_dvs_num = $common_rs->fields["order_detail_dvs_num"];
        $order_common_seqno = $common_rs->fields["order_common_seqno"];

        //후공정은 조판이아닌 주문과 관련되어 있음.
        $param = array();
        $param["table"] = "after_op";
        $param["col"]["state"] = $state_arr["후공정준비"];
        $param["prk"] = "order_detail_dvs_num";
        $param["prkVal"] = $order_detail_dvs_num;

        $after_rs = $dao->updateData($conn, $param);

        if (!$after_rs) {
            $check = 0;
        }

        $common_rs->moveNext(); 
    }
    //order_common 의 order_state 변경
    $param = array();
    $param["table"] = "order_common";
    $param["col"]["order_state"] = $state;
    $param["prk"] = "order_common_seqno";
    $param["prkVal"] = $order_common_seqno;

    $common_update_rs = $dao->updateData($conn, $param);
    if (!$common_update_rs) {
        $check = 0;
    }

    $sheet_rs->moveNext(); 
}

//낱장 조판 파일 삭제
$param = array();
$param["table"] = "sheet_typset_file";
$param["col"] = "file_path, save_file_name";
$param["where"]["sheet_typset_seqno"] = $sheet_typset_seqno;

$sel_rs = $dao->selectData($conn, $param);

while ($sel_rs && !$sel_rs->EOF) {
    $file_path = $sel_rs->fields["file_path"];
    $file_name = $sel_rs->fields["save_file_name"];

    $full_path = INC_PATH . $file_path . $file_name;

    unlink($full_path);
    $sel_rs->moveNext();
}

$param = array();
$param["table"] = "sheet_typset_file";
$param["prk"] = "sheet_typset_seqno";
$param["prkVal"] = $sheet_typset_seqno;

$rs = $dao->deleteData($conn, $param);

if (!$rs) {
    $check = 0;
}

$param = array();
$param["table"] = "sheet_typset_preview_file";
$param["col"] = "file_path, save_file_name";
$param["where"]["sheet_typset_seqno"] = $sheet_typset_seqno;

$sel_rs = $dao->selectData($conn, $param);

while ($sel_rs && !$sel_rs->EOF) {
    $file_path = $sel_rs->fields["file_path"];
    $file_name = $sel_rs->fields["save_file_name"];

    $full_path = INC_PATH . $file_path . $file_name;

    unlink($full_path);
    $sel_rs->moveNext();
}

$param = array();
$param["table"] = "sheet_typset_preview_file";
$param["prk"] = "sheet_typset_seqno";
$param["prkVal"] = $sheet_typset_seqno;

$rs = $dao->deleteData($conn, $param);

if (!$rs) {
    $check = 0;
}

$param = array();
$param["table"] = "amt_order_detail_sheet";
$param["col"]["state"] = $state;
$param["col"]["sheet_typset_seqno"] = NULL;
$param["prk"] = "sheet_typset_seqno";
$param["prkVal"] = $sheet_typset_seqno;

$sheet_rs = $dao->updateData($conn, $param);
if (!$sheet_rs) {
    $check = 0;
}

//낱장 조판 삭제
$param = array();
$param["table"] = "sheet_typset";
$param["prk"] = "typset_num";
$param["prkVal"] = $typset_num;

$rs = $dao->deleteData($conn, $param);

if (!$rs) {
    $check = 0;
}

$conn->CompleteTrans();
$conn->Close();
echo $check;
?>
