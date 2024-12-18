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
$coupon_seqno = $fb->form("coupon_seqno");

$param = array();
$param["coupon_seqno"] = $coupon_seqno;

$result = $cpDAO->selectMembersForCouponRelease($conn);


while($result && !$result->EOF) {
    $param["member_seqno"] = $result->fields["member_seqno"];
    $cpDAO->insertCoupon($conn,$param);
    $result->MoveNext();
}
$cpDAO->updateCouponReleaseComplete($conn, $param);

$conn->close();
?>
