<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/member/member_mng/MemberCommonListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$memberCommonListDAO = new MemberCommonListDAO();
$check = 1;

$conn->StartTrans();

$param = array();
$param["member_seqno"] = $fb->form("seqno");

$rs = $memberCommonListDAO->selectMemberDetailInfo($conn, $param);

$param = array();
$param["table"] = "grade_req";
$param["col"]["exist_grade"] = $rs->fields["grade"];
$param["col"]["new_grade"] = $fb->form("new_grade");
$param["col"]["req_empl_name"] = $fb->session("name");
$param["col"]["reason"] = $fb->form("reason");
$param["col"]["state"] = 1;
$param["col"]["regi_date"] = date("Y-m-d H:i:s");
$param["col"]["member_seqno"] = $fb->form("seqno");

$rs = $memberCommonListDAO->insertData($conn, $param);

if (!$rs) {
    $check = 0; 
} 

echo $check;
$conn->CompleteTrans();
$conn->close();
?>
