<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/produce/typset_mng/ProcessMngDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new ProcessMngDAO();
$check = 1;

$order_num = "";

$after_op_seqno = $fb->form("seqno");
$state_arr = $fb->session("state_arr");
$conn->StartTrans();

$state = $state_arr["후공정중"];

//after_op 상태값 변경
$param = array();
$param["table"] = "after_op";
$param["col"]["state"] = $state;
$param["prk"] = "after_op_seqno";
$param["prkVal"] = $after_op_seqno;

$rs = $dao->updateData($conn, $param);

if (!$rs) {
    $check = 0;
}

$param = array();
$param["table"] = "after_op";
$param["col"] = "order_common_seqno, order_detail_dvs_num";
$param["where"]["after_op_seqno"] = $after_op_seqno;

$sel_rs = $dao->selectData($conn, $param);

$order_common_seqno = $sel_rs->fields["order_common_seqno"];
$order_detail_dvs_num = $sel_rs->fields["order_detail_dvs_num"];

/*  
 * 후공정이 몇건이라도 한건이라도 시작 진행시 
 * 주문(order_common, order_detail, order_detail_count_file) 의 상태값은 변함.
 */

//order_common 의 order_state 값 변경
$param = array();
$param["order_common_seqno"] = $order_common_seqno;
$param["state"] = $state;

$rs = $dao->updateOrderCommonState($conn, $param);
if (!$rs) {
    $check = 0;
}

$flattyp_yn = substr($order_detail_dvs_num, 0, 1);

if ($flattyp_yn === "S") {  
    //낱장형
    //order_detail 의 state 값 변경
    $param = array();
    $param["table"] = "order_detail";
    $param["col"]["state"] = $state;
    $param["prk"] = "order_detail_dvs_num";
    $param["prkVal"] = $order_detail_dvs_num;

    $detail_update_rs = $dao->updateData($conn, $param);
    if (!$detail_update_rs) {
        $check = 0;
    }

    //order_detail 의 order_detail_seqno 값 구하기
    $param = array();
    $param["table"] = "order_detail";
    $param["col"] = "order_detail_seqno";
    $param["where"]["order_detail_dvs_num"] = $order_detail_dvs_num;
    $rs = $dao->selectData($conn, $param);

    $order_detail_seqno = $rs->fields["order_detail_seqno"];

    //order_detail_count_file 의 state 값 변경
    $param = array();
    $param["table"] = "order_detail_count_file";
    $param["col"]["state"] = $state;
    $param["prk"] = "order_detail_seqno";
    $param["prkVal"] = $order_detail_seqno;

    $count_update_rs = $dao->updateData($conn, $param);
    if (!$count_update_rs) {
        $check = 0;
    }
} else if ($flattyp_yn === "B") {
    //책자형
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
}

//후공정 작업일지 추가
$param = array();
$param["table"] = "after_work_report";
$param["col"]["worker_memo"] = $fb->form("worker_memo");
$param["col"]["work_start_hour"] = date("Y-m-d H:i:s");
$param["col"]["adjust_price"] = $fb->form("adjust_price");
$param["col"]["work_price"] = $fb->form("work_price");
$param["col"]["extnl_etprs_seqno"] = $fb->form("extnl_etprs_seqno");
$param["col"]["worker"] = $fb->session("name");
$param["col"]["valid_yn"] = "Y";
$param["col"]["state"] = $state;
$param["col"]["after_op_seqno"] = $after_op_seqno;

$rs = $dao->insertData($conn, $param);

if (!$rs) {
    $check = 0;
}

$conn->CompleteTrans();
$conn->Close();
echo $check;
?>
