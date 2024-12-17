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

$conn->StartTrans();
$typset_num = $fb->form("typset_num");

$param = array();
$param["table"] = "sheet_typset";
$param["col"] = "sheet_typset_seqno";
$param["where"]["typset_num"] = $typset_num;

$sel_rs = $dao->selectData($conn, $param);

$sheet_typset_seqno = $sel_rs->fields["sheet_typset_seqno"];

$param = array();
$param["table"] = "produce_process_flow";
$param["col"] = "paper_yn ,output_yn ,print_yn 
,typset_save_yn ,output_save_yn ,print_save_yn";
$param["where"]["typset_num"] = $typset_num;

$sel_rs = $dao->selectData($conn, $param);

$state = "";
$output_yn = $sel_rs->fields["output_yn"];
$print_yn = $sel_rs->fields["print_yn"];
$typset_save_yn = $sel_rs->fields["typset_save_yn"];
$output_save_yn = $sel_rs->fields["output_save_yn"];
$print_save_yn = $sel_rs->fields["print_save_yn"];

if ($typset_save_yn != "Y") {
    echo "2";
    exit;
}

if ($output_save_yn != "Y") {
    echo "3";
    exit;
}

if ($print_save_yn != "Y") {
    echo "4";
    exit;
}

//출력 대기
if ($output_yn == "Y") {
    $state = $state_arr["출력대기"];

    $param = array();
    $param["table"] = "output_op";
    $param["col"]["regi_date"] = date("Y-m-d H:i:s");
    $param["col"]["state"] = $state;
    $param["col"]["orderer"] = $fb->session("name");
    $param["prk"] = "typset_num";
    $param["prkVal"] = $typset_num;

    $rs = $dao->updateData($conn, $param);

    if (!$rs) {
        $check = 0;
    }

    changeState($conn, $dao, $sheet_typset_seqno, $state);
}

//인쇄 대기
if ($output_yn == "N" && $print_yn == "Y") {
    $state = $state_arr["인쇄대기"];

    $param = array();
    $param["table"] = "print_op";
    $param["col"]["regi_date"] = date("Y-m-d H:i:s");
    $param["col"]["state"] = $state;
    $param["col"]["orderer"] = $fb->session("name");
    $param["prk"] = "typset_num";
    $param["prkVal"] = $typset_num;

    $rs = $dao->updateData($conn, $param);

    if (!$rs) {
        $check = 0;
    }
    
    changeState($conn, $dao, $sheet_typset_seqno, $state);
}

//후공정 대기
if ($output_yn == "N" && $print_yn == "N") {
    $state = $state_arr["후공정대기"];
    
    changeState($conn, $dao, $sheet_typset_seqno, $state, 1, $fb->session("name"));
}

$param = array();
$param["table"] = "amt_order_detail_sheet";
$param["col"]["state"] = $state;
$param["prk"] = "sheet_typset_seqno";
$param["prkVal"] = $sheet_typset_seqno;

$rs = $dao->updateData($conn, $param);

if (!$rs) {
    $check = 0;
}

$param = array();
$param["table"] = "sheet_typset";
$param["col"]["state"] = $state;
$param["prk"] = "typset_num";
$param["prkVal"] = $typset_num;

$rs = $dao->updateData($conn, $param);

if (!$rs) {
    $check = 0;
}

$conn->CompleteTrans();
$conn->Close();
echo $check;


/**
 * 2016-07-19 추가 전민재
 * order_common, order_detail, order_detail_count_file 의 state 값 변경
 * param1 : sheet_typset_seqno = 조판 번호
 * param2 : state = 상태값 (출력, 인쇄, 후공정 각기 다름)
 * param3 : after_op = 후공정 여부
 * param4 : sessionName = 후공정일 경우에만 세션네임 
 */
function changeState ($conn, $dao, $sheet_typset_seqno, $state, $after_op = 0, $sessionName = "") {
    $param = array();
    $param["table"] = "amt_order_detail_sheet";
    $param["col"] = "order_detail_count_file_seqno";
    $param["where"]["sheet_typset_seqno"] = $sheet_typset_seqno;

    $sheet_rs = $dao->selectData($conn, $param);

    while ($sheet_rs && !$sheet_rs->EOF) {
        $order_detail_count_file_seqno = $sheet_rs->fields["order_detail_count_file_seqno"];

        //order_detail_count_file 의 state 변경
        $param = array();
        $param["table"] = "order_detail_count_file";
        $param["col"]["state"] = $state;
        $param["prk"] = "order_detail_count_file_seqno";
        $param["prkVal"] = $order_detail_count_file_seqno;

        $count_update_rs = $dao->updateData($conn, $param);
        if (!$count_update_rs) {
            $check = 0;
        }

        //order_detail_seqno 값 구하기(무조건 값이 1개임).
        $param = array();
        $param["table"] = "order_detail_count_file";
        $param["col"] = "order_detail_seqno";
        $param["where"]["order_detail_count_file_seqno"] = $order_detail_count_file_seqno;
        $detail_rs = $dao->selectData($conn, $param);

        $order_detail_seqno = $detail_rs->fields["order_detail_seqno"];

        //order_detail 의 state 변경
        $param = array();
        $param["table"] = "order_detail";
        $param["col"]["state"] = $state;
        $param["prk"] = "order_detail_seqno";
        $param["prkVal"] = $order_detail_seqno;

        $detail_update_rs = $dao->updateData($conn, $param);
        if (!$detail_update_rs) {
            $check = 0;
        }

        //order_common_seqno 값 구하기(무조건 값이 1개임).
        //after_op 경우를 위해 order_detail_dvs_num 값 구함.
        $param = array();
        $param["table"] = "order_detail";
        $param["col"] = "order_common_seqno, order_detail_dvs_num";
        $param["where"]["order_detail_seqno"] = $order_detail_seqno;
        $common_rs = $dao->selectData($conn, $param);

        while ($common_rs && !$common_rs->EOF) {
            $order_detail_dvs_num = $common_rs->fields["order_detail_dvs_num"];
            $order_common_seqno = $common_rs->fields["order_common_seqno"];

            //후공정은 조판이아닌 주문과 관련되어 있음.
            if ($after_op == 1) {
                $param = array();
                $param["table"] = "after_op";
                $param["col"]["regi_date"] = date("Y-m-d H:i:s");
                $param["col"]["order_common_seqno"] = $order_common_seqno;
                $param["col"]["state"] = $state;
                $param["col"]["orderer"] = $sessionName;
                $param["prk"] = "order_detail_dvs_num";
                $param["prkVal"] = $order_detail_dvs_num;

                $after_rs = $dao->updateData($conn, $param);

                if (!$after_rs) {
                    $check = 0;
                }
            }

            $common_rs->moveNext(); 
        }
        //order_common 의 order_state 변경
        $param = array();
        $param["state"] = $state;
        $param["order_common_seqno"] = $order_common_seqno;

        $common_update_rs = $dao->updateOrderCommonState($conn, $param);
        if (!$common_update_rs) {
            $check = 0;
        }

        $sheet_rs->moveNext(); 
    }
}
?>
