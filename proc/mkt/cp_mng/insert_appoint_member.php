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

for($i = 0; $i < count($member_arr); $i++) {

    $cnt_check = 0;

    $tmp = explode("@", $member_arr[$i]);

    $seqno = $tmp[0];
    $nick = $tmp[1];

    //지정 회원 임시테이블 중복 검사
    $param = array();
    $param["table"] = "cp_object_appoint_temp";
    $param["col"] = "cp_object_appoint_temp_seqno";
    $param["where"]["member_seqno"] = $seqno;

    $result = $cpDAO->selectData($conn, $param);

    //임시테이블에 중복 된 값이 있으면
    if ($result->recordCount() > 0) {
        $cnt_check = 1;
    }

    /*
     * 지정 회원 쿠폰 발급테이블 중복 검사
     *
     */    
    $param = array();
    $param["table"] = "cp_issue";
    $param["col"] = "cp_issue_seqno";
    $param["where"]["member_seqno"] = $seqno;
    $param["where"]["cp_seqno"] = $fb->form("cp_seqno");

    $result = $cpDAO->selectData($conn, $param);

    //쿠폰 발급 테이블에 중복 된 값이 있으면
    if ($result->recordCount() > 0) {
        $cnt_check = 1;
    }

    //중복 되지 않는 회원은 임시 테이블에 저장
    if ($cnt_check == 0) {

        $param = array();
        $param["table"] = "cp_object_appoint_temp";
        $param["col"]["member_seqno"] = $seqno;
        $param["col"]["office_nick"] = $nick;

        $result = $cpDAO->insertData($conn, $param);

        if (!$result) {
            $check = 1;
        }
    }
}

//쿠폰 발급 테이블
$param = array();
$param["cp_seqno"] = $fb->form("cp_seqno");
$result = $cpDAO->selectCpIssueList($conn, $param);
$list = "";

if ($result->recordCount() > 0) {
    $list = makeAppointMemberList($result);
}

//쿠폰 발급 임시 테이블
$param = array();
$param["table"] = "cp_object_appoint_temp";
$param["col"] = "member_seqno, office_nick";

$result = $cpDAO->selectData($conn, $param);
//리스트 불러오기
$list .= makeAppointMemberList($result);

echo $check . "♪♥♭" . $list;

$conn->CompleteTrans();
$conn->close();
?>
