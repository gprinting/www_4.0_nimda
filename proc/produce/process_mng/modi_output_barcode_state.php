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

//출력 발주서 검색
$param = array();
$param["table"] = "output_op";
$param["col"] = "flattyp_dvs, state, name, size, board, output_op_seqno, extnl_brand_seqno";
$param["where"]["typset_num"] = $typset_num;

$sel_rs = $dao->selectData($conn, $param);

$output_op_seqno = $sel_rs->fields["output_op_seqno"];

$param = array();
$param["table"] = "extnl_brand";
$param["col"] = "extnl_etprs_seqno";
$param["where"]["extnl_brand_seqno"] = $sel_rs->fields["extnl_brand_seqno"];

$extnl_etprs_seqno = $dao->selectData($conn, $param)->fields["extnl_etprs_seqno"];

$param = array();
$param["table"] = "output";
$param["col"] = "basic_price";
$param["where"]["extnl_brand_seqno"] = $sel_rs->fields["extnl_brand_seqno"];
$param["where"]["search_check"] = $sel_rs->fields["name"] . "|" . 
$sel_rs->fields["board"] . "|" . 
$sel_rs->fields["size"];

$basic_price = $dao->selectData($conn, $param)->fields["basic_price"];
$price = number_format(intVal($basic_price) * intVal($fb->form("amt")));

$conn->StartTrans();

if ($sel_rs->fields["state"] == $util->status2statusCode("출력대기")) {
    $state = $util->status2statusCode("출력중");

    //출력 작업일지 추가
    $param = array();
    $param["table"] = "output_work_report";
    $param["col"]["worker_memo"] = "바코드 처리";
    $param["col"]["work_start_hour"] = date("Y-m-d H:i:s");
    $param["col"]["work_price"] = $price;
    $param["col"]["adjust_price"] = 0;
    $param["col"]["extnl_etprs_seqno"] = $extnl_etprs_seqno;
    $param["col"]["worker"] = $fb->session("name");
    $param["col"]["valid_yn"] = "Y";
    $param["col"]["state"] = $state;
    $param["col"]["output_op_seqno"] = $output_op_seqno;

    $rs = $dao->insertData($conn, $param);

    if (!$rs) {
        $check = 0;
    }

} else if ($sel_rs->fields["state"] == $util->status2statusCode("출력중")) {

    $param = array();
    $param["table"] = "produce_process_flow";
    $param["col"] = "print_yn";
    $param["where"]["typset_num"] = $typset_num;

    $print_yn = $dao->selectData($conn, $param)->fields["print_yn"];

    //print_yn 이 Y 일경우 print_op 의 state 변경
    //            N 일경우 after_op 의 state 변경
    if ($print_yn == "Y") {
        $state = $util->status2statusCode("인쇄대기");

        $param = array();
        $param["table"] = "print_op";
        $param["col"]["state"] = $state;
        $param["prk"] = "typset_num";
        $param["prkVal"] = $typset_num;

        $rs = $dao->updateData($conn, $param);

        if (!$rs) {
            $check = 0;
        }

    } else if ($print_yn == "N") {
        //후공정은 하나의 주문이 출력, 인쇄가 완료 되면 후공정 대기로 변함
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

} else {
    echo "2";
    exit;
}

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

$param = array();
$param["flattyp_dvs"] = $sel_rs->fields["flattyp_dvs"];
$param["typset_num"] = $typset_num;
$param["state"] = $state;

$check = $util->changeOrderState($conn, $dao, $param);

$conn->CompleteTrans();
$conn->Close();
echo $check;
?>
