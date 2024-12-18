<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/calcul_mng/tab/SalesTabListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new SalesTabListDAO();
$able_price = 0;
$issued_price = 0;

$member_seqno   = $fb->form("member_seqno");

$param = array();
$param["member_seqno"] = $member_seqno;

//회원 발행 가능 금액 조회
$rs = $dao->selectIssueAblePrice($conn, $param);
if ($rs) {
    $able_price = $rs->fields["public_object_price"];
    if (!$able_price) $able_price = 0;
}

//회원에게 세금계산서 OR 현금영수증 발행해준 금액 조회
$param = array();
$param["member_seqno"] = $member_seqno;

$rs = $dao->selectIssuePriceSum($conn, $param);
if ($rs) {
    $issue_price = $rs->fields["issue_sum"];
    if (!$issue_price) $issue_price = 0;
}

//발행 가능 금액 - 발행 한 금액
$issue_able_price = 0;
$issue_able_price = (int)$able_price - (int)$issue_price;
/*
//금액이 0 이하일때는 0을 보여줌
if ($issue_able_price < 0) {
    $issue_able_price = 0;
}
*/

echo number_format($issue_able_price);
$conn->close();
?>
