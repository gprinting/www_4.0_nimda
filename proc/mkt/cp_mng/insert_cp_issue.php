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

$seqno = $fb->form("cp_seqno");

$param = array();
$param["table"] = "cp_object_appoint_temp";
$param["col"] = "member_seqno";

$result = $cpDAO->selectData($conn, $param);
if (!$result) {

    $check = 1;
} else if ($result->recordCount() == 0) {

    $check = 2;
    echo $check;
    exit;

}

//시작일자, 종료일자가 있는 기간제일때
$cp_param = array();
$cp_param["table"] = "cp";
$cp_param["col"]  = "public_period_start_date, cp_extinct_date, expire_dvs";
$cp_param["where"]["cp_seqno"] = $seqno;
$cp_result = $cpDAO->selectData($conn, $cp_param);

$use_start_date = $cp_result->fields["public_period_start_date"];
$use_deadline = $cp_result->fields["cp_extinct_date"];
$expire_dvs = $cp_result->fields["expire_dvs"];

while ($result && !$result->EOF) {

    $member_seqno = $result->fields["member_seqno"];

    $issue_date = date("Y-m-d H:i:s", time());
    
    //랜덤 쿠폰번호 생성
    $cp_num = "";

    $feed = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    for ($i=0; $i < 10; $i++) {
        $cp_num .= substr($feed, rand(0, strlen($feed)-1), 1);
    }

    $param = array();
    $param["table"] = "cp_issue";
    $param["col"]["cp_seqno"] = $seqno;
    $param["col"]["cp_num"] = $cp_num;
    $param["col"]["member_seqno"] = $member_seqno;
    $param["col"]["issue_date"] = $issue_date;
    $param["col"]["use_yn"] = "N";

    if ($expire_dvs != 3) {
        $param["col"]["use_deadline"] = $use_deadline . " 23:59:59";
    }
    $param["col"]["use_able_start_date"] = $use_start_date . " 00:00:00";

    $cp_result = $cpDAO->insertData($conn, $param);
    if (!$cp_result) {

        $check = 1;
    }

    $result->moveNext();
}

$param = array();
$param["cp_seqno"] = $fb->form("cp_seqno");
$result = $cpDAO->selectCpIssueList($conn, $param);

$list = makeAppointMemberList($result);

echo $check . "♪♥♭" . $list;

$conn->CompleteTrans();
$conn->close();
?>
