<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/member/member_mng/MemberCommonListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$memberCommonListDAO = new MemberCommonListDAO();

$param = array();
$param["table"] = "member_dlvr";
$param["col"] = "dlvr_name ,regi_date ,recei ,tel_num 
                ,cell_num ,zipcode ,addr ,addr_detail
                ,member_seqno, basic_yn, member_dlvr_seqno";
$param["where"]["member_seqno"] = $fb->form("seqno");

$rs = $memberCommonListDAO->selectData($conn, $param);

$dlvr_list_html = "";
if (!$rs->EOF == 1) {
    $dlvr_list_html = makeMemberDlvrHtml($rs);
} else {
    $dlvr_list_html = "<tr><td colspan=\"7\">검색 된 내용이 없습니다.</td></tr>";
}

$param["where"]["basic_yn"] = "Y";

$basic_rs = $memberCommonListDAO->selectData($conn, $param);

$tel_num = explode("-", $basic_rs->fields["tel_num"]);
$cell_num = explode("-", $basic_rs->fields["cell_num"]);

$param = array();
$param["member_seqno"] = $fb->form("seqno");

$fr_send_rs = $memberCommonListDAO->selectMemberDlvrInfo($conn, $param);

$param = array();
$param["member_seqno"] = $fb->form("seqno");
$param["dlvr_name"] = $basic_rs->fields["dlvr_name"];
$param["tel_num1"] = $tel_num[0];
$param["tel_num2"] = $tel_num[1];
$param["tel_num3"] = $tel_num[2];
$param["recei"] = $basic_rs->fields["recei"];
$param["cell_num1"] = $cell_num[0];
$param["cell_num2"] = $cell_num[1];
$param["cell_num3"] = $cell_num[2];
$param["addr"] = $basic_rs->fields["addr"] . " " . $basic_rs->fields["addr_detail"];
$param["dlvr_list_html"] = $dlvr_list_html;

echo makeMemberDlvrInfoHtml($param) . "♪" . 
     $fr_send_rs->fields["dlvr_friend_yn"] . "♪" . 
     $fr_send_rs->fields["dlvr_friend_main"] . "♪" .
     $fr_send_rs->fields["dlvr_dvs"] . "♪" .
     $fr_send_rs->fields["order_way"] . "♪" .
     $fr_send_rs->fields["dlvr_code"] . "♪" .
     $fr_send_rs->fields["direct_dlvr_yn"];
$conn->close();
?>
