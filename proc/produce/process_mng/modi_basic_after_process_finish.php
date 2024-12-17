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
$basic_after_op_seqno = $fb->form("seqno");
$state_arr = $fb->session("state_arr");

$state = $state_arr["주문후공정대기"];

//출력 발주서 검색
$param = array();
$param["table"] = "basic_after_op";
$param["col"] = "typset_num, flattyp_dvs";
$param["where"]["basic_after_op_seqno"] = $basic_after_op_seqno;

$sel_rs = $dao->selectData($conn, $param);

$conn->StartTrans();

$param = array();
$param["flattyp_dvs"] = $sel_rs->fields["flattyp_dvs"];
$param["typset_num"] = $sel_rs->fields["typset_num"];
$param["state"] = $state;

$check = $util->changeOrderState($conn, $dao, $param);

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

//기존 작업일지 수정
$param = array();
$param["table"] = "basic_after_work_report";
$param["col"]["work_end_hour"] = date("Y-m-d H:i:s");
$param["col"]["state"] = $state;
$param["prk"] = "basic_after_op_seqno";
$param["prkVal"] = $basic_after_op_seqno;

$rs = $dao->updateWorkReport($conn, $param);

if (!$rs) {
    $check = 0;
}

$conn->CompleteTrans();
$conn->Close();
echo $check;
?>
