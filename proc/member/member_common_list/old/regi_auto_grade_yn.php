<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/member/member_mng/MemberCommonListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new MemberCommonListDAO();
$check = 1;

$conn->StartTrans();

$param = array();
$param["member_seqno"] = $fb->form("seqno");
$param["auto_grade_yn"] = $fb->form("auto_grade_yn");

$rs = $dao->updateMemberAutoGrade($conn, $param);

if (!$rs) {
    $check = 0; 
} 

echo $check;
$conn->CompleteTrans();
$conn->close();
?>
