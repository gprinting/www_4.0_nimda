<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/common_lib/CommonUtil.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/Template.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/member/member_mng/MemberCommonListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$template = new Template();
$dao = new MemberCommonListDAO();
$util = new CommonUtil();

$state = $util->status2statusCode("입금대기");

$param = array();
$param["member_seqno"] = $fb->form("seqno");
$param["order_state"] = $state;

$rs = $dao->selectMemberSummaryInfo($conn, $param);
$order_count_rs = $dao->selectOrderCountInfo($conn, $param);

$param = array();
$param["table"] = "cp_issue";
$param["col"] = "COUNT(*) AS cp";
$param["where"]["member_seqno"] = $fb->form("seqno");

$cp_count_rs = $dao->selectData($conn, $param);

$param = array();
$param["table"] = "empl";
$param["col"] = "name";
$param["where"]["empl_seqno"] = $rs->fields["nc_release_resp"];

$nc_release_resp = $dao->selectData($conn, $param)->fields["name"];

$param = array();
$param["table"] = "empl";
$param["col"] = "name";
$param["where"]["empl_seqno"] = $rs->fields["bl_release_resp"];

$bl_release_resp = $dao->selectData($conn, $param)->fields["name"];

$param = array();
$param["member_name"] = $rs->fields["member_name"];
$param["member_id"] = $rs->fields["member_id"];
$param["office_nick"] = $rs->fields["office_nick"];
$param["grade_name"] = $rs->fields["grade_name"];
$param["member_typ"] = $rs->fields["member_typ"];
$param["own_point"] = number_format($rs->fields["own_point"]);
$param["cp"] = number_format($cp_count_rs->fields["cp"]);
$param["order_count"] = number_format($order_count_rs->fields["order_count"]);
$param["office_eval"] = $rs->fields["office_eval"];
$param["first_join_date"] = $rs->fields["first_join_date"];
$param["first_order_date"] = $rs->fields["first_order_date"];
$param["final_order_date"] = $rs->fields["final_order_date"];
$param["nc_release_resp"] = $nc_release_resp;
$param["bl_release_resp"] = $bl_release_resp;

echo makeMemberSummaryInfoHtml($param) . "♪" . $rs->fields["mailing_yn"] . "♪" . $rs->fields["sms_yn"];
$conn->close();
?>
