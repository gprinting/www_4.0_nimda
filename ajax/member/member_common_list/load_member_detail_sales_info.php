<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/member/member_mng/MemberCommonListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$memberCommonListDAO = new MemberCommonListDAO();

$member_seqno = $fb->form("seqno");

$opt = array();
$opt[0] = "주문일";

$optVal = array();
$optVal[0] = "order_regi_date";

$param = array();
$param["value"] = $optVal;
$param["fields"] = $opt;
$param["id"] = "sales_search_cnd";
$param["flag"] = TRUE;
$param["from_id"] = "sales_from";
$param["to_id"] = "sales_to";
$param["func"] = "salesDateSet";

//날짜 검색
$date_picker_html = makeDatePickerHtml($param);

//기업 개인인 경우 기업정보 보여줌
$param = array();
$param["member_seqno"] = $member_seqno;

$group_rs = $memberCommonListDAO->selectMemberDetailInfo($conn, $param);

if ($group_rs->fields["group_id"]) {
    $member_seqno = $group_rs->fields["group_id"];
}

$param = array();
$param["date_picker_html"] = $date_picker_html;

echo makeMemberSalesInfoHtml($param) . "♪";
$conn->close();
?>
