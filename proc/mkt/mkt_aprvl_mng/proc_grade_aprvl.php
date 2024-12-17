<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/mkt/mkt_mng/MktAprvlMngDAO.inc");
$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();
$conn->StartTrans();

$fb = new FormBean();
$mktDAO = new MktAprvlMngDAO();

$state = $fb->form("state");
$grade_req_seq = $fb->form("grade_req_seq");
$check = 1;

//등급승인시
if ($state == "2") {

    $param = array();
    $param["table"] = "grade_req";
    $param["col"] = "new_grade, member_seqno";
    $param["where"]["grade_req_seqno"] = $grade_req_seq;

    $result = $mktDAO->selectData($conn, $param);
    if (!$result) $check = 0;

    $new_grade = $result->fields["new_grade"];
    $member_seqno = $result->fields["member_seqno"];
    

    //회원 등급 변경
    $param = array();
    $param["new_grade"] = $new_grade;
    $param["member_seqno"] = $member_seqno;

    $result = $mktDAO->updateMemberGrade($conn, $param);

    if (!$result) $check = 0;
}

//등급 요청 승인/거절시 상태 update 처리
$param = array();
$param["table"] = "grade_req";
$param["col"]["state"] = $state;
$param["col"]["aprvl_empl_name"] = $_SESSION["name"];
$param["prk"] = "grade_req_seqno";
$param["prkVal"] = $grade_req_seq;

$result = $mktDAO->updateData($conn, $param);
if (!$result) $check = 0;

echo $check;

$conn->CompleteTrans();
$conn->close();
?>
