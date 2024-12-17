<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/define/nimda/common_config.inc");
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
$order_detail_seqno = $fb->form("order_detail_seqno");
$font_file_upload_yn = $fb->form("font_file_upload_yn");

$param = array();
$param["table"] = "order_after_history";
$param["col"] = "COUNT(*) AS cnt";
$param["where"]["order_detail_dvs_num"] = $order_detail_dvs_num;
$param["where"]["basic_yn"] = "N";

$rs = $dao->selectData($conn, $param);

$history_cnt = $rs->fields["cnt"];

$param = array();
$param["table"] = "after_op";
$param["col"] = "COUNT(*) AS cnt";
$param["where"]["order_detail_dvs_num"] = $order_detail_dvs_num;
$param["where"]["basic_yn"] = "N";

$rs = $dao->selectData($conn, $param);

$op_cnt = $rs->fields["cnt"];

if ($history_cnt != $op_cnt) {
    echo "2";
    exit;
}

$state_arr = $fb->session("state_arr");

$conn->StartTrans();

$param = array();
$param["seqno"] = $order_common_seqno;
$param["receipt_mng"] = $fb->session("name");
$param["order_state"] = $state_arr["QC대기"];

$rs = $dao->updateReceipt($conn, $param);

if (!$rs) {
    $check = 0;
}

$param = array();
$param["table"] = "order_detail";
$param["col"]["state"] = $state_arr["QC대기"];
$param["prk"] = "order_detail_seqno";
$param["prkVal"] = $order_detail_seqno;

$rs = $dao->updateData($conn, $param);

if (!$rs) {
    $check = 0;
}

/*
 * 2016-07-12 추가. 전민재
 * order_detail_count_file 의 seq, order_detail_file_num, state 업데이트
 */
$param = array();
$param["table"] = "order_detail_count_file";
$param["col"] = "order_detail_count_file_seqno, tmp_file_path, tmp_file_name";
$param["where"]["order_detail_seqno"] = $order_detail_seqno;

$rs = $dao->selectData($conn, $param);

while ($rs && !$rs->EOF) {
    $param = array();
    $param["table"] = "order_detail_count_file";
    $param["col"]["state"] = $state_arr["QC대기"];
    $param["prk"] = "order_detail_count_file_seqno";
    $param["prkVal"] = $rs->fields["order_detail_count_file_seqno"]; 
  
    $dao->updateData($conn, $param);

    $tmp_file = $rs->fields["tmp_file_path"] . $rs->fields["tmp_file_name"];
    if ($font_file_upload_yn == "Y") {
        $mv_file_path = SITE_DEFAULT_FONT_INPUT . DIRECTORY_SEPARATOR . $rs->fields["tmp_file_name"];
    } else {
        $mv_file_path = SITE_DEFAULT_FONT_INPUT . DIRECTORY_SEPARATOR . $rs->fields["tmp_file_name"];
    }

    exec("cp ".$ori_file." ".$mv_file_path);

    $i++; 
    $rs->moveNext();
}

$conn->CompleteTrans();
$conn->Close();
echo $check;
?>
