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
$point_req_seq = $fb->form("point_req_seq");
$check = 1;

//포인트 승인시
if ($state == "2") {

    //회원 포인트 요청 테이블
    $param = array();
    $param["table"] = "member_point_req";
    $param["col"] = "point_name, point, member_seqno";
    $param["where"]["member_point_req_seqno"] = $point_req_seq;

    $result = $mktDAO->selectData($conn, $param);
    if (!$result) $check = 0;

    $point_name = $result->fields["point_name"];
    $mkt_point = $result->fields["point"];
    $member_seqno = $result->fields["member_seqno"];

    //회원 테이블
    $param = array();
    $param["member_seqno"] = $member_seqno;

    $result = $mktDAO->selectMemberPoint($conn, $param);
    if (!$result) $check = 0;

    $own_point = $result->fields["own_point"];
    $member_grade = $result->fields["member_grade"];

    //update 될 포인트
    $point = intval($mkt_point) + intval($own_point);
    
    //회원 포인트 수정
    $param = array();
    $param["point"] = $point;
    $param["member_seqno"] = $member_seqno;

    $result = $mktDAO->updateMemberPoint($conn, $param);
    if (!$result) $check = 0;

    //회원 포인트 내역 추가
    $param = array();
    $param["table"] = "member_point_history";
    $param["col"]["regi_date"] = date("Y-m-d h:i:s"); 
    $param["col"]["point_name"] = $point_name;
    $param["col"]["point"] = $mkt_point;
    $param["col"]["rest_point"] = $point;
    $param["col"]["give_reason"] = "마케팅 지급 포인트";
    $param["col"]["dvs"] = "적립";
    $param["col"]["member_seqno"] = $member_seqno;
    $param["col"]["member_grade"] = $member_grade;

    $result = $mktDAO->insertData($conn, $param);
    if (!$result) $check = 0;
}

//등급 요청 승인/거절시 상태 update 처리
$param = array();
$param["table"] = "member_point_req";
$param["col"]["state"] = $state;
$param["col"]["aprvl_empl_name"] = $_SESSION["name"];
$param["prk"] = "member_point_req_seqno";
$param["prkVal"] = $point_req_seq;

$result = $mktDAO->updateData($conn, $param);
if (!$result) $check = 0;

echo $check;

$conn->CompleteTrans();
$conn->close();
?>
