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

$rs = $memberCommonListDAO->selectMemberCalculInfo($conn, $param);

$param = array();
$param["member_seqno"] = $fb->form("seqno");
$param["prepay_price"] = number_format($rs->fields["prepay_price"]);
$param["order_lack_price"] = number_format($rs->fields["order_lack_price"]);
$param["member_name"] = $rs->fields["member_name"];
$param["bank_name"] = $rs->fields["bank_name"];
$param["ba_num"] = $rs->fields["ba_num"];

echo makeMemberCalculInfoHtml($param) . "♪";
$conn->close();
?>
