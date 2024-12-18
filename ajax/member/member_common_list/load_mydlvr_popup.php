<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/common_define/common_info.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/member/member_mng/MemberCommonListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$memberCommonListDAO = new MemberCommonListDAO();

$member_seqno = $fb->form("member_seqno");
$member_dlvr_seqno = $fb->form("seqno");

$param = array();
$param["table"] = "member_dlvr";
$param["col"] = "dlvr_name ,regi_date ,recei ,tel_num 
                ,cell_num ,zipcode ,addr ,addr_detail
                ,member_seqno, basic_yn, member_dlvr_seqno";
$param["where"]["member_dlvr_seqno"] = $member_dlvr_seqno;

$rs = $memberCommonListDAO->selectData($conn, $param);

$del_html = "";
if ($member_dlvr_seqno) {
//    $del_html .= "<label class=\"fix_width5\"> &nbsp; </label>";
    $del_html .= "<button type=\"button\" class=\"btn btn-sm btn-danger\" onclick=\"delDlvrAddrInfo('$member_dlvr_seqno');\">삭제</button>";

} else {
    $del_html = "";
} 

$option_html = "\n<option value=\"%s\">%s</option>";
//전화번호
$tel_html = "";
foreach (TEL_NUM as $val) {
    $tel_html .= sprintf($option_html, $val, $val);
}

$tel_num = explode("-", $rs->fields["tel_num"]);
$cell_num = explode("-", $rs->fields["cell_num"]);

$param = array();
$param["member_seqno"] = $member_seqno;
$param["member_dlvr_seqno"] = $member_dlvr_seqno;
$param["del_html"] = $del_html;
$param["dlvr_name"] = $rs->fields["dlvr_name"];
$param["recei"] = $rs->fields["recei"];
$param["tel_html"] = $tel_html;
$param["tel_num2"] = $tel_num[1];
$param["tel_num3"] = $tel_num[2];
$param["cell_num2"] = $cell_num[1];
$param["cell_num3"] = $cell_num[2];
$param["zipcode"] = $rs->fields["zipcode"];
$param["addr"] = $rs->fields["addr"];
$param["addr_detail"] = $rs->fields["addr_detail"];

echo makeMemberDlvrPopup($param) . "♪" . $tel_num[0] . "♪" . $cell_num[0];
$conn->close();
?>
