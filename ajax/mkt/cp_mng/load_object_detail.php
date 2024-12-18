<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/mkt/mkt_mng/CpMngDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$cpDAO = new CpMngDAO();

//쿠폰 일련번호
$cp_seqno = $fb->form("cp_seqno");
//회사 관리 일련번호
$cpn_seqno = $fb->form("cpn_seqno");

$param = array();
$param["cp_seqno"] = $cp_seqno;
$param["cpn_seqno"] = $cpn_seqno;

$result = $cpDAO->selectCpIssueList($conn, $param);

if ($result->recordCount() > 0) {
    $issue_list = makeAppointMemberList($result);

} else {
    $issue_list = "<tr><td colspan=\"3\">내용이 없습니다.</td></tr>";
}

//팀구분 콤보 박스 셋팅
$param = array();
$param["table"] = "depar_admin";
$param["col"] = "depar_code, depar_name";
$param["where"]["depar_level"] = "2";
$param["where"]["cpn_admin_seqno"] = $cpn_seqno;
$result = $cpDAO->selectData($conn, $param);

$arr = array();
$arr["flag"] = "Y";
$arr["def"] = "전체";
$arr["val"] = "depar_code";
$arr["dvs"] = "depar_name";
$depar_html = makeSelectOptionHtml($result, $arr);

//등급구분 콤보박스 셋팅
$param = array();
$param["table"] = "member_grade_policy";
$param["col"] = "grade, member_grade_policy_seqno";

$result = $cpDAO->selectData($conn, $param);

$arr = array();
$arr["flag"] = "Y";
$arr["def"] = "전체";
$arr["val"] = "member_grade_policy_seqno";
$arr["dvs"] = "grade";
$arr["dvs_tail"] = "등급";
$grade_html = makeSelectOptionHtml($result, $arr);

//DOC파일에 넘길 파라미터;
$param = array();
$param["depar_html"] = $depar_html;
$param["grade_html"] = $grade_html;
$param["issue_list"] = $issue_list;

$html = getObjectView($param);

echo $html;

$conn->close();
?>
