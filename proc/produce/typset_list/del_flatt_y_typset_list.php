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

$seqno = $fb->form("seqno");

//낱장 조판
$param = array();
$param["table"] = "sheet_typset";
$param["col"] = "typset_num";
$param["where"]["sheet_typset_seqno"] = $seqno;

$rs = $dao->selectData($conn, $param);

$typset_num = $rs->fields["typset_num"];

$conn->StartTrans();

//생산공정흐름 삭제
$param = array();
$param["table"] = "produce_process_flow";
$param["prk"] = "typset_num";
$param["prkVal"] = $typset_num;

$rs = $dao->deleteData($conn, $param);

if (!$rs) {
    $check = 0;
}

//주문 상태 변경
$param = array();
$param["table"] = "amt_order_detail_sheet";
$param["col"] = "order_detail_count_file_seqno";
$param["where"]["sheet_typset_seqno"] = $seqno;

$sel_rs = $dao->selectData($conn, $param);

while ($sel_rs && !$sel_rs->EOF) {

    $order_detail_count_file_seqno = $sel_rs->fields["order_detail_count_file_seqno"];

    $param = array();
    $param["table"] = "order_detail_count_file";
    $param["col"] = "order_detail_seqno";
    $param["where"]["order_detail_count_file_seqno"] = $order_detail_count_file_seqno;

    $rs = $dao->selectData($conn, $param);

    $order_detail_seqno = $rs->fields["order_detail_seqno"];

    $param = array();
    $param["table"] = "order_detail";
    $param["col"] = "order_common_seqno";
    $param["where"]["order_detail_seqno"] = $order_detail_seqno;
    
    $rs = $dao->selectData($conn, $param);

    $order_common_seqno = $rs->fields["order_common_seqno"];

    $param = array();
    $param["order_state"] = "2120";
    $param["order_common_seqno"] = $order_common_seqno;

    $rs = $dao->updateOrderState($conn, $param);

    if (!$rs) {
        $check = 0;
    }

    $param = array();
    $param["table"] = "after_op";
    $param["col"]["state"] = "2410";
    $param["prk"] = "order_common_seqno";
    $param["prkVal"] = $seqno;
 
    $rs = $dao->updateData($conn, $param);

    if (!$rs) {
        $check = 0;
    }

    $sel_rs->moveNext();
}
//수량주문상세낱장
$param = array();
$param["table"] = "amt_order_detail_sheet";
$param["col"]["sheet_typset_seqno"] = NULL;
$param["col"]["state"] = '2120';
$param["prk"] = "sheet_typset_seqno";
$param["prkVal"] = $seqno;

$rs = $dao->updateData($conn, $param);

if (!$rs) {
    $check = 0;
}

//낱장조판파일 검색
$param = array();
$param["table"] = "sheet_typset_file";
$param["col"] = "file_path ,save_file_name";
$param["where"]["sheet_typset_seqno"] = $seqno;

$sel_rs = $dao->selectData($conn, $param);

$file_path = $sel_rs->fields["file_path"] . $sel_rs->fields["save_file_name"];

if ($file_path) {
    unlink(INC_PATH . $file_path);
}

//낱장조판파일삭제
$param = array();
$param["table"] = "sheet_typset_file";
$param["prk"] = "sheet_typset_seqno";
$param["prkVal"] = $seqno;

$rs = $dao->deleteData($conn, $param);

if (!$rs) {
    $check = 0;
}

//낱장조판삭제
$param = array();
$param["table"] = "sheet_typset";
$param["prk"] = "sheet_typset_seqno";
$param["prkVal"] = $seqno;

$rs = $dao->deleteData($conn, $param);

if (!$rs) {
    $check = 0;
}

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
$param["col"]["state"] = "8180";
$param["prk"] = "typset_num";
$param["prkVal"] = $typset_num;

$rs = $dao->updateData($conn, $param);

if (!$rs) {
    $check = 0;
}

$conn->CompleteTrans();
$conn->Close();
echo $check;
?>
