<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/mkt/mkt_mng/CpMngDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();
$conn->StartTrans();

$fb = new FormBean();
$cpDAO = new CpMngDAO();
$check = 0;

$member_arr = $fb->form("member_arr");
$cp_seqno = $fb->form("cp_seqno");

for($i = 0; $i < count($member_arr); $i++) {

    $tmp = explode("@", $member_arr[$i]);

    $seqno = $tmp[0];
    $nick = $tmp[1];

    //대상 지정 임시 일련번호 가져오기
    $param = array();
    $param["table"] = "cp_object_appoint_temp";
    $param["col"] = "cp_object_appoint_temp_seqno";
    $param["where"]["member_seqno"] = $seqno;

    $result = $cpDAO->selectData($conn, $param);

    if (!$result) {
        $check = 1;
    }
   
    //대상 지정 임시 테이블에 찾는 값이 있을 때 삭제
    if ($result-> recordCount() > 0) {

        $cp_object_seqno = $result->fields["cp_object_appoint_temp_seqno"];

        //지정 회원 임시 테이블에서 삭제
        $param = array();
        $param["table"] = "cp_object_appoint_temp";
        $param["prk"] = "cp_object_appoint_temp_seqno";
        $param["prkVal"] = $cp_object_seqno;

        $result = $cpDAO->deleteData($conn, $param);

        if (!$result) {
            $check = 1;
        }
    }

    //쿠폰 발급 일련번호 가져오기
    $param = array();
    $param["table"] = "cp_issue";
    $param["col"] = "cp_issue_seqno";
    $param["where"]["cp_seqno"] = $cp_seqno;
    $param["where"]["member_seqno"] = $seqno;

    $result = $cpDAO->selectData($conn, $param);

    if (!$result) {
        $check = 1;
    }

    //쿠폰 발급 테이블에 찾는 값이 있을때 삭제
    if ($result->recordCount() > 0) {

        $cp_issue_seqno = $result->fields["cp_issue_seqno"];

        //쿠폰 발급 테이블에서 삭제
        $param = array();
        $param["table"] = "cp_issue";
        $param["prk"] = "cp_issue_seqno";
        $param["prkVal"] = $cp_issue_seqno;

        $result = $cpDAO->deleteData($conn, $param);

        if (!$result) {
            $check = 1;
        }
    }
}

//쿠폰 발급 테이블
$param = array();
$param["cp_seqno"] = $cp_seqno;
$result = $cpDAO->selectCpIssueList($conn, $param);

if (!$result) {
    $check = 1;
}

$list = "";

if ($result->recordCount() > 0) {
    //발급 리스트 불러오기
    $list = makeAppointMemberList($result);
}

//쿠폰 대상 지정 임시 테이블
$param = array();
$param["table"] = "cp_object_appoint_temp";
$param["col"] = "member_seqno, office_nick";
$result = $cpDAO->selectData($conn, $param);

if (!$result) {
    $check = 1;
}

//임시 리스트 불러오기
$list .= makeAppointMemberList($result);

echo $check . "♪♥♭" . $list;

$conn->CompleteTrans();
$conn->close();
?>
