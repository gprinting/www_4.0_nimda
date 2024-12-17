<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/define/nimda/common_config.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/member/member_mng/MemberCommonListDAO.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/file/FileAttachDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$fileDAO = new FileAttachDAO();
$memberCommonListDAO = new MemberCommonListDAO();

$member_seqno = $fb->form("seqno");

//기업 개인인 경우 기업정보 보여줌
$param = array();
$param["member_seqno"] = $member_seqno;

$group_rs = $memberCommonListDAO->selectMemberDetailInfo($conn, $param);

if ($group_rs->fields["group_id"]) {
    $member_seqno = $group_rs->fields["group_id"];
}

$check = 1;
$conn->StartTrans();

//파일 업로드 경로
$param = array();
$param["file_path"] = SITE_DEFAULT_POINT_FILE; 
$param["tmp_name"] = $_FILES["point_img"]["tmp_name"];
$param["origin_file_name"] = $_FILES["point_img"]["name"];

//파일을 업로드 한 후 저장된 경로를 리턴한다.
$rs = $fileDAO->upLoadFile($param);

//등록 
$param = array();
$param["table"] = "member_point_req";
$param["col"]["point_name"] = $fb->form("point_name");
$param["col"]["point"] = str_replace(",", "", $fb->form("point"));
$param["col"]["reason"] = $fb->form("point_reason");
$param["col"]["regi_date"] = date("Y-m-d");
$param["col"]["req_empl_name"] = $fb->session("name");
$param["col"]["state"] = 1;
$param["col"]["origin_file_name"] = $_FILES["point_img"]["name"];
$param["col"]["save_file_name"] = $rs["save_file_name"];
$param["col"]["file_path"] = $rs["file_path"];
$param["col"]["member_seqno"] = $member_seqno;

$rs = $memberCommonListDAO->insertData($conn,$param);

if (!$rs) {
    $check = 0;
}

echo $check;
$conn->CompleteTrans();
$conn->close();
?>
