<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/business/claim_mng/ClaimListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new ClaimListDAO();

$order_file_seqno = $fb->form("order_file_seqno");

$root_path = INC_PATH;

$conn->StartTrans();

$param = array();
$param["table"] = "order_file";
$param["col"] = " order_file_seqno
                 ,dvs
                 ,save_file_name
                 ,origin_file_name
                 ,size
                 ,order_common_seqno
                 ,member_seqno";

$param["where"]["order_file_seqno"] = $order_file_seqno;

$rs = $dao->selectData($conn, $param);
$fields = $rs->fields;

$file_path = $root_path .
             $fields["file_path"] .
             $fields["save_file_name"];

if (@unlink($file_path) === false) {
    if (@is_file($file_path) === true) {
        goto ERR;
    }
}

$param = array();
$param["table"] = "order_file";
$param["prk"] = "order_file_seqno";
$param["prkVal"] = $order_file_seqno;

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
