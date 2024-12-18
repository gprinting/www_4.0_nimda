<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/Template.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/member/member_mng/MemberCommonListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$template = new Template();
$memberCommonListDAO = new MemberCommonListDAO();

$param = array();
$param["table"] = "member_grade_policy";
$param["col"] = "grade_name, grade";

$grade_rs = $memberCommonListDAO->selectData($conn, $param);

$grade_arr = array();
while ($grade_rs && !$grade_rs->EOF) {
    $grade = $grade_rs->fields["grade"];
    $grade_name = $grade_rs->fields["grade_name"];
    $grade_arr[$grade] = $grade_name;
    $grade_rs->moveNext();
}

$grade_rs->moveFirst();

$arr = [];
$arr["flag"] = "Y";
$arr["def"] = "등급(전체)";
$arr["dvs"] = "grade_name";
$arr["val"] = "grade";

$grade_html = makeSelectOptionHtml($grade_rs, $arr);

$member_seqno = $fb->form("seqno");

//기업 개인인 경우 기업정보 보여줌
$param = array();
$param["member_seqno"] = $member_seqno;

$group_rs = $memberCommonListDAO->selectMemberDetailInfo($conn, $param);

if ($group_rs->fields["group_id"]) {
    $member_seqno = $group_rs->fields["group_id"];
}

$param = array();
$param["table"] = "grade_req";
$param["col"] = "exist_grade, new_grade, req_empl_name, 
    aprvl_empl_name, reason, state, regi_date";
$param["where"]["member_seqno"] = $member_seqno;

$rs = $memberCommonListDAO->selectData($conn, $param);

if (!$rs->EOF == 1) {
    $grade_list_html = makeMemberGradeListHtml($rs, $grade_arr);
} else {
    $grade_list_html = "<tr><td colspan=\"8\">검색 된 내용이 없습니다.</td></tr>";
}

$year_html = "";
for ($i = 1970; $i < 2100; $i++) {
    $year_html .= "<option value=\"$i\"" . ($i == date('Y') ? "selected=\"selected\"" : "") . ">$i</option>";
}

$param = array();
$param["year_html"] = $year_html;
$param["grade_html"] = $grade_html;
$param["grade_list_html"] = $grade_list_html;
$param["member_seqno"] = $member_seqno;

echo makeMemberGradeInfoHtml($param) . "♪";
$conn->close();
?>
