<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/produce/receipt_mng/ReceiptListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new ReceiptListDAO();

$order_detail_count_file_seqno = $fb->form("order_detail_count_file_seqno");

$conn->StartTrans();

$param = array();
$param["table"] = "order_detail_count_file";
$param["col"] = " order_detail_count_file_seqno
                 ,tmp_file_path
                 ,tmp_file_name
                 ,order_detail_seqno";
                
$param["where"]["order_detail_count_file_seqno"] = $order_detail_count_file_seqno;

$rs = $dao->selectData($conn, $param);

$detail_num = $rs->fields["detail_num"];
$order_detail_seqno = $rs->fields["order_detail_seqno"];

//file_path 가 없을경우 에러로 간주
$file_path = $rs->fields["tmp_file_path"] .
             $rs->fields["tmp_file_name"];

if (@unlink($file_path) === false) {
    if (@is_file($file_path) === true) {
        goto ERR;
    }
}

//파일관련 컬럼을 null 값으로 업데이트
$param = array();
$param["order_detail_count_file_seqno"] = $order_detail_count_file_seqno;
$dao->updateFileDelete($conn, $param);

if ($conn->HasFailedTrans() === true) {
    $conn->FailTrans();
    $conn->RollbackTrans();
    echo "error";
    goto ERR;
}

$conn->CompleteTrans();

echo 'T';
$conn->Close();
exit;

ERR:
    echo 'F';
    $conn->Close();
    exit;
?>
