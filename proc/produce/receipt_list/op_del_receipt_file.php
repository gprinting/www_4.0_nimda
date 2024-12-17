<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/produce/receipt_mng/ReceiptListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new ReceiptListDAO();

$after_op_work_file_seqno = $fb->form("after_op_work_file_seqno");

$conn->StartTrans();

$param = array();
$param["table"] = "after_op_work_file";
$param["col"] = " after_op_work_file_seqno 
                 ,file_path
                 ,save_file_name
                 ,origin_file_name
                 ,size
                 ,after_op_seqno";
                
$param["where"]["after_op_work_file_seqno"] = $after_op_work_file_seqno;

$rs = $dao->selectData($conn, $param);

$file_path = $rs->fields["file_path"] .
             $rs->fields["save_file_name"];

if (@unlink($file_path) === false) {
    if (@is_file($file_path) === true) {
        goto ERR;
    }
}

$param = array();
$param["table"] = "after_op_work_file";
$param["prk"] = "after_op_work_file_seqno";
$param["prkVal"] = $after_op_work_file_seqno;

$dao->deleteData($conn, $param);

if ($conn->HasFailedTrans() === true) {
    $conn->FailTrans();
    $conn->RollbackTrans();
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
