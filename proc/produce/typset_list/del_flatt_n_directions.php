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

$seqno = $fb->form("seqno");

//낱장 조판
$param = array();
$param["table"] = "brochure_typset";
$param["col"] = "typset_num";
$param["where"]["brochure_typset_seqno"] = $seqno;

$rs = $dao->selectData($conn, $param);

$typset_num = $rs->fields["typset_num"];

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
$param["col"]["state"] = $state_arr["출력준비"];
$param["prk"] = "typset_num";
$param["prkVal"] = $typset_num;

$rs = $dao->updateData($conn, $param);

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
$param["col"]["state"] = $state_arr["인쇄준비"];
$param["prk"] = "typset_num";
$param["prkVal"] = $typset_num;

$rs = $dao->updateData($conn, $param);

if (!$rs) {
    $check = 0;
}

//종이발주 삭제
$param = array();
$param["table"] = "paper_op";
$param["col"]["state"] = $state_arr["종이발주취소"];
$param["prk"] = "typset_num";
$param["prkVal"] = $typset_num;

$rs = $dao->updateData($conn, $param);

if (!$rs) {
    $check = 0;
}

//책자 조판
$param = array();
$param["table"] = "brochure_typset";
$param["col"]["state"] = $state_arr["조판중"];
$param["prk"] = "typset_num";
$param["prkVal"] = $typset_num;

$rs = $dao->updateData($conn, $param);

if (!$rs) {
    $check = 0;
}

/*
 * 2016-07-21 전민재 추가
 * page_order_detail_brochure - state
 * order_detail_brochure - state
 * order_common - order_state
 * 모두 조판대기(410) 변경함.
 */
$param = array();
$param["table"] = "brochure_typset";
$param["col"] = "brochure_typset_seqno";
$param["where"]["typset_num"] = $typset_num;

$sel_rs = $dao->selectData($conn, $param);

$brochure_typset_seqno = $sel_rs->fields["brochure_typset_seqno"];
$state = $state_arr["조판대기"];

$param = array();
$param["table"] = "page_order_detail_brochure";
$param["col"]["state"] = $state;
$param["prk"] = "brochure_typset_seqno";
$param["prkVal"] = $brochure_typset_seqno;

$rs = $dao->updateData($conn, $param);

if (!$rs) {
    $check = 0;
}


$param = array();
$param["table"] = "page_order_detail_brochure";
$param["col"] = "order_detail_dvs_num";
$param["where"]["brochure_typset_seqno"] = $brochure_typset_seqno;

$brochure_rs = $dao->selectData($conn, $param);

while ($brochure_rs && !$brochure_rs->EOF) {
    $order_detail_dvs_num = $brochure_rs->fields["order_detail_dvs_num"];

    //order_detail_brochure 의 state 변경
    $param = array();
    $param["table"] = "order_detail_brochure";
    $param["col"]["state"] = $state;
    $param["prk"] = "order_detail_dvs_num";
    $param["prkVal"] = $order_detail_dvs_num;

    $rs = $dao->updateData($conn, $param);
    if (!$rs) {
        $check = 0;
    }

    //order_common_seqno 값 구하기.
    $param = array();
    $param["table"] = "order_detail_brochure";
    $param["col"] = "order_common_seqno";
    $param["where"]["order_detail_dvs_num"] = $order_detail_dvs_num;
    $rs = $dao->selectData($conn, $param);

    $order_common_seqno = $rs->fields["order_common_seqno"];

    //order_common 의 order_state 변경
    $param = array();
    $param["state"] = $state;
    $param["order_common_seqno"] = $order_common_seqno;

    $common_update_rs = $dao->updateOrderCommonState($conn, $param);
    if (!$common_update_rs) {
        $check = 0;
    }

    //after_op 도 바꿔줘야함
    $param = array();
    $param["table"] = "after_op";
    $param["col"]["state"] = $state_arr["후공정준비"];
    $param["prk"] = "order_detail_dvs_num";
    $param["prkVal"] = $order_detail_dvs_num;

    $after_rs = $dao->updateData($conn, $param);

    if (!$after_rs) {
        $check = 0;
    }

    $brochure_rs->moveNext(); 
}

$conn->CompleteTrans();
$conn->Close();
echo $check;
?>
