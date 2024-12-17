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

$seqno = $fb->form("seqno");

$conn->StartTrans();
//낱장조판파일 검색
$param = array();
$param["table"] = "after_op_work_file";
$param["col"] = "file_path, save_file_name";
$param["where"]["after_op_work_file_seqno"] = $seqno;

$sel_rs = $dao->selectData($conn, $param);

$file_path = $sel_rs->fields["file_path"] . $sel_rs->fields["save_file_name"];

if ($file_path) {
    unlink(INC_PATH . $file_path);
}

$param = array();
$param["table"] = "after_op_work_file";
$param["prk"] = "after_op_work_file_seqno";
$param["prkVal"] = $seqno;

$rs = $dao->deleteData($conn, $param);

if (!$rs) {
    $check = 0;
}

$conn->CompleteTrans();
$conn->Close();
echo $check;
?>
