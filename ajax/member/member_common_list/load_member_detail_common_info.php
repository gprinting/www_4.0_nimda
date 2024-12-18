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
$param["member_seqno"] = $fb->form("seqno");

$rs = $memberCommonListDAO->selectMemberCommonInfo($conn, $param);

$param = array();
$param["member_seqno"] = $rs->fields["member_seqno"];
$param["member_name"] = $rs->fields["member_name"];
$param["member_id"] = $rs->fields["member_id"];
$param["sell_site"] = $rs->fields["sell_site"];
$param["cell_num"] = $rs->fields["cell_num"];
$param["tel_num"] = $rs->fields["tel_num"];
$param["mail"] = $rs->fields["mail"];
$param["birth"] = $rs->fields["birth"];
$param["fix_oa"] = number_format($rs->fields["fix_oa"]);
$param["bad_oa"] = number_format($rs->fields["bad_oa"]);
$param["loan_limit_price"] = number_format($rs->fields["loan_limit_price"]);

echo makeMemberBasicInfoHtml($param) . "♪" . $rs->fields["member_dvs"] . "♪" . 
$rs->fields["new_yn"] . "♪" . $rs->fields["member_typ"] . "♪" . $rs->fields["onefile_etprs_yn"] . "♪" . 
$rs->fields["card_pay_yn"];

$conn->close();
?>
