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
$state = $util->status2statusCode("출력중");

//출력 발주서 검색
$param = array();
$param["table"] = "output_op";
$param["col"] = "typset_num, flattyp_dvs";
$param["where"]["output_op_seqno"] = $output_op_seqno;

$sel_rs = $dao->selectData($conn, $param);

$conn->StartTrans();

$param = array();
$param["flattyp_dvs"] = $sel_rs->fields["flattyp_dvs"];
$param["typset_num"] = $sel_rs->fields["typset_num"];
$param["state"] = $state;

$check = $util->changeOrderState($conn, $dao, $param);

//변경된 수량, 판, 상태값 적용
$param = array();
$param["table"] = "output_op";
$param["col"]["amt"] = $fb->form("amt");
$param["col"]["board"] = $fb->form("board");
$param["col"]["extnl_brand_seqno"] = $fb->form("extnl_brand_seqno");
$param["col"]["state"] = $state;
$param["prk"] = "output_op_seqno";
$param["prkVal"] = $output_op_seqno;

$rs = $dao->updateData($conn, $param);

if (!$rs) {
    $check = 0;
}

//출력 작업일지 추가
$param = array();
$param["table"] = "output_work_report";
$param["col"]["worker_memo"] = $fb->form("worker_memo");
$param["col"]["work_start_hour"] = date("Y-m-d H:i:s");
$param["col"]["adjust_price"] = $fb->form("adjust_price");
$param["col"]["work_price"] = $fb->form("work_price");
$param["col"]["extnl_etprs_seqno"] = $fb->form("extnl_etprs_seqno");
$param["col"]["worker"] = $fb->session("name");
$param["col"]["valid_yn"] = "Y";
$param["col"]["state"] = $state;
$param["col"]["output_op_seqno"] = $output_op_seqno;

$rs = $dao->insertData($conn, $param);

if (!$rs) {
    $check = 0;
}

$conn->CompleteTrans();
$conn->Close();
echo $check;
?>
