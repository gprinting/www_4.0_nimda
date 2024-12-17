<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/member/member_mng/MemberCommonListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$memberCommonListDAO = new MemberCommonListDAO();

$check = 1;
$conn->StartTrans();

$param = array();
$param["cashreceipt_card_num"] = $fb->form("cashreceipt_card_num");
$param["cashreceipt_cell_num"] = $fb->form("cashreceipt_cell_num");
$param["member_seqno"] = $fb->form("seqno");

$rs = $memberCommonListDAO->updateMemberDetailCashInfo($conn, $param);

if (!$rs) {
    $check = 0;
}

echo $check;
$conn->CompleteTrans();
$conn->close();
?>
