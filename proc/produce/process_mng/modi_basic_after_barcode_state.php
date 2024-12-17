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

//후공정 발주서 검색
$param = array();
$param["table"] = "basic_after_op";
$param["col"] = "flattyp_dvs, after_name, depth1, depth2, depth3, extnl_brand_seqno, amt";
$param["where"]["typset_num"] = $typset_num;

$sel_rs = $dao->selectData($conn, $param);

$flattyp_dvs = $sel_rs->fields["flattyp_dvs"];
$basic_after_op_seqno = $sel_rs->fields["basic_after_op_seqno"];

$param = array();
$param["table"] = "extnl_brand";
$param["col"] = "extnl_etprs_seqno";
$param["where"]["extnl_brand_seqno"] = $sel_rs->fields["extnl_brand_seqno"];

$extnl_etprs_seqno = $dao->selectData($conn, $param)->fields["extnl_etprs_seqno"];

$param = array();
$param["table"] = "after";
$param["col"] = "basic_price";
$param["where"]["extnl_brand_seqno"] = $sel_rs->fields["extnl_brand_seqno"];
$param["where"]["after_name"] = $sel_rs->fields["after_name"];
$param["where"]["amt"] = $sel_rs->fields["amt"];

$basic_price = $dao->selectData($conn, $param)->fields["basic_price"];
$price = number_format(intVal($basic_price) * intVal($fb->form("amt")));

$conn->StartTrans();

if ($sel_rs->fields["state"] == $util->status2statusCode("조판후공정대기")) {
    $state = $util->status2statusCode("조판후공정중");

    //출력 작업일지 추가
    $param = array();
    $param["table"] = "basic_after_work_report";
    $param["col"]["worker_memo"] = "바코드 처리";
    $param["col"]["work_start_hour"] = date("Y-m-d H:i:s");
    $param["col"]["work_price"] = $price;
    $param["col"]["adjust_price"] = 0;
    $param["col"]["extnl_etprs_seqno"] = $extnl_etprs_seqno;
    $param["col"]["worker"] = $fb->session("name");
    $param["col"]["valid_yn"] = "Y";
    $param["col"]["state"] = $state;
    $param["col"]["basic_after_op_seqno"] = $output_op_seqno;

    $rs = $dao->insertData($conn, $param);

    if (!$rs) {
        $check = 0;
    }

} else if ($sel_rs->fields["state"] == $util->status2statusCode("조판후공정중")) {
    $state = $util->status2statusCode("주문후공정대기");

    //기존 작업일지 수정
    $param = array();
    $param["table"] = "basic_after_work_report";
    $param["col"]["work_end_hour"] = date("Y-m-d H:i:s");
    $param["col"]["state"] = $state;
    $param["prk"] = "output_op_seqno";
    $param["prkVal"] = $basic_after_op_seqno;

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
$param["table"] = "basic_after_op";
$param["col"]["state"] = $state;
$param["prk"] = "basic_after_op_seqno";
$param["prkVal"] = $basic_after_op_seqno;

$rs = $dao->updateData($conn, $param);

if (!$rs) {
    $check = 0;
}

$param = array();
$param["flattyp_dvs"] = $flattyp_dvs;
$param["typset_num"] = $typset_num;
$param["state"] = $state;

$check = $util->changeOrderState($conn, $dao, $param);

$conn->CompleteTrans();
$conn->Close();
echo $check;
?>
