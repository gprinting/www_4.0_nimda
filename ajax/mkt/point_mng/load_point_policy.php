<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/mkt/mkt_mng/PointMngDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$pointDAO = new PointMngDAO();

//포인트 정책 정보 가져오기
$param = array();
$param["table"] = "point_policy";
$param["col"] = "member_join_point, prdt_order_give_rate";
$param["where"]["point_policy_seqno"] = "1";

$result = $pointDAO->selectData($conn, $param);

$join_point = $result->fields["member_join_point"];
$order_rate = $result->fields["prdt_order_give_rate"];

echo $join_point . "♪♭@" . $order_rate;

$conn->close();
?>
