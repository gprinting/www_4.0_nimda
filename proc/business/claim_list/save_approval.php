<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/business/claim_mng/ClaimListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new ClaimListDAO();
$check = "저장했습니다.";

$conn->StartTrans();
$session = $fb->getSession();
$claim_seqno = $fb->form("seqno");
$aprvl_code  = $fb->form("code");
$aprvl_user  = $session["name"];

$param = array();
$param["claim_seqno"] = $claim_seqno;
$param["aprvl_code"]  = $aprvl_code;
$param["aprvl_user"]  = $aprvl_user;

$rs = $dao->updateAprvlData($conn, $param);
if (!$rs) {
    $check = "저장에 실패했습니다."; 
    $conn->FailTrans();
    $conn->RollbackTrans();
}

$conn->CompleteTrans();
$conn->Close();
echo $check;
?>
