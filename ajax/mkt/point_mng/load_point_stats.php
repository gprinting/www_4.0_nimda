<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/mkt/mkt_mng/PointMngDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$pointDAO = new PointMngDAO();

//월별 포인트 통계
$param = Array();
$param["table"] = "mon_point_stats";
$param["col"] = "member_join_point, prdtorder_point, admin_give_point, 
                 grade_point, tot_recoup_point";
$param["where"]["year"] = $fb->form("year");
$param["where"]["mon"] = $fb->form("mon");
$param["where"]["cpn_admin_seqno"] = $fb->form("sell_site");

$result = $pointDAO->selectData($conn, $param);

//회원가입 포인트
$join_point = $result->fields["member_join_point"];
if ($join_point == "") $join_point = 0;
//상품주문 포인트
$order_point = $result->fields["prdtorder_point"];
if ($order_point == "") $order_point = 0;
//관리자 지급 포인트
$admin_point = $result->fields["admin_give_point"];
if ($admin_point == "") $admin_point = 0;
//등급 포인트
$grade_point = $result->fields["grade_point"];
if ($grade_point == "") $grade_point = 0;
$tot_point = $join_point + $order_point + $admin_point + $grade_point;

//총 회수 포인트
$recoup_point = $result->fields["tot_recoup_point"];
if ($recoup_point == "") $recoup_point = 0;

echo $join_point . "♪♭‡" . $order_point . "♪♭‡" . 
     $admin_point . "♪♭‡" . $grade_point . "♪♭‡" . 
     $tot_point . "♪♭‡" . $recoup_point;

$conn->close();
?>
