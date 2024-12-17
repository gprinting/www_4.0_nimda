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
$output_op_seqno = $fb->form("seqno");

//출력 발주서 검색
$param = array();
$param["table"] = "output_op";
$param["col"] = "typset_num, flattyp_dvs";
$param["where"]["output_op_seqno"] = $output_op_seqno;

$sel_rs = $dao->selectData($conn, $param);

$param = array();
$param["table"] = "produce_process_flow";
$param["col"] = "print_yn";
$param["where"]["typset_num"] = $sel_rs->fields["typset_num"];

$print_yn = $dao->selectData($conn, $param)->fields["print_yn"];

$conn->StartTrans();

//print_yn 이 Y 일경우 print_op 의 state 변경
//            N 일경우 after_op 의 state 변경
if ($print_yn == "Y") {
    $state = $util->status2statusCode("인쇄대기");

    $param = array();
    $param["table"] = "print_op";
    $param["col"]["state"] = $state;
    $param["prk"] = "typset_num";
    $param["prkVal"] = $sel_rs->fields["typset_num"];

    $rs = $dao->updateData($conn, $param);

    if (!$rs) {
        $check = 0;
    }

} else if ($print_yn == "N") {
    //후공정은 하나의 주문이 출력, 인쇄가 완료 되면 후공정 대기로 변함
    $state = $util->status2statusCode("조판후공정대기");
}

$param = array();
$param["flattyp_dvs"] = $sel_rs->fields["flattyp_dvs"];
$param["typset_num"] = $sel_rs->fields["typset_num"];
$param["state"] = $state;

$check = $util->changeOrderState($conn, $dao, $param);

//변경된 상태값 적용
$param = array();
$param["table"] = "output_op";
$param["col"]["state"] = $state;
$param["prk"] = "output_op_seqno";
$param["prkVal"] = $output_op_seqno;

$rs = $dao->updateData($conn, $param);

if (!$rs) {
    $check = 0;
}

//기존 작업일지 수정
$param = array();
$param["table"] = "output_work_report";
$param["col"]["work_end_hour"] = date("Y-m-d H:i:s");
$param["col"]["state"] = $state;
$param["prk"] = "output_op_seqno";
$param["prkVal"] = $output_op_seqno;

$rs = $dao->updateWorkReport($conn, $param);

if (!$rs) {
    $check = 0;
}

$conn->CompleteTrans();
$conn->Close();
echo $check; ?>
