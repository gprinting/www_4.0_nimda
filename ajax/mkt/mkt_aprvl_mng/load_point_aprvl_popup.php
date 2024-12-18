<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/mkt/mkt_mng/MktAprvlMngDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$mktDAO = new MktAprvlMngDAO();

$point_req_seq = $fb->form("point_req_seq");

//회원 포인트 요청
$param = array();
$param["table"] = "member_point_req";
$param["col"] = "state, origin_file_name, save_file_name, file_path";
$param["where"]["member_point_req_seqno"] =  $point_req_seq;
$result = $mktDAO->selectData($conn, $param);

$state = $result->fields["state"];
$file_name = $result->fields["origin_file_name"];

$param = array();
//파일이 있을 때 버튼 보이기
if ($file_name) {

    //파일 정보
    $param["file_path"] = $result->fields["file_path"];
    $param["save_file_name"] = $result->fields["save_file_name"];

} else {

    $param["file_btn"] = "disabled=\"disabled\"";

}

//이미 승인/거절 처리한 데이터는 수정 불가
if ($state != "1") {

    $param["dis_btn"] = "disabled=\"disabled\"";
}

$html = getPointAprvlView($param);

echo $html;

$conn->close();
?>
