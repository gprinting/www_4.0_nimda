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

$after_op_seqno = $fb->form("seqno");
$state_arr = $fb->session("state_arr");

$conn->StartTrans();

$state = $state_arr["입고대기"];

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
 * 후공정의 모든 상태가 입고대기 일경우에시 
 * 주문(order_common, order_detail, order_detail_count_file) 의 상태값은 변함.
 */

//주문에 몇개의 후공정이 있는지 파악
$param = array();
$param["table"] = "after_op";
$param["where"]["order_common_seqno"] = $order_common_seqno;
$tot_rs = $dao->countData($conn, $param);
$totAfterCount = $tot_rs->fields["cnt"];

//after_op 후공정 작업이 totAfterCount 와 같을경우 
//주문(order_common, order_detail, order_detail_count_file) 의 상태값은 변함.
$param = array();
$param["table"] = "after_op";
$param["where"]["order_common_seqno"] = $order_common_seqno;
$param["where"]["state"] = $state;
$tot_rs = $dao->countData($conn, $param);
$totAfterStateCount = $tot_rs->fields["cnt"];

//주문(order_common, order_detail, order_detail_count_file) 의 상태값은 변함.
if ($totAfterCount === $totAfterStateCount) {
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
}

$param = array();
$param["table"] = "after_work_report";
$param["col"] = "worker_memo ,work_start_hour
, worker, adjust_price, work_price, extnl_etprs_seqno";
$param["where"]["after_op_seqno"] = $after_op_seqno;
$param["where"]["valid_yn"] = "Y";

$rs = $dao->selectData($conn, $param);

$worker_memo = $rs->fields["worker_memo"];
$work_start_hour = $rs->fields["work_start_hour"];
$worker = $rs->fields["worker"];
$adjust_price = $rs->fields["adjust_price"];
$work_price = $rs->fields["work_price"];
$extnl_etprs_seqno = $rs->fields["extnl_etprs_seqno"];

//기존 작업일지 유효여부 수정
$param = array();
$param["table"] = "after_work_report";
$param["col"]["work_end_hour"] = date("Y-m-d H:i:s");
$param["col"]["state"] = $state;
$param["prk"] = "after_op_seqno";
$param["prkVal"] = $after_op_seqno;

$rs = $dao->updateWorkReport($conn, $param);

if (!$rs) {
    $check = 0;
}

$conn->CompleteTrans();
$conn->Close();
echo $check;
?>
