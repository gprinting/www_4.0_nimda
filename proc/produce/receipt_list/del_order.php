<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/produce/receipt_mng/ReceiptListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new ReceiptListDAO();
$check = 1;

$order_common_seqno = $fb->form("seqno");
$order_detail_dvs_num = $fb->form("order_detail_dvs_num");

$param = array();
$param["table"] = "order_detail_count_file";
$param["col"] = "order_detail_count_file_seqno";
$param["where"]["order_detail_dvs_num"] = $order_detail_dvs_num;

$sel_rs = $dao->selectData($conn, $param);

$order_detail_count_file_seqno = $rs->fields["order_detail_count_file_seqno"];

//접수 취소시 결재금액, 회원 일련번호
$param = array();
$param["seqno"] = $order_common_seqno;

$sel_rs = $dao->selectOrderCancelInfo($conn, $param);

$member_seqno = $sel_rs->fields["member_seqno"];
$pay_price = $sel_rs->fields["pay_price"];

$param = array();
$param["member_seqno"] = $member_seqno;

$sel_rs = $dao->selectMemberPrepay($conn, $param);

$prepay_price = $sel_rs->fields["prepay_price"];

$conn->StartTrans();

$param = array();
$param["prepay_price"] = intVal($prepay_price) + intVal($pay_price);
$param["member_seqno"] = $member_seqno;

$rs = $dao->updatePrepayBack($conn, $param);

if (!$rs) {
    $check = 0;
}

$param = array();
$param["eraser"] = $fb->session("name");
$param["seqno"] = $order_common_seqno;

$rs = $dao->updateOrderDel($conn, $param);

if (!$rs) {
    $check = 0;
}

//후공정발주 삭제
if ($order_detail_dvs_num) {
    $param = array();
    $param["table"] = "after_op";
    $param["col"] = "after_op_seqno";
    $param["where"]["order_detail_dvs_num"] = $order_detail_dvs_num;

    $after_rs = $dao->selectData($conn, $param);

    while ($after_rs && !$after_rs->EOF) {

        $after_op_seqno = $after_rs->fields["after_op_seqno"];

        if ($after_op_seqno) {
            $paramFile = array();
            $paramFile["table"] = "after_op_work_file";
            $paramFile["prk"] = "after_op_seqno";
            $paramFile["prkVal"] = $after_op_seqno;
            
            $after_delete_rs = $dao->deleteData($conn, $paramFile);
            if (!$after_delete_rs) {
                $check = 0;
            }
        }
    
        $after_rs->moveNext();
    }

    $param = array();
    $param["table"] = "after_op";
    $param["prk"] = "order_detail_dvs_num";
    $param["prkVal"] = $order_detail_dvs_num;

    $rs = $dao->deleteData($conn, $param);

    if (!$rs) {
        $check = 0;
    }
}

$param = array();

//수량 주문 상세 조판 삭제
$param = array();
$param["table"] = "amt_order_detail_sheet";
$param["prk"] = "order_detail_count_file_seqno";
$param["prkVal"] = $order_detail_count_file_seqno;

$rs = $dao->deleteData($conn, $param);

if (!$rs) {
    $check = 0;
}

$conn->CompleteTrans();
$conn->Close();

echo $check;
?>
