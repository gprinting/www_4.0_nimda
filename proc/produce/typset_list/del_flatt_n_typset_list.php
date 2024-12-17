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

//책자 조판
$param = array();
$param["table"] = "brochure_typset";
$param["col"] = "typset_num";
$param["where"]["brochure_typset_seqno"] = $seqno;

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
$param["table"] = "page_order_detail_brochure";
$param["col"] = "order_detail_dvs_num";
$param["where"]["brochure_typset_seqno"] = $seqno;

$sel_rs = $dao->selectData($conn, $param);

while ($sel_rs && !$sel_rs->EOF) {

    $order_detail_dvs_num = $sel_rs->fields["order_detail_dvs_num"];

    $param = array();
    $param["table"] = "order_detail_brochure";
    $param["col"] = "order_common_seqno";
    $param["where"]["order_detail_dvs_num"] = $order_detail_dvs_num;

    $rs = $dao->selectData($conn, $param);

    $order_common_seqno = $rs->fields["order_common_seqno"];

    $param = array();
    $param["order_state"] = "410";
    $param["order_common_seqno"] = $order_common_seqno;

    $rs = $dao->updateOrderState($conn, $param);

    if (!$rs) {
        $check = 0;
    }

    $param = array();
    $param["table"] = "after_op";
    $param["col"]["state"] = "805";
    $param["prk"] = "order_common_seqno";
    $param["prkVal"] = $seqno;
 
    $rs = $dao->updateData($conn, $param);

    if (!$rs) {
        $check = 0;
    }

    $sel_rs->moveNext();
}
//수량주문상세책장
$param = array();
$param["table"] = "page_order_detail_brochure";
$param["col"]["brochure_typset_seqno"] = NULL;
$param["col"]["state"] = '410';
$param["prk"] = "brochure_typset_seqno";
$param["prkVal"] = $seqno;

$rs = $dao->updateData($conn, $param);

if (!$rs) {
    $check = 0;
}

//책자조판파일 검색
$param = array();
$param["table"] = "brochure_typset_file";
$param["col"] = "file_path ,save_file_name";
$param["where"]["brochure_typset_seqno"] = $seqno;

$sel_rs = $dao->selectData($conn, $param);

$file_path = $sel_rs->fields["file_path"] . $sel_rs->fields["save_file_name"];

if ($file_path) {
    unlink(INC_PATH . $file_path);
}

//책자조판파일삭제
$param = array();
$param["table"] = "brochure_typset_file";
$param["prk"] = "brochure_typset_seqno";
$param["prkVal"] = $seqno;

$rs = $dao->deleteData($conn, $param);

if (!$rs) {
    $check = 0;
}

//책자조판삭제
$param = array();
$param["table"] = "brochure_typset";
$param["prk"] = "brochure_typset_seqno";
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
$param["col"]["state"] = "530";
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
